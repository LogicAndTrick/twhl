<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\Accounts\User;
use App\Helpers\TypedRouting;
use App\Rules\StopForumSpam;

/**
 * Much of the code here comes from the traits that ship with the laravel/ui package.
 * I've inlined the traits so I can drop that dependency.
 */
#[TypedRouting]
class AuthController extends Controller
{
    public function __construct()
    {
        $this->anonymous(['login', 'register']);
        $this->permission(['logout', 'convert'], true);
        $this->middleware(RedirectIfAuthenticated::class, ['except' => ['getLogout', 'getConvert', 'postConvert']]);
    }

    public function getLogin()
    {
        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // throttle login attempts - 5 per minute
        if (app(RateLimiter::class)->tooManyAttempts($this->throttleKey($request), 5)) {
            event(new Lockout($request)); // don't need this event, but it's built-in, so we'll use it

            $seconds = app(RateLimiter::class)->availableIn($this->throttleKey($request));
            throw ValidationException::withMessages([
                'email' => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ])->status(Response::HTTP_TOO_MANY_REQUESTS);
        }

        // attempt to login
        $attempt = Auth::guard()->attempt($request->only('email', 'password'), $request->boolean('remember'));

        if ($attempt) {
            // successful
            $request->session()->put('auth.password_confirmed_at', time()); // for routes that force a password confirmation (we have none)
            $request->session()->regenerate();
            app(RateLimiter::class)->clear($this->throttleKey($request));

            // user logged in - update the database stats and session info
            $user = Auth::guard()->user();

            $request->session()->put('login_time', Carbon::now());
            $request->session()->put('last_access_time', $user->last_access_time);

            $user->last_login_time = Carbon::now();
            $user->last_access_time = Carbon::now();
            $user->last_access_page = $request->getPathInfo();
            $user->last_access_ip = $request->ip();
            $user->stat_logins++;
            $user->save();

            return redirect()->intended();
        }

        // login failed, rate limiter + 1
        app(RateLimiter::class)->hit($this->throttleKey($request), 60);

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ])->redirectTo("/auth/login");
    }

    public function getRegister()
    {
        // new accounts are rate-limited, so show a different page if the most recent account is too recent
        $lastUser = User::withTrashed()->orderBy('created_at', 'desc')->first();
        $lastRegistrationRecent = $lastUser && $lastUser->created_at->diffInMinutes() < 60;
        if ($lastRegistrationRecent) return view('auth.rate-limited');

        return view('auth.register');
    }

    public function postRegister(Request $request)
    {
        // If two people hit this post at the same time, let them through
        // Bots can't hit this endpoint directly due to Recaptcha.
        $this->registrationValidator($request->all())->validate();

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        event(new Registered($user));
        Auth::guard()->login($user);
        return redirect('/');
    }

    public function getLogout(Request $request)
    {
        // Verify CSRF token to avoid trolls (aka potatis_invalid)
        $token = $request->input('_token');

        if (
            is_string($request->session()->token()) &&
            is_string($token) &&
            hash_equals($request->session()->token(), $token)
        ) {
            Auth::guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/');
        } else {
            throw new TokenMismatchException();
        }
    }

    /**
     * Convert a legacy TWHL3 account into a TWHL4 account.
     */
    public function getConvert()
    {
        $user = Auth::user();
        if (!$user || !$user->legacy_password) return redirect('/');

        return view('auth.convert', [
            'user' => $user
        ]);
    }

    public function postConvert(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->legacy_password) return redirect('/');

        $this->convertValidator($user, $request->all())->validate();

        $user->update([
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'legacy_password' => ''
        ]);

        return redirect('home');
    }

    /**
     * Get the throttle key for the request.
     */
    protected function throttleKey(Request $request)
    {
        return Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());
    }

    /**
     * Get a validator for converting a legacy account.
     */
    private function convertValidator(User $user, array $data): \Illuminate\Contracts\Validation\Validator
    {
        Validator::extend('match_legacy_password', function ($attr, $value, $parameters) use ($user) {
            $legacy_plain = $value;
            if (strlen($legacy_plain) > 20) $legacy_plain = substr($legacy_plain, 0, 20);
            $legacy_pass = md5(strtolower(trim($legacy_plain)));
            return $user->legacy_password == $legacy_pass;
        });
        return Validator::make($data, [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'old_password' => ['required', 'match_legacy_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'agree_rules' => ['accepted'],
        ], [
            'match_legacy_password' => 'This doesn\'t match your current password.',
            'accepted' => 'You must agree to the rules.'
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     */
    private function registrationValidator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        // use this if we need to disable registration for some reason
        Validator::extend('never', function ($attribute, $value, $parameters) {
            return false;
        }, 'Registration is disabled.');

        return Validator::make($data, [
            'name' => [
                'required',
                'max:64',
                Rule::unique('users'),
                Rule::notRegex('/^\s|\s$/u'), // no leading or trailing whitespace
                Rule::notRegex('/[\p{C}\p{So}]|[\x{FE0F}\x{20E3}\x{1F3FB}-\x{1F3FF}]/u'), // no control characters, no emojis
                Rule::notRegex('/@/'), // no @ symbol
                new StopForumSpam()
            ],
            'email' => ['required', 'confirmed', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'g-recaptcha-response' => ['required', 'recaptcha'],
            'agree_rules' => ['accepted'],
        ], [
            'accepted' => 'You must agree to the rules.'
        ]);
    }
}

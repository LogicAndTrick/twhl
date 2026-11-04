<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Helpers\TypedRouting;
use App\Helpers\RouteName;

/**
 * Much of the code here comes from the traits that ship with the laravel/ui package.
 * I've inlined the traits so I can drop that dependency.
 */
#[TypedRouting]
class PasswordController extends Controller
{
    public function __construct()
    {
        $this->anonymous(['email', 'reset']);
        $this->middleware(RedirectIfAuthenticated::class);
    }

    public function getEmail()
    {
        return view('auth.password');
    }

    public function postEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $response = Password::broker()->sendResetLink($request->only('email'));

        return $response == Password::RESET_LINK_SENT
            ? back()->with('status', trans($response))
            : back()->withInput($request->only('email'))->withErrors(['email' => trans($response)]);
    }

    #[RouteName('password.reset')]
    public function getReset(Request $request, string|null $token = null)
    {
        return view('auth.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function postReset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->legacy_password = '';
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
                Auth::guard()->login($user);
            }
        );

        return $response == Password::PASSWORD_RESET
            ? redirect('/')->with('status', trans($response))
            : back()->withInput($request->only('email'))->withErrors(['email' => trans($response)]);
    }
}

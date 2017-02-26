<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller {

    use AuthenticatesUsers, RegistersUsers {
      AuthenticatesUsers::redirectPath insteadof RegistersUsers;
      AuthenticatesUsers::guard insteadof RegistersUsers;
  }

	public function __construct()
	{
		$this->middleware('guest', ['except' => ['getLogout', 'getConvert', 'postConvert'] ]);
	}

	public function getLogin() { return $this->showLoginForm(); }
	public function postLogin(Request $request) { return $this->login($request); }
	public function getLogout(Request $request) { return $this->logout($request); }
    public function getRegister() { return $this->showRegistrationForm(); }
   	public function postRegister(Request $request) { return $this->register($request); }

    /**
	 * Convert a legacy TWHL3 account into a TWHL4 account.
	 */
	public function getConvert()
	{
        $user = Auth::user();
        if (!$user || !$user->legacy_password) return redirect('/home');

        return view('auth.convert', [
            'user' => $user
        ]);
	}

    public function postConvert(Request $request) {
        $user = Auth::user();
        if (!$user || !$user->legacy_password) return redirect('/home');

        Validator::extend('match_legacy_password', function($attr, $value, $parameters) use ($user) {
            $legacy_plain = $value;
            if (strlen($legacy_plain) > 20) $legacy_plain = substr($legacy_plain, 0, 20);
            $legacy_pass = md5(strtolower(trim($legacy_plain)));
            return $user->legacy_password == $legacy_pass;
        });

        $this->validate($request->instance(), [
            'email' => 'required|confirmed|email|max:255|unique:users,email,'.$user->id,
            'old_password' => 'required|match_legacy_password',
            'password' => 'required|confirmed|min:6',
            'agree_rules' => 'accepted'
        ], [
            'match_legacy_password' => 'This doesn\'t match your current password.',
            'accepted' => 'You must agree to the rules.'
        ]);


        $user->update([
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'legacy_password' => ''
        ]);

        return redirect('home');
    }

    /**
     * Called after a user has successfully logged in
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, User $user)
    {
        $request->session()->put('login_time', Carbon::create());
        $request->session()->put('last_access_time', $user->last_access_time);

        $user->last_login_time = Carbon::create();
        $user->last_access_time = Carbon::create();
        $user->last_access_page = $request->getPathInfo();
        $user->last_access_ip = $request->ip();
        $user->stat_logins++;
        $user->save();

        return redirect()->intended($this->redirectPath());
    }

    /**
   	 * Get a validator for an incoming registration request.
   	 *
   	 * @param  array  $data
   	 * @return \Illuminate\Contracts\Validation\Validator
   	 */
   	public function validator(array $data)
   	{
        Validator::extend('never', function($attribute, $value, $parameters) { return false; }, 'Registration is disabled.');
   		return Validator::make($data, [
   			'name' => 'required|max:255|unique:users',
   			'email' => 'required|confirmed|email|max:255|unique:users',
   			'password' => 'required|confirmed|min:6',
            'g-recaptcha-response' => 'required|recaptcha',
            'agree_rules' => 'accepted'
   		], [
            'accepted' => 'You must agree to the rules.'
        ]);
   	}

   	/**
   	 * Create a new user instance after a valid registration.
   	 *
   	 * @param  array  $data
   	 * @return User
   	 */
   	public function create(array $data)
   	{
   		return User::create([
   			'name' => $data['name'],
   			'email' => $data['email'],
   			'password' => bcrypt($data['password']),
   		]);
   	}

}

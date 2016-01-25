<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller {

	use AuthenticatesAndRegistersUsers;

	public function __construct()
	{

		$this->middleware('guest', ['except' => 'getLogout']);
	}

    /**
     * Called after a user has successfully logged in
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticated(Request $request, User $user)
    {
        $request->session()->set('login_time', Carbon::create());
        $request->session()->set('last_access_time', $user->last_access_time);

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
   			'email' => 'required|email|max:255|unique:users',
   			'password' => 'required|confirmed|min:6',
            'g-recaptcha-response' => 'required|recaptcha',
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

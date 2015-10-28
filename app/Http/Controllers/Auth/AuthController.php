<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
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
   	 * Handle a login request to the application.
   	 *
   	 * @param  \Illuminate\Http\Request  $request
   	 * @return \Illuminate\Http\Response
   	 */
   	public function postLogin(Request $request)
   	{
   		$this->validate($request, [
   			'email' => 'required',    // We also allow usernames here, so don't use the 'email' validator
            'password' => 'required',
   		]);

   		$credentials = $request->only('email', 'password');

   		if (Auth::attempt($credentials, $request->has('remember')))
   		{
   			return redirect()->intended($this->redirectPath());
   		}

   		return redirect($this->loginPath())
   					->withInput($request->only('email', 'remember'))
   					->withErrors([
   						'email' => $this->getFailedLoginMessage(),
   					]);
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
   			'name' => 'required|max:255|unique:users|never',
   			'email' => 'required|email|max:255|unique:users',
   			'password' => 'required|confirmed|min:6',
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

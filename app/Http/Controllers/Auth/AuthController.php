<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

class AuthController extends Controller {

	use AuthenticatesAndRegistersUsers;

    /**
     * Create a new authentication controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\Guard $auth
     * @param  \Illuminate\Contracts\Auth\Registrar $registrar
     * @return \App\Http\Controllers\Auth\AuthController
     */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth = $auth;
		$this->registrar = $registrar;

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

   		if ($this->auth->attempt($credentials, $request->has('remember')))
   		{
   			return redirect()->intended($this->redirectPath());
   		}

   		return redirect($this->loginPath())
   					->withInput($request->only('email', 'remember'))
   					->withErrors([
   						'email' => $this->getFailedLoginMessage(),
   					]);
   	}

}

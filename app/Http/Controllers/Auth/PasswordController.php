<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */
    use SendsPasswordResetEmails, ResetsPasswords {
        SendsPasswordResetEmails::broker insteadof ResetsPasswords;
        resetPassword as protected resetPasswordInner;
    }

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function getEmail()
    {
        return view('auth.password');
    }

    public function postEmail(Request $request) {
        return $this->sendResetLinkEmail($request);
    }

    public function getReset(Request $request, $token = null) {
        return view('auth.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function postReset(Request $request) {
        return $this->reset($request);
    }

    protected function resetPassword($user, $password) {
        $user->legacy_password = '';
        $this->resetPasswordInner($user, $password);
    }

}

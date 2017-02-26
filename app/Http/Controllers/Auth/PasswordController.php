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
    }

    /**
     * Create a new password controller instance.
     *
     * @return \App\Http\Controllers\Auth\PasswordController
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function getEmail() { return $this->showLinkRequestForm(); }
    public function postEmail(Request $request) { return $this->sendResetLinkEmail($request); }

    public function getReset(Request $request, $token = null) { return $this->showResetForm($request, $token); }
    public function postReset(Request $request) { return $this->reset($request); }


    public function showLinkRequestForm()
    {
        return view('auth.password');
    }

}

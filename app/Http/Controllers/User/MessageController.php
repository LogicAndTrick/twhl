<?php namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use Request;
use Input;
use Auth;
use DB;

class MessageController extends Controller {

	public function __construct() {
        $this->permission(['index'], true);
	}

    private static function GetUser($id) {
        if (!permission('Admin') || !$id) $id = Auth::user()->id;
        return User::findOrFail($id);
    }

	public function getIndex($id = 0) {
        $user = MessageController::GetUser($id);
        return view('user/message/index', [
            'user' => $user
        ]);
	}

    public function getView($id) {

    }

    public function getSend($to_user = 0) {

    }

    public function getDelete($id) {

    }
}

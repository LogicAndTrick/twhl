<?php namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use Request;
use Input;
use Auth;
use DB;

class PanelController extends Controller {

	public function __construct() {
        $this->permission(['index'], true);
	}

    private static function GetUser($id) {
        if (!permission('Admin') || !$id) $id = Auth::user()->id;
        return User::findOrFail($id);
    }

	public function getIndex($id = 0) {
        $user = PanelController::GetUser($id);
        return view('user/panel/index', [
            'user' => $user
        ]);
	}

    public function getEdit($id = 0) {

    }

    public function getPassword($id = 0) {

    }

    public function getAvatar($id = 0) {

    }
}

<?php namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use Request;
use Input;
use Auth;
use DB;

class UserController extends Controller {

	public function __construct() {

	}

    public function getIndex() {

    }

	public function getView($id) {
        $user = User::findOrFail($id);
        return view('user/user/index', [
            'user' => $user
        ]);
	}

    public function getMaps($id) {

    }

    public function getPosts($id) {

    }

    public function getThreads($id) {

    }

    public function getJournals($id) {

    }
}

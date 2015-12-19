<?php namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Journal;
use App\Models\Vault\VaultItem;
use Request;
use Input;
use Auth;
use DB;

class UserController extends Controller {

	public function __construct() {

	}

    public function getIndex() {
        $users = User::with([])->orderBy('created_at', 'desc')->paginate(60);
		return view('user/user/index', [
            'users' => $users
        ]);
    }

	public function getView($id) {
        $user = User::findOrFail($id);
        if (!Auth::user() || Auth::user()->id != $user->id) {
            $user->stat_profile_hits++;
            $user->save();
        }
        $journals = Journal::where('user_id', '=', $id)->orderBy('created_at', 'desc')->take(5)->get();
        $vault_items = VaultItem::with(['vault_screenshots', 'user', 'game'])->where('user_id', '=', $id)->orderBy('created_at', 'desc')->take(5)->get();
        return view('user/user/view', [
            'user' => $user,
            'journals' => $journals,
            'vault_items' => $vault_items
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

<?php

namespace App\Http\Controllers\User;

use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Journal;
use App\Models\Vault\VaultItem;
use Illuminate\Support\Facades\Auth;

#[TypedRouting]
class UserController extends Controller
{
    public function __construct()
    {
        $this->anonymous(['index', 'view']);
    }

    public function getIndex()
    {
        $users = User::with([])->orderBy('created_at', 'desc')->paginate(60);
        return view('user/user/index', [
            'users' => $users
        ]);
    }

    public function getView(int $id)
    {
        $user = User::findOrFail($id);
        if (Auth::id() != $user->id) {
            User::withoutTimestamps(fn() => $user->increment('stat_profile_hits'));
        }
        $journals = Journal::where('user_id', '=', $id)->orderBy('created_at', 'desc')->take(5)->get();
        $vault_items = VaultItem::with(['vault_screenshots', 'user', 'game'])->where('user_id', '=', $id)->orderBy('created_at', 'desc')->take(5)->get();
        return view('user/user/view', [
            'user' => $user,
            'journals' => $journals,
            'vault_items' => $vault_items
        ]);
    }
}

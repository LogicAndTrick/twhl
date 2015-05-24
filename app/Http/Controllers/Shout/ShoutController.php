<?php namespace App\Http\Controllers\Shout;

use App\Http\Controllers\Controller;
use App\Models\Shout;
use Request;
use Input;
use Auth;
use DB;

class ShoutController extends Controller {

	public function __construct() {
        $this->permission(['add'], true);
        $this->permission(['edit', 'delete'], 'Admin');
	}

	public function getShouts($last = 0) {
        return Shout::with(['user'])->where('id', '>', $last)->orderBy('created_at', 'desc')->take(50)->get()->reverse();
	}

    public function postAdd() {
        $this->validate(Request::instance(), [
            'text' => 'required|max:250'
        ]);
        return Shout::Create([
            'user_id' => Auth::user()->id,
            'content' => Request::input('text')
        ]);
    }

    public function postEdit() {
        $this->validate(Request::instance(), [
            'id' => 'required|numeric',
            'text' => 'required|max:250'
        ]);
        $shout = Shout::findOrFail(Request::input('id'));
        $shout->update([
            'content' => Request::input('text')
        ]);
        return $shout;
    }

    public function postDelete() {
        $shout = Shout::findOrFail(Request::input('id'));
        $shout->delete();
        return response()->json(['success' => true]);
    }
}

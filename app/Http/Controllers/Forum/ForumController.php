<?php namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumThread;

class ForumController extends Controller {

	public function __construct()
	{
        $this->middleware('auth', ['only' => ['getCreate', 'postCreate', 'getEdit', 'postEdit', 'getDelete', 'postDelete']]);
	}

	public function getIndex()
	{
        $forums = Forum::with(['last_post', 'last_post.thread', 'last_post.user'])->get();
		return view('forums/forum/index', [
            'forums' => $forums
        ]);
	}

    public function getView($id)
    {
        $threads = ForumThread::with(['forum'])->where('forum_id', '=', $id)->get();
        return view('forums/forum/view', [
            'threads' => $threads
        ]);
    }

}

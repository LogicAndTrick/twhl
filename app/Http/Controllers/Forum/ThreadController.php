<?php namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumPost;
use App\Models\Forums\ForumThread;

class ThreadController extends Controller {

	public function __construct()
	{
        $this->middleware('auth', ['only' => ['getCreate', 'postCreate', 'getEdit', 'postEdit', 'getDelete', 'postDelete']]);
	}

	public function getIndex()
	{
        return 'asdf';
	}

    public function getView($id, $page)
    {
        $thread = ForumThread::with(['forum'])->find($id);
        $post_query = ForumPost::with('user')->where('thread_id', '=', $id);
        $count = $post_query->getQuery()->getCountForPagination();
        $posts = $post_query->skip(($page - 1) * 50)->take(50)->get();
        $forums = Forum::with(['last_post', 'last_post.thread', 'last_post.user'])->get();
        return view('forums/thread/view', [
            'thread' => $thread,
            'posts' => $posts
        ]);
    }

}

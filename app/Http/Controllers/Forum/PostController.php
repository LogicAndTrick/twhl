<?php namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumPost;
use App\Models\Forums\ForumThread;
use Request;
use Auth;

class PostController extends Controller {

	public function __construct()
	{
        $this->permission('create', 'ForumCreate');
        $this->permission('edit', 'ForumEdit');
        $this->permission('delete', 'ForumAdmin');
	}

	public function postCreate()
	{
        $this->validate(Request::instance(), [
            'thread_id' => 'required',
            'content_text' => 'required|max:10000'
        ]);
        $thread = ForumThread::find(Request::input('thread_id'));
        ForumPost::Create([
            'thread_id' => Request::input('thread_id'),
            'forum_id' => $thread->forum_id,
            'user_id' => Auth::user()->id,
            'content_text' => Request::input('content_text'),
            'content_html' => '',
        ]);
        return redirect('thread/view/'.$thread->id.'/last');
	}
}

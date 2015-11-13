<?php namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumPost;
use App\Models\Forums\ForumThread;
use Request;
use Auth;

class PostController extends Controller {

    // TODO Forum Posts: view deleted posts
	public function __construct()
	{
        $this->permission(['create', 'edit'], 'ForumCreate');
        $this->permission('delete', 'ForumAdmin');
	}

	public function postCreate()
	{
        $id = intval(Request::input('thread_id'));
        $thread = ForumThread::findOrFail($id);
        if (!$thread->isPostable()) abort(404);
        $this->validate(Request::instance(), [
            'text' => 'required|max:10000'
        ]);
        $post = ForumPost::Create([
            'thread_id' => Request::input('thread_id'),
            'forum_id' => $thread->forum_id,
            'user_id' => Auth::user()->id,
            'content_text' => Request::input('text'),
            'content_html' => app('bbcode')->Parse(Request::input('text')),
        ]);
        return redirect('thread/view/'.$thread->id.'?page=last#post-' . $post->id);
	}

    public function getEdit($id) {
        $post = ForumPost::with(['user'])->findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        if (!$post->isEditable($thread)) abort(404);
        return view('forums/post/edit', [
            'forum' => $forum,
            'thread' => $thread,
            'post' => $post
        ]);
    }

    public function postEdit() {
        $id = intval(Request::input('id'));
        $post = ForumPost::findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        if (!$post->isEditable($thread)) abort(404);
        $this->validate(Request::instance(), [
            'content_text' => 'required|max:10000'
        ]);
        $post->update([
            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text')),
        ]);
        if(permission('ForumAdmin')) {
            $post->user_id = intval(Request::input('user_id'));
        }
        $post->save();
        return redirect('thread/view/'.$thread->id.'?page=last#post-' . $post->id);
    }

    // Administrative Tasks

    public function getDelete($id) {
        $post = ForumPost::with(['user'])->findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        return view('forums/post/delete', [
            'forum' => $forum,
            'thread' => $thread,
            'post' => $post
        ]);
    }

    public function postDelete() {
        $id = intval(Request::input('id'));
        $post = ForumPost::findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        $post->delete();
        return redirect('thread/view/'.$thread->id.'?page=last');
    }

    public function getRestore($id) {
        $post = ForumPost::with(['user'])->onlyTrashed()->findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        return view('forums/post/restore', [
            'forum' => $forum,
            'thread' => $thread,
            'post' => $post
        ]);
    }

    public function postRestore() {
        $id = intval(Request::input('id'));
        $post = ForumPost::onlyTrashed()->findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        $post->restore();
        return redirect('thread/view/'.$thread->id.'?page=last');
    }
}

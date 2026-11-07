<?php

namespace App\Http\Controllers\Forum;

use App\Events\ForumPostCreated;
use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumPost;
use App\Models\Forums\ForumThread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

#[TypedRouting]
class PostController extends Controller
{
    // TODO Forum Posts: view deleted posts
    public function __construct()
    {
        $this->anonymous(['index']);
        $this->permission(['create', 'edit'], 'ForumCreate');
        $this->permission(['delete', 'restore'], 'ForumAdmin');
    }

    public function getIndex(Request $request)
    {
        $query = ForumPost::with(['thread', 'user'])
            ->orderBy('created_at', 'desc');

        $user = $request->integer('user');
        $user = $user > 0 ? User::find($user) : null;
        if ($user) $query = $query->whereUserId($user->id)->forceIndex('forum_posts_deleted_at_user_id_index');

        $posts = $query->simplePaginate(50);

        return view('forums/post/index', [
            'posts' => $posts->appends($request->except('page')),
            'user' => $user
        ]);
    }

    public function postCreate(Request $request)
    {
        $id = $request->integer('thread_id');
        $thread = ForumThread::findOrFail($id);
        if (!$thread->isPostable()) abort(404);

        $this->validate($request, [
            'text' => ['required', 'max:10000']
        ]);
        $post = ForumPost::Create([
            'thread_id' => $request->integer('thread_id'),
            'forum_id' => $thread->forum_id,
            'user_id' => Auth::user()->id,
            'content_text' => $request->input('text'),
            'content_html' => bbcode($request->input('text')),
        ]);
        event(new ForumPostCreated($post));
        return redirect('thread/view/' . $thread->id . '?page=last#post-' . $post->id);
    }

    public function getEdit(int $id)
    {
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

    public function postEdit(Request $request)
    {
        $id = $request->integer('id');
        $post = ForumPost::findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        if (!$post->isEditable($thread)) abort(404);

        $this->validate($request, [
            'content_text' => ['required', 'string', 'max:10000']
        ]);
        $post->update([
            'content_text' => $request->input('content_text'),
            'content_html' => bbcode($request->input('content_text')),
        ]);
        if (permission('ForumAdmin')) {
            $post->user_id = $request->integer('user_id');
        }
        $post->save();
        return redirect('thread/view/' . $thread->id . '?page=last#post-' . $post->id);
    }

    // Administrative Tasks

    public function getDelete(int $id)
    {
        $post = ForumPost::with(['user'])->findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        return view('forums/post/delete', [
            'forum' => $forum,
            'thread' => $thread,
            'post' => $post
        ]);
    }

    public function postDelete(Request $request)
    {
        $id = $request->integer('id');
        $post = ForumPost::findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        $post->delete();
        return redirect('thread/view/' . $thread->id . '?page=last');
    }

    public function getRestore(int $id)
    {
        $post = ForumPost::with(['user'])->onlyTrashed()->findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        return view('forums/post/restore', [
            'forum' => $forum,
            'thread' => $thread,
            'post' => $post
        ]);
    }

    public function postRestore(Request $request)
    {
        $id = $request->integer('id');
        $post = ForumPost::onlyTrashed()->findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        $post->restore();
        return redirect('thread/view/' . $thread->id . '?page=last');
    }
}

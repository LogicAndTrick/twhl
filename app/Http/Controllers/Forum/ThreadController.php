<?php namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumPost;
use App\Models\Forums\ForumThread;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Request;
use Auth;
use Input;

class ThreadController extends Controller {

    // TODO Forum Threads: view deleted threads, tracking (?)
	public function __construct()
	{
        $this->permission('create', 'ForumCreate');
        $this->permission(['edit', 'delete', 'restore'], 'ForumAdmin');
	}

	public function getIndex($id)
	{
        return 1;
	}

    public function getView($id)
    {
        $thread = ForumThread::findOrFail($id);
        $forum = Forum::findOrFail($thread->forum_id);

        // Update stats
        $thread->stat_views++;
        $thread->save();

        $page = intval(Input::get('page')) ?: 1;
        $post_query = ForumPost::with('user')->where('thread_id', '=', $id)->orderBy('created_at');
        $count = $post_query->getQuery()->getCountForPagination();
        if (Input::get('page') == 'last') $page = ceil($count / 50);
        $posts = $post_query->skip(($page - 1) * 50)->take(50)->get();
        foreach ($posts as $p) {
            if ($p->content_html == '' && $p->content_text != '') {
                $p->content_html = app('bbcode')->Parse($p->content_text);
                $p->save();
            }
        }
        $pag = new LengthAwarePaginator($posts, $count, 50, $page, [ 'path' => Paginator::resolveCurrentPath() ]);

        return view('forums/thread/view', [
            'forum' => $forum,
            'thread' => $thread,
            'posts' => $pag
        ]);
    }

    public function getCreate($id)
    {
        $forum = Forum::where('id', '=', $id)->firstOrFail();
        return view('forums/thread/create', [
            'forum' => $forum
        ]);
    }

    public function postCreate() {
        $id = intval(Request::input('forum_id'));
        $forum = Forum::where('id', '=', $id)->firstOrFail();
        $this->validate(Request::instance(), [
            'title' => 'required|max:200',
            'text' => 'required|max:10000'
        ]);
        $thread = ForumThread::Create([
            'forum_id' => $id,
            'user_id' => Auth::user()->id,
            'title' => Request::input('title'),
            'is_open' => true
        ]);
        $post = ForumPost::Create([
            'thread_id' => $thread->id,
            'forum_id' => $id,
            'user_id' => Auth::user()->id,
            'content_text' => Request::input('text'),
            'content_html' => app('bbcode')->Parse(Request::input('text')),
        ]);
        return redirect('thread/view/'.$thread->id.'?page=last');
    }

    // Administrative Tasks

    public function getEdit($id) {
        $thread = ForumThread::findOrFail($id);
        $forum = Forum::findOrFail($thread->forum_id);
        return view('forums/thread/edit', [
            'forum' => $forum,
            'thread' => $thread
        ]);
    }

    public function postEdit() {
        $id = intval(Request::input('id'));
        $thread = ForumThread::findOrFail($id);
        $forum = Forum::findOrFail($thread->forum_id);
        $this->validate(Request::instance(), [
            'title' => 'required|max:200',
            'forum_id' => 'required|integer',
            'user_id' => 'required|integer'
        ]);
        $thread->update([
            'title' => Request::input('title'),
            'user_id' => Request::input('user_id'),
            'forum_id' => Request::input('forum_id'),
            'is_open' => !!Request::input('is_open'),
            'is_sticky' => !!Request::input('is_sticky')
        ]);
        $thread->save();
        return redirect('thread/view/'.$thread->id);
    }

    public function getDelete($id) {
        $thread = ForumThread::findOrFail($id);
        $forum = Forum::findorFail($thread->forum_id);
        return view('forums/thread/delete', [
            'forum' => $forum,
            'thread' => $thread
        ]);
    }

    public function postDelete() {
        $id = intval(Request::input('id'));
        $thread = ForumThread::findOrFail($id);
        $forum = Forum::findorFail($thread->forum_id);
        $thread->delete();
        return redirect('forum/view/'.$forum->slug);
    }

    public function getRestore($id) {
        $thread = ForumThread::onlyTrashed()->findOrFail($id);
        $forum = Forum::findorFail($thread->forum_id);
        return view('forums/thread/restore', [
            'forum' => $forum,
            'thread' => $thread
        ]);
    }

    public function postRestore() {
        $id = intval(Request::input('id'));
        $thread = ForumThread::onlyTrashed()->findOrFail($id);
        $forum = Forum::findorFail($thread->forum_id);
        $thread->restore();
        return redirect('thread/view/'.$thread->id);
    }
}

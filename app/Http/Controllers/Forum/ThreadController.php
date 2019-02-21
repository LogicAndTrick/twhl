<?php namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Accounts\UserSubscription;
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

	public function getIndex()
	{
        $auth_user = Auth::user();
        $auth_user_id = $auth_user ? $auth_user->id : 0;

        $query = ForumThread::with(['last_post', 'last_post.user', 'forum'])
            ->leftJoin('forums as f', 'f.id', '=', 'forum_threads.forum_id')
            ->whereRaw('(
                f.permission_id is null
                or f.permission_id in (
                    select up.permission_id from user_permissions up
                    left join users u on up.user_id = u.id
                    where u.id = ?
                ))', [$auth_user_id])
            ->orderBy('forum_threads.created_at', 'desc')
            ->select('forum_threads.*');

        $user = intval(Request::get('user'));
        $user = $user > 0 ? User::find($user) : null;
        if ($user) $query = $query->whereUserId($user->id);

        $threads = $query->paginate(50);

        return view('forums/thread/index', [
            'threads' => $threads,
            'user' => $user
        ]);
	}

    public function getLocatePost($id) {
        $post = ForumPost::findOrFail($id);
        $thread = ForumThread::findOrFail($post->thread_id);
        $forum = Forum::findOrFail($thread->forum_id);
        $all_posts = $thread->posts;
        $index = 0;
        for ($index = 0; $index < $all_posts->count(); $index++) {
            $p = $all_posts[$index];
            if ($p->id == $id) break;
        }
        $page = ceil(($index+1) / 50);
        return redirect('thread/view/'.$thread->id.'?page='.$page.'#post-'.$id);
    }

    public function getView($id)
    {
        $thread = ForumThread::with(['user'])->findOrFail($id);
        $forum = Forum::findOrFail($thread->forum_id);

        // Update stats
        $thread->timestamps = false;
        $thread->markAsRead();
        $thread->stat_views++;
        $thread->save();
        $thread->timestamps = true;

        $page = intval(Input::get('page')) ?: 1;
        $post_query = ForumPost::with('user')->where('thread_id', '=', $id)->whereNull('deleted_at')->orderBy('created_at');
        $count = $post_query->getQuery()->getCountForPagination();
        if (Input::get('page') == 'last') $page = ceil($count / 50);
        $posts = $post_query->skip(($page - 1) * 50)->take(50)->get();
        foreach ($posts as $p) {
            if ($p->content_html == '' && $p->content_text != '') {
                $p->content_html = app('bbcode')->Parse($p->content_text);
                $p->timestamps = false;
                $p->save();
            }
        }
        $pag = new LengthAwarePaginator($posts, $count, 50, $page, [ 'path' => Paginator::resolveCurrentPath() ]);

        return view('forums/thread/view', [
            'forum' => $forum,
            'thread' => $thread,
            'posts' => $pag,
            'subscription' => UserSubscription::getSubscription(Auth::user(), UserSubscription::FORUM_THREAD, $id, true)
        ]);
    }

    public function getSubscribe($id)
    {
        $sub = UserSubscription::getSubscription(Auth::user(), UserSubscription::FORUM_THREAD, $id);
        if (!$sub) {
            $sub = UserSubscription::Create([
                'user_id' => Auth::user()->id,
                'article_type' => UserSubscription::FORUM_THREAD,
                'article_id' => intval($id, 10),
                'send_email' => true,
                'send_push_notification' => false
            ]);
        }
        return redirect('thread/view/'.$id);
    }

    public function getUnsubscribe($id)
    {
        $sub = UserSubscription::getSubscription(Auth::user(), UserSubscription::FORUM_THREAD, $id);
        if ($sub) {
            $sub->delete();
        }
        return redirect('thread/view/'.$id);
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
        $sub = UserSubscription::Create([
            'user_id' => Auth::user()->id,
            'article_type' => UserSubscription::FORUM_THREAD,
            'article_id' => $thread->id,
            'send_email' => true,
            'send_push_notification' => false,
            'is_own_article' => true
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

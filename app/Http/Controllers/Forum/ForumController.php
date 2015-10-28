<?php namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumThread;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Request;
use Input;

class ForumController extends Controller {

	public function __construct()
	{
        $this->permission(['create', 'edit', 'delete', 'restore'], 'ForumAdmin');
	}

	public function getIndex()
	{
        $forums = Forum::with(['last_post', 'last_post.thread', 'last_post.user'])->orderBy('order_index');
        if (Input::get('deleted') !== null && permission('ForumAdmin')) $forums = $forums->withTrashed();
		return view('forums/forum/index', [
            'forums' => $forums->get()
        ]);
	}

    public function getView($slug)
    {
        // TODO Forums: thread listing - open/sticky icons, user avatars
        $page = intval(Input::get('page')) ?: 1;
        $forum = Forum::where('slug', '=', $slug)->firstOrFail();
        $thread_query = ForumThread::where('forum_threads.forum_id', '=', $forum->id)
            ->with(['last_post', 'last_post.user', 'user'])
            ->leftJoin('forum_posts as p', 'p.id', '=', 'forum_threads.last_post_id')
            ->select('forum_threads.*')
            ->orderBy('is_sticky', 'desc')
            ->orderBy('p.created_at', 'desc')
            ->orderBy('forum_threads.updated_at', 'desc');
        $count = $thread_query->getQuery()->getCountForPagination();
        $threads = $thread_query->skip(($page - 1) * 50)->take(50)->get();
        $pag = new LengthAwarePaginator($threads, $count, 50, $page, [ 'path' => Paginator::resolveCurrentPath() ]);
        return view('forums/forum/view', [
            'threads' => $pag,
            'forum' => $forum
        ]);
    }

    // Administrative Tasks

    public function getCreate() {
        return view('forums/forum/create', [

        ]);
    }

    public function postCreate() {
        $this->validate(Request::instance(), [
            'forum_name' => 'required|max:255',
            'slug' => 'required|max:15|unique:forums,slug',
            'description' => 'required',
            'permission_id' => 'integer'
        ]);
        Forum::Create([
            'name' => Request::input('forum_name'),
            'slug' => Request::input('slug'),
            'description' => Request::input('description'),
            'permission_id' => Request::input('permission_id')
        ]);
        return redirect('forum/index');
    }

    public function getEdit($id) {
        $forum = Forum::where('id', '=', $id)->firstOrFail();
        return view('forums/forum/edit', [
            'forum' => $forum
        ]);
    }

    public function postEdit() {
        $id = intval(Request::input('id'));
        $forum = Forum::where('id', '=', $id)->firstOrFail();
        $this->validate(Request::instance(), [
            'forum_name' => 'required|max:255',
            'slug' => 'required|max:15|unique:forums,slug,'.$id,
            'description' => 'required',
            'permission_id' => 'integer'
        ]);
        $forum->update([
            'name' => Request::input('forum_name'),
            'slug' => Request::input('slug'),
            'description' => Request::input('description'),
            'permission_id' => Request::input('permission_id'),
        ]);
        $forum->save();
        return redirect('forum/index');
    }

    public function getDelete($id) {
        $forum = Forum::where('id', '=', $id)->firstOrFail();
        return view('forums/forum/delete', [
            'forum' => $forum
        ]);
    }

    public function postDelete() {
        $id = intval(Request::input('id'));
        $forum = Forum::where('id', '=', $id)->firstOrFail();
        $forum->delete();
        return redirect('forum/index');
    }

    public function getRestore($id) {
        $forum = Forum::onlyTrashed()->where('id', '=', $id)->firstOrFail();
        return view('forums/forum/restore', [
            'forum' => $forum
        ]);
    }

    public function postRestore() {
        $id = intval(Request::input('id'));
        $forum = Forum::onlyTrashed()->where('id', '=', $id)->firstOrFail();
        $forum->restore();
        return redirect('forum/index');
    }

}

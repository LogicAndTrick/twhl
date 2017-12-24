<?php namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumThread;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Request;
use Input;
use DB;

class ForumController extends Controller {

	public function __construct()
	{
        $this->permission(['create', 'edit', 'delete', 'restore'], 'ForumAdmin');
	}

	public function getIndex()
	{
        $forums = Forum::with(['last_post', 'last_post.thread', 'last_post.user'])->orderBy('order_index');
        $show_deleted = Input::get('deleted') !== null && permission('ForumAdmin');
        if ($show_deleted) $forums = $forums->withTrashed();
        $forums = $forums->get();

        $recent_threads = ForumThread::with(['last_post', 'last_post.user'])->from(
            DB::raw(
                '(' . implode(' union all ', $forums->map(function ($f) {
                    return "(select *
                            from forum_threads t
                            where t.forum_id = {$f->id}
                            and t.deleted_at is null
                            order by t.updated_at desc
                            limit 5)";
                })->toArray()) . ') forum_threads'
            )
        )->get();

		return view('forums/forum/index', [
            'forums' => $forums,
            'recent_threads' => $recent_threads,
            'show_deleted' => $show_deleted
        ]);
	}

	public function getId($id) {
	    $forum = Forum::findOrFail($id);
	    return redirect('/forum/view/' . $forum->slug);
    }

    public function getView($slug)
    {
        $page = intval(Input::get('page')) ?: 1;
        $forum = Forum::where('slug', '=', $slug)->firstOrFail();
        $thread_query = ForumThread::where('forum_id', '=', $forum->id)
            ->with(['last_post', 'last_post.user', 'user'])
            ->orderBy('is_sticky', 'desc')
            ->orderBy('last_post_at', 'desc')
            ->orderBy('updated_at', 'desc');
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

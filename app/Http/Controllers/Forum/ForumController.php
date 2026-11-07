<?php

namespace App\Http\Controllers\Forum;

use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumThread;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

#[TypedRouting]
class ForumController extends Controller
{
    public function __construct()
    {
        $this->anonymous(['index', 'view', 'id']);
        $this->permission(['create', 'edit', 'delete', 'restore'], 'ForumAdmin');
    }

    public function getIndex(Request $request)
    {
        $forums = Forum::with(['last_post', 'last_post.thread', 'last_post.user'])->orderBy('order_index');
        $show_deleted = $request->input('deleted') !== null && permission('ForumAdmin');
        if ($show_deleted) $forums = $forums->withTrashed();
        $forums = $forums->get();

        $recent_threads = ForumThread::with(['last_post', 'last_post.user'])->from(
            DB::raw(
                '(' . implode(' union all ', $forums->map(function ($f) {
                    return "(select *
                            from forum_threads t
                            where t.forum_id = {$f->id}
                            and t.deleted_at is null
                            order by t.last_post_at desc
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

    public function getId(int $id)
    {
        $forum = Forum::findOrFail($id);
        return redirect('/forum/view/' . $forum->slug);
    }

    public function getView(Request $request, string $slug)
    {
        $page = $request->integer('page', 1) ?: 1;
        $forum = Forum::where('slug', '=', $slug)->firstOrFail();
        $thread_query = ForumThread::where('forum_id', '=', $forum->id)
            ->with(['last_post', 'last_post.user', 'user'])
            ->orderBy('is_sticky', 'desc')
            ->orderBy('last_post_at', 'desc')
            ->orderBy('updated_at', 'desc');
        $count = $thread_query->count();
        $threads = $thread_query->skip(($page - 1) * 50)->take(50)->get();
        $pag = new LengthAwarePaginator($threads, $count, 50, $page, ['path' => Paginator::resolveCurrentPath()]);
        return view('forums/forum/view', [
            'threads' => $pag,
            'forum' => $forum
        ]);
    }

    // Administrative Tasks

    public function getCreate()
    {
        return view('forums/forum/create');
    }

    public function postCreate(Request $request)
    {
        $this->validate($request, [
            'forum_name' => ['required', 'max:255'],
            'slug' => ['required', 'max:15', 'unique:forums,slug'],
            'description' => ['required'],
            'permission_id' => ['integer']
        ]);
        Forum::Create([
            'name' => $request->input('forum_name'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'permission_id' => $request->filled('permission_id') ? $request->integer('permission_id') : null
        ]);
        return redirect('forum/index');
    }

    public function getEdit(int $id)
    {
        $forum = Forum::findOrFail($id);
        return view('forums/forum/edit', [
            'forum' => $forum
        ]);
    }

    public function postEdit(Request $request)
    {
        $id = $request->integer('id');
        $forum = Forum::findOrFail($id);
        $this->validate($request, [
            'forum_name' => ['required', 'max:255'],
            'slug' => ['required', 'max:15', 'unique:forums,slug,' . $id],
            'description' => ['required'],
            'permission_id' => ['integer']
        ]);
        $forum->update([
            'name' => $request->input('forum_name'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'permission_id' => $request->filled('permission_id') ? $request->integer('permission_id') : null
        ]);
        $forum->save();
        return redirect('forum/index');
    }

    public function getDelete(int $id)
    {
        $forum = Forum::findOrFail($id);
        return view('forums/forum/delete', [
            'forum' => $forum
        ]);
    }

    public function postDelete(Request $request)
    {
        $id = $request->integer('id');
        $forum = Forum::findOrFail($id);
        $forum->delete();
        return redirect('forum/index');
    }

    public function getRestore(int $id)
    {
        $forum = Forum::onlyTrashed()->findOrFail($id);
        return view('forums/forum/restore', [
            'forum' => $forum
        ]);
    }

    public function postRestore(Request $request)
    {
        $id = $request->integer('id');
        $forum = Forum::onlyTrashed()->findOrFail($id);
        $forum->restore();
        return redirect('forum/index');
    }
}

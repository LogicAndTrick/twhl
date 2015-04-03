<?php namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumThread;
use Request;
use Input;

class ForumController extends Controller {

    // TODO Forums: edit, delete on forums
	public function __construct()
	{
        $this->permission(['create', 'edit', 'delete'], 'ForumAdmin');
	}

	public function getIndex()
	{
        $forums = Forum::with(['last_post', 'last_post.thread', 'last_post.user'])->get();
		return view('forums/forum/index', [
            'forums' => $forums
        ]);
	}

    public function getView($slug, $page = 1)
    {
        // TODO Forums: pagination
        $forum = Forum::where('slug', '=', $slug)->firstOrFail();
        $thread_query = ForumThread::where('forum_id', '=', $forum->id);
        $count = $thread_query->getQuery()->getCountForPagination();
        $threads = $thread_query->skip(($page - 1) * 50)->take(50)->get();
        return view('forums/forum/view', [
            'threads' => $threads
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

}

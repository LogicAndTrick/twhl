<?php namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Forums\Forum;
use App\Models\Forums\ForumThread;
use Request;

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
        // TODO Forums: pick from a list of permissions
        return view('forums/forum/create', [

        ]);
    }

    public function postCreate() {
        $this->validate(Request::instance(), [
            'forum_name' => 'required|max:255',
            'slug' => 'required|max:15|unique:forums,slug',
            'description' => 'required',
        ]);
        Forum::Create([
            'name' => Request::input('forum_name'),
            'slug' => Request::input('slug'),
            'description' => Request::input('description'),
        ]);
        return redirect('forum/index');
    }

}

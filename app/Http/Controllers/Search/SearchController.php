<?php namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Forums\ForumPost;
use App\Models\Forums\ForumThread;
use App\Models\Vault\VaultItem;
use App\Models\Wiki\WikiRevision;
use DB;
use Request;
use Auth;

class SearchController extends Controller {

	public function __construct()
	{

	}

    public function getIndex()
   	{
        $search = trim(Request::input('search'));
        $searched = !!$search;

        $users = null;
        $vaults = null;
        $wikis = null;
        $threads = null;
        $posts = null;

        if ($searched) {
            $user = Auth::user();
            $user_id = $user ? $user->id : 0;

            $threads = ForumThread::with(['user', 'forum', 'last_post', 'last_post.user'])
                ->leftJoin('forums as f', 'f.id', '=', 'forum_threads.forum_id')
                ->whereRaw('(
                    f.permission_id is null
                    or f.permission_id in (
                        select up.permission_id from user_permissions up
                        left join users u on up.user_id = u.id
                        where u.id = ?
                    ))', [ $user_id ])
                ->whereRaw('(
                        MATCH (forum_threads.title) AGAINST (?)
                        OR forum_threads.title LIKE CONCAT(\'%\', ?, \'%\')
                    )', [ $search, $search ])
                ->select('forum_threads.*')
                ->paginate()
                ->appends(Request::except('page'));

            $posts = ForumPost::with(['user', 'thread', 'forum'])
                ->leftJoin('forums as f', 'f.id', '=', 'forum_posts.forum_id')
                ->whereRaw('(
                    f.permission_id is null
                    or f.permission_id in (
                        select up.permission_id from user_permissions up
                        left join users u on up.user_id = u.id
                        where u.id = ?
                    ))', [ $user_id ])
                ->whereRaw('MATCH (forum_posts.content_text) AGAINST (?)', [ $search ])
                ->select('forum_posts.*')
                ->paginate()
                ->appends(Request::except('page'));

            $wikis = WikiRevision::with(['user', 'wiki_object'])
                ->where('is_active', '=', 1)
                ->whereRaw('(
                        MATCH (wiki_revisions.content_text, wiki_revisions.title) AGAINST (?)
                        OR wiki_revisions.title LIKE CONCAT(\'%\', ?, \'%\')
                    )', [ $search, $search ])
                ->paginate()
                ->appends(Request::except('page'));

            $vaults = VaultItem::with(['user', 'vault_category', 'vault_type'])
                ->whereRaw('(
                        MATCH (vault_items.content_text, vault_items.name) AGAINST (?)
                        OR vault_items.name LIKE CONCAT(\'%\', ?, \'%\')
                    )', [ $search, $search ])
                ->paginate()
                ->appends(Request::except('page'));

            //SELECT * FROM `users` WHERE name like CONCAT('%', 'user', '%')
            $users = User::with([])
                ->whereRaw('(
                        MATCH (users.name, users.info_biography_text) AGAINST (?)
                        OR users.name LIKE CONCAT(\'%\', ?, \'%\')
                    )', [ $search, $search ])
                ->paginate()
                ->appends(Request::except('page'));

        }

   		return view('search/index', [
            'searched' => $searched,
            'search' => $search,
            'results_threads' => $threads,
            'results_posts' => $posts,
            'results_wikis' => $wikis,
            'results_users' => $users,
            'results_vaults' => $vaults
       ]);
   	}

}

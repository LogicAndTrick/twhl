<?php namespace App\Http\Controllers;

use App\Models\Accounts\User;
use App\Models\Forums\ForumPost;
use App\Models\Forums\ForumThread;
use App\Models\Journal;
use App\Models\News;
use App\Models\Polls\Poll;
use App\Models\Polls\PollItemVote;
use App\Models\Vault\Motm;
use App\Models\Vault\VaultItem;
use App\Models\Wiki\WikiObject;
use DB;
use Auth;

class HomeController extends Controller {

	public function __construct()
	{

	}

	public function index()
	{
        $user = Auth::user();
        $user_id = $user ? $user->id : 0;

        // Vault section
        $motm = Motm::with(['vault_item', 'vault_item.user', 'vault_item.vault_screenshots'])
            ->whereNotNull('item_id')
            ->orderByRaw('(year * 10) + month DESC')
            ->first();

        $new_maps = VaultItem::with(['user', 'vault_screenshots'])
            ->whereIn('type_id', [1,4]) // Maps and mods
            ->orderBy('updated_at', 'desc')
            ->limit(6)
            ->get();

        $excluded = $new_maps->map(function($m) { return $m->id; });
        if ($motm) $excluded[] = $motm->item_id;

        $top_maps = VaultItem::with(['user', 'vault_screenshots'])
            ->whereIn('type_id', [1,4]) // Maps and mods
            ->whereCategoryId(2) // Completed
            ->whereFlagRatings(true)
            ->where('stat_ratings', '>=', 5)
            ->whereRaw('(ceil(stat_average_rating * 2) / 2) >= 4.5')
            ->whereIn('id', $excluded, 'and', true) // NOT in
            ->orderByRaw('RAND()')
            ->limit(5)
            ->get();

        // Wiki section
        $wiki_edits = WikiObject::with(['current_revision', 'current_revision.user'])
            ->orderBy('updated_at', 'desc')
            ->limit(12)
            ->get();

        // Forums section
        $threads = ForumThread::with(['last_post', 'last_post.user'])
            ->leftJoin('forums as f', 'f.id', '=', 'forum_threads.forum_id')
            ->leftJoin('forum_posts as p', 'p.id', '=', 'forum_threads.last_post_id')
            ->whereRaw('(
                f.permission_id is null
                or f.permission_id in (
                    select up.permission_id from user_permissions up
                    left join users u on up.user_id = u.id
                    where u.id = ?
                ))', [$user_id])
            ->orderBy('p.updated_at', 'desc')
            ->take(5)
            ->select('forum_threads.*')
            ->get();
        $thread_users = User::with([])
            ->from(
                DB::raw(
                    '(' . implode(' union all ', $threads->map(function ($t) {
                        return "(select distinct p.thread_id, u.*
                                from forum_posts p
                                left join users u on p.user_id = u.id
                                where p.thread_id = {$t->id}
                                and p.user_id != {$t->last_post->user_id}
                                and p.deleted_at is null
                                and u.deleted_at is null
                                order by p.updated_at desc
                                limit 5)";
                    })->toArray()) . ') users'
                )
            )->get();

        // Journals section
        $journals = Journal::with(['user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // News section
        $newses = News::with(['user'])
            ->orderBy('created_at', 'desc')
            ->take(1)
            ->get();

        // Poll section
        $polls = Poll::with(['items'])
            ->orderBy('created_at', 'desc')
            ->take(1)
            ->get();

        $user_votes = [];
        $user_polls = [];
        if (Auth::user()) {
            $pids = $polls->map(function($x) { return $x->id; })->toArray();
            $uvotes = PollItemVote::whereIn('poll_id', $pids)->whereUserId(Auth::user()->id)->get();
            $user_votes = $uvotes->map(function($x) { return $x->item_id; })->toArray();
            $user_polls = $uvotes->map(function($x) { return $x->poll_id; })->toArray();
        }

		return view('home/index', [
            'motm' => $motm,
            'top_maps' => $top_maps,
            'new_maps' => $new_maps,
            'wiki_edits' => $wiki_edits,
            'threads' => $threads,
            'thread_users' => $thread_users,
            'journals' => $journals,
            'newses' => $newses,
            'polls' => $polls,
            'user_votes' => $user_votes,
            'user_polls' => $user_polls
        ]);
	}

}

<?php namespace App\Http\Controllers;

use App\Models\Accounts\User;
use App\Models\Competitions\Competition;
use App\Models\Competitions\CompetitionStatus;
use App\Models\Forums\ForumPost;
use App\Models\Forums\ForumThread;
use App\Models\Journal;
use App\Models\News;
use App\Models\Polls\Poll;
use App\Models\Polls\PollItemVote;
use App\Models\Vault\Motm;
use App\Models\Vault\VaultItem;
use App\Models\Wiki\WikiObject;
use App\Models\Wiki\WikiRevision;
use App\Models\Wiki\WikiRevisionMeta;
use DB;
use Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller {

	public function __construct()
	{

	}

	public function index()
	{
        $user = Auth::user();
        $user_id = $user ? $user->id : 0;

        // Vault section
        $new_maps = VaultItem::with(['user', 'vault_screenshots'])
            ->whereIn('type_id', [1,4]) // Maps and mods
            ->orderBy('updated_at', 'desc')
            ->limit(4)
            ->get();

        // Competitions
        $comps = Competition::with(['type', 'judge_type'])
            ->whereIn('status_id', [CompetitionStatus::ACTIVE, CompetitionStatus::JUDGING, CompetitionStatus::VOTING])
            ->get();

        // Wiki section
        $wiki_edits = WikiObject::with(['current_revision', 'current_revision.user'])
            ->orderBy('updated_at', 'desc')
            ->limit(6)
            ->get();

        $wiki_articles = $this->getWikiFeatureData();

        // Forums section
        $threads = ForumThread::with(['last_post', 'last_post.user'])
            ->leftJoin('forums as f', 'f.id', '=', 'forum_threads.forum_id')
            ->whereRaw('(
                f.permission_id is null
                or f.permission_id in (
                    select up.permission_id from user_permissions up
                    left join users u on up.user_id = u.id
                    where u.id = ?
                ))', [$user_id])
            ->orderBy('forum_threads.last_post_at', 'desc')
            ->take(5)
            ->select('forum_threads.*')
            ->get();

        // Journals section
        $journals = Journal::with(['user'])
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        // News section
        $newses = News::with(['user'])
            ->orderBy('created_at', 'desc')
            ->take(1)
            ->get();

        // Poll section
        $polls = Poll::with(['items'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->take(1)
            ->get();

        // Onliners section
        $onliners = User::with([])->orderBy('last_access_time', 'desc')->take(7)->get();

        $user_votes = [];
        $user_polls = [];
        if (Auth::user()) {
            $pids = $polls->map(function($x) { return $x->id; })->toArray();
            $uvotes = PollItemVote::whereIn('poll_id', $pids)->whereUserId(Auth::user()->id)->get();
            $user_votes = $uvotes->map(function($x) { return $x->item_id; })->toArray();
            $user_polls = $uvotes->map(function($x) { return $x->poll_id; })->toArray();
        }

		return view('home/index', [
            'new_maps' => $new_maps,
            'competitions' => $comps,
            'wiki_edits' => $wiki_edits,
            'wiki_articles' => $wiki_articles,
            'threads' => $threads,
            'journals' => $journals,
            'newses' => $newses,
            'polls' => $polls,
            'user_votes' => $user_votes,
            'user_polls' => $user_polls,
            'onliners' => $onliners
        ]);
	}

    private function getWikiFeatureData()
    {
        return [
            // Featured tutorial lasts 1 day
            'featured_tutorials' => \Illuminate\Support\Facades\Cache::remember('featured_tutorials', 60 * 24, function () {
                return WikiRevision::whereIsActive(true)
                    ->whereRaw("id in (
                        select m.revision_id
                        from wiki_revision_metas as m
                        where m.key = ?
                        and m.value = ?
                        group by m.revision_id
                    )", [ WikiRevisionMeta::CATEGORY, WikiRevision::CreateSlug('Tutorials') ])
                    ->orderByRaw('RAND()')
                    ->limit(3)
                    ->get();
            }),
            // Recent edits lasts 1 minute
            'recent_edits' => Cache::remember('recent_edits', 1, function () {
                return WikiObject::with(['current_revision', 'current_revision.user'])
                    ->orderBy('updated_at', 'desc')
                    ->limit(6)
                    ->get();
            })
        ];
    }

}

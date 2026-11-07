<?php

namespace App\Http\Controllers\Search;

use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Forums\ForumPost;
use App\Models\Forums\ForumThread;
use App\Models\Vault\VaultItem;
use App\Models\Wiki\WikiRevision;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

#[TypedRouting]
class SearchController extends Controller
{
    public function __construct()
    {
        $this->anonymous(['index']);
    }

    public function getIndex(Request $request)
    {
        $errors = new ViewErrorBag();

        $search = trim($request->input('search'));
        $max_search_length = Auth::check() ? 60 : 30;
        if (mb_strlen($search) > $max_search_length) {
            $search = mb_substr($search, 0, $max_search_length);
        }
        $searched = !!$search;

        $perPage = 15;
        $page = $request->integer('page', 1);
        if (!$page || $page < 1) $page = 1;
        $maxAttempts = 10;

        if (!Auth::check()) {
            $page = 1;
            $maxAttempts = 2;
            $validator = Validator::make($request->all(), [
                'g-recaptcha-response' => ['required', 'recaptcha'],
                'search' => ['required', 'max:30']
            ]);
            if ($validator->fails()) {
                $errors->put('default', $validator->errors());
                $searched = false;
            }
        }

        $users = null;
        $vaults = null;
        $wikis = null;
        $threads = null;
        $posts = null;

        if ($searched && app(RateLimiter::class)->tooManyAttempts($this->throttleKey($request), $maxAttempts)) {
            $errors->put('default', new MessageBag()->add('search', 'Too many search attempts. Wait a minute and try again.'));
            $searched = false;
        }

        if ($searched) {
            app(RateLimiter::class)->hit($this->throttleKey($request), 60);

            $threads = ForumThread::with(['user', 'forum', 'last_post', 'last_post.user'])
                ->whereRaw('(
                        MATCH (forum_threads.title) AGAINST (?)
                        OR forum_threads.title LIKE CONCAT(\'%\', ?, \'%\')
                    )', [$search, $search])
                ->paginate(perPage: $perPage, page: $page)
                ->appends($request->except('page'));

            $posts = ForumPost::with(['user', 'thread', 'forum'])
                ->leftJoin('forum_threads as t', 't.id', '=', 'forum_posts.thread_id')
                ->whereRaw('MATCH (forum_posts.content_text) AGAINST (?)', [$search])
                ->whereRaw('t.deleted_at is null')
                ->select('forum_posts.*')
                ->paginate(perPage: $perPage, page: $page)
                ->appends($request->except('page'));

            $wikis = WikiRevision::with(['user', 'wiki_object'])
                ->where('is_active', '=', 1)
                ->whereRaw('(
                        MATCH (wiki_revisions.content_text, wiki_revisions.title) AGAINST (?)
                        OR wiki_revisions.title LIKE CONCAT(\'%\', ?, \'%\')
                    )', [$search, $search])
                ->paginate(perPage: $perPage, page: $page)
                ->appends($request->except('page'));

            $vaults = VaultItem::with(['user', 'vault_category', 'vault_type'])
                ->whereRaw('(
                        MATCH (vault_items.content_text, vault_items.name) AGAINST (?)
                        OR vault_items.name LIKE CONCAT(\'%\', ?, \'%\')
                    )', [$search, $search])
                ->paginate(perPage: $perPage, page: $page)
                ->appends($request->except('page'));

            $users = User::with([])
                ->whereRaw('(
                        MATCH (users.name, users.info_biography_text) AGAINST (?)
                        OR users.name LIKE CONCAT(\'%\', ?, \'%\')
                    )', [$search, $search])
                ->paginate(perPage: $perPage, page: $page)
                ->appends($request->except('page'));
        }

        return view('search/index', [
            'searched' => $searched,
            'search' => $search,
            'max_search_length' => $max_search_length,
            'results_threads' => $threads,
            'results_posts' => $posts,
            'results_wikis' => $wikis,
            'results_users' => $users,
            'results_vaults' => $vaults,
            'errors' => $errors,
        ]);
    }

    protected function throttleKey(Request $request)
    {
        return Str::transliterate(Str::lower(SearchController::class . '|search|' . $request->ip()));
    }
}

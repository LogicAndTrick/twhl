<?php namespace App\Http\Controllers\Comments;

use App\Events\CommentCreated;
use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Accounts\UserNotification;
use App\Models\Accounts\UserSubscription;
use App\Models\Comments\CommentArticle;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentMeta;
use Request;
use Auth;
use DB;
use Input;

class CommentController extends Controller {

    public static $comment_config = [
        Comment::NEWS => array(
            'model' => '\App\Models\News',
            'redirect' => 'news/view/{id}{bookmark}',
            'auth_create' => 'NewsComment',
            'auth_moderate' => 'NewsAdmin'
        ),
        Comment::JOURNAL => array(
            'model' => '\App\Models\Journal',
            'redirect' => 'journal/view/{id}{bookmark}',
            'auth_create' => 'JournalComment',
            'auth_moderate' => 'JournalAdmin'
        ),
        Comment::VAULT => [
            'model' => '\App\Models\Vault\VaultItem',
            'redirect' => 'vault/view/{id}{bookmark}',
            'auth_create' => 'VaultComment',
            'auth_moderate' => 'VaultAdmin',
            'meta' => [
                CommentMeta::RATING => [
                    'key' => 'meta_rating',
                    'one_per_user' => true,
                    'valid' => '/^(1|2|3|4|5)$/i' // Rating is a value from 1-5
                ]
            ]
        ],
        Comment::POLL => [
            'model' => '\App\Models\Polls\Poll',
            'redirect' => 'poll/view/{id}{bookmark}',
            'auth_create' => 'PollComment',
            'auth_moderate' => 'PollAdmin'
        ],
        Comment::WIKI => [
            'model' => '\App\Models\Wiki\WikiObject',
            'redirect' => 'wiki/view/{id}{bookmark}',
            'auth_create' => 'WikiComment',
            'auth_moderate' => 'WikiAdmin'
        ],
        // Leaving this out for now. Not sure comments on reviews are useful enough.
        //Comment::REVIEW => array(
        //    'model' => '\App\Models\Vault\VaultItemReview',
        //    'redirect' => 'vault-review/view/{id}{bookmark}',
        //    'auth_create' => 'VaultComment',
        //    'auth_moderate' => 'VaultAdmin'
        //)
    ];
    
	public function __construct()
	{
        // Just assert for a logged-in user, do the permission checks dynamically
        $this->permission(['create', 'edit', 'delete', 'subscribe', 'unsubscribe'], true);
	}
    
    private function replaceUrlVars($url, $comment)
    {
        $bookmark = '#comment';
        if ($comment->deleted_at) $bookmark .= 's';
        else $bookmark .= '-'.$comment->id;

        $str = $url;
        $str = str_ireplace('{id}', $comment->article_id, $str);
        $str = str_ireplace('{bookmark}', $bookmark, $str);
        return $str;
    }

    public function getIndex() {
        $query = Comment::with(['user', 'comment_metas'])
            ->orderBy('created_at', 'desc');

        $user = intval(Request::get('user'));
        $user = $user > 0 ? User::find($user) : null;
        if ($user) $query = $query->whereUserId($user->id);

        $comments = $query->paginate(50);

        $conditions = [];
        foreach ($comments as $comment) {
            $conditions[] = "(article_type  = '{$comment->article_type}' AND article_id = $comment->article_id)";
        }
        $sql = '(' . implode(' OR ', $conditions) . ')';

        return view('comments/index', [
            'comments' => $comments->appends(Input::except('page')),
            'user' => $user
        ]);
    }

	public function postCreate()
	{
        $type = Request::input('article_type');
        $id = intval(Request::input('article_id'));

        if (!array_key_exists($type, CommentController::$comment_config)) abort(404);
        $config = CommentController::$comment_config[$type];

        if (!permission($config['auth_create'])) abort(404);

        $this->validate(Request::instance(), [
            'text' => 'required|max:10000'
        ]);

        $article = call_user_func($config['model'] . '::findOrFail', $id);
        if (!permission('Admin') && $article->commentsIsLocked()) abort(404);

        $comment = Comment::Create([
            'article_type' => $type,
            'article_id' => $id,
            'user_id' => Auth::user()->id,
            'content_text' => Request::input('text'),
            'content_html' => app('bbcode')->Parse(Request::input('text')),
        ]);
        if (array_key_exists('meta', $config) && is_array($config['meta'])) {
            $metas = [];
            foreach ($config['meta'] as $key => $meta) {
                if (!$article->commentsCanAddMeta($key)) continue;
                $val = strval(Request::input($meta['key']));
                if ($val && preg_match($meta['valid'], $val)) {
                    $metas[] = new CommentMeta([ 'key' => $key, 'value' => $val]);
                    if (isset($meta['one_per_user']) && $meta['one_per_user']) {
                        DB::statement(
                            'DELETE m FROM comment_metas AS m
                            LEFT JOIN comments AS c ON c.id = m.comment_id
                            WHERE c.article_type = ? AND c.article_id = ? AND c.user_id = ? AND m.key = ?',
                            [$type, $id, $comment->user_id, $key]);
                    }
                }
            }
            $comment->comment_metas()->saveMany($metas);
        }
        DB::statement('CALL update_comment_statistics(?, ?, ?);', [$type, $id, $comment->user_id]);
        event(new CommentCreated($comment));
        return redirect($this->replaceUrlVars($config['redirect'], $comment) );
	}

    public function getEdit($id) {
        $comment = Comment::with(['comment_metas', 'user'])->findOrFail($id);
        if (!$comment->isEditable()) abort(404);
        return view('comments/edit', [
            'comment' => $comment
        ]);
    }

    public function postEdit() {
        $comment_id = intval(Request::input('id'));
        $comment = Comment::findOrFail($comment_id);
        if (!$comment->isEditable()) abort(404);

        $type = $comment->article_type;
        $id = $comment->article_id;

        if (!array_key_exists($type, CommentController::$comment_config)) abort(404);
        $config = CommentController::$comment_config[$type];

        if (!permission($config['auth_create'])) abort(404);

        $this->validate(Request::instance(), [
            'text' => 'required|max:10000'
        ]);

        $article = call_user_func($config['model'] . '::findOrFail', $id);
        $comment->update([
            'content_text' => Request::input('text'),
            'content_html' => app('bbcode')->Parse(Request::input('text')),
        ]);

        DB::statement('DELETE FROM comment_metas WHERE comment_id = ?', [$comment->id]);
        if (array_key_exists('meta', $config) && is_array($config['meta'])) {
            $metas = [];
            foreach ($config['meta'] as $key => $meta) {
                if (!$article->commentsCanAddMeta($key)) continue;
                $val = strval(Request::input($meta['key']));
                if ($val && preg_match($meta['valid'], $val)) {
                    $metas[] = new CommentMeta([ 'key' => $key, 'value' => $val]);
                    if (isset($meta['one_per_user']) && $meta['one_per_user']) {
                        DB::statement(
                            'DELETE m FROM comment_metas AS m
                            LEFT JOIN comments AS c ON c.id = m.comment_id
                            WHERE c.article_type = ? AND c.article_id = ? AND c.user_id = ? AND m.key = ?',
                            [$type, $id, $comment->user_id, $key]);
                    }
                }
            }
            $comment->comment_metas()->saveMany($metas);
        }
        DB::statement('CALL update_comment_statistics(?, ?, ?);', [$type, $id, $comment->user_id]);
        return redirect($this->replaceUrlVars($config['redirect'], $comment) );
    }

    // Subscriptions

    public function getSubscribe($type, $article_id)
    {
        $sub = Comment::getSubscription(Auth::user(), $type, $article_id);
        if (!$sub) {
            $sub = UserSubscription::Create([
                'user_id' => Auth::user()->id,
                'article_type' => UserNotification::GetTypeFromCommentType($type),
                'article_id' => intval($article_id, 10),
                'send_email' => true,
                'send_push_notification' => false
            ]);
        }

        $config = CommentController::$comment_config[$type];
        $str = $config['redirect'];
        $str = str_ireplace('{id}', $article_id, $str);
        $str = str_ireplace('{bookmark}', '#comments', $str);
        return redirect($str);
    }

    public function getUnsubscribe($type, $article_id)
    {
        $sub = Comment::getSubscription(Auth::user(), $type, $article_id);
        if ($sub) {
            $sub->delete();
        }

        $config = CommentController::$comment_config[$type];
        $str = $config['redirect'];
        $str = str_ireplace('{id}', $article_id, $str);
        $str = str_ireplace('{bookmark}', '#comments', $str);
        return redirect($str);
    }

    // Administrative Tasks

    public function getDelete($id) {
        $comment = Comment::with([ 'user' ])->findOrFail($id);
        if (!$comment->isDeletable()) abort(404);
        return view('comments/delete', [
            'comment' => $comment
        ]);
    }

    public function postDelete() {
        $comment_id = intval(Request::input('id'));
        $comment = Comment::findOrFail($comment_id);
        if (!$comment->isDeletable()) abort(404);

        $type = $comment->article_type;
        $id = $comment->article_id;

        if (!array_key_exists($type, CommentController::$comment_config)) abort(404);
        $config = CommentController::$comment_config[$type];

        if (!permission($config['auth_moderate'])) abort(404);

        $comment->delete();
        DB::statement('CALL update_comment_statistics(?, ?, ?);', [$type, $id, $comment->user_id]);

        return redirect($this->replaceUrlVars($config['redirect'], $comment) );
    }

    public function getRestore($id) {
        $comment = Comment::with([ 'user' ])->onlyTrashed()->findOrFail($id);
        return view('comments/restore', [
            'comment' => $comment
        ]);
    }

    public function postRestore() {

        $id = intval(Request::input('id'));
        $comment = Comment::onlyTrashed()->findOrFail($id);
        if (!$comment->isDeletable()) abort(404);

        $type = $comment->article_type;
        $id = $comment->article_id;

        if (!array_key_exists($type, CommentController::$comment_config)) abort(404);
        $config = CommentController::$comment_config[$type];

        if (!permission($config['auth_moderate'])) abort(404);

        $comment->restore();
        DB::statement('CALL update_comment_statistics(?, ?, ?);', [$type, $id, $comment->user_id]);

        return redirect($this->replaceUrlVars($config['redirect'], $comment) );
    }
}

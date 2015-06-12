<?php namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentMeta;
use Request;
use Auth;
use DB;

class CommentController extends Controller {

    private $comment_config = [
        Comment::NEWS => array(
            'model' => '\App\Models\News',
            'redirect' => 'news/view/{id}',
            'auth_create' => 'NewsComment',
            'auth_moderate' => 'NewsAdmin'
        ),
        Comment::JOURNAL => array(
            'model' => '\App\Models\Journal',
            'redirect' => 'journal/view/{id}',
            'auth_create' => 'JournalComment',
            'auth_moderate' => 'JournalAdmin'
        ),
        Comment::VAULT => [
            'model' => '\App\Models\Vault\VaultItem',
            'redirect' => 'vault/view/{id}',
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
            'redirect' => 'poll/view/{id}',
            'auth_create' => 'PollComment',
            'auth_moderate' => 'PollAdmin'
        ]
        //Comment::MOTM => array(
        //    'model' => '\App\Models\Motm\Motm',
        //    'redirect' => 'motm/view/{id}',
        //    'auth_create' => 'MotmComment',
        //    'auth_moderate' => 'MotmAdmin'
        //)
    ];
	public function __construct()
	{
        // Just assert for a logged-in user, do the permission checks dynamically
        $this->permission(['create', 'edit', 'delete'], true);
	}

	public function postCreate()
	{
        $type = Request::input('article_type');
        $id = intval(Request::input('article_id'));

        if (!array_key_exists($type, $this->comment_config)) abort(404);
        $config = $this->comment_config[$type];

        if (!permission($config['auth_create'])) abort(404);

        $this->validate(Request::instance(), [
            'text' => 'required|max:10000'
        ]);

        $article = call_user_func($config['model'] . '::findOrFail', $id);
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
                $val = strval(Request::input($meta['key']));
                if ($val && preg_match('/^(1|2|3|4|5)$/i', $val)) {
                    $metas[] = new CommentMeta([ 'key' => $key, 'value' => $val]);
                    if (isset($meta['one_per_user']) && $meta['one_per_user']) {
                        DB::statement('DELETE m FROM comment_metas AS m LEFT JOIN comments AS c ON c.id = m.comment_id WHERE article_type = ? AND article_id = ? AND user_id = ?', [$type, $id, $comment->user_id]);
                    }
                }
            }
            $comment->comment_metas()->saveMany($metas);
        }
        DB::statement('CALL update_comment_statistics(?, ?, ?);', [$type, $id, $comment->user_id]);
        return redirect(str_ireplace('{id}', $id, $config['redirect']));
	}

    public function getEdit($id) {
        $comment = Comment::with(['comment_metas'])->findOrFail($id);
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

        if (!array_key_exists($type, $this->comment_config)) abort(404);
        $config = $this->comment_config[$type];

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
                $val = strval(Request::input($meta['key']));
                if ($val && preg_match('/^(1|2|3|4|5)$/i', $val)) {
                    $metas[] = new CommentMeta([ 'key' => $key, 'value' => $val]);
                    if (isset($meta['one_per_user']) && $meta['one_per_user']) {
                        DB::statement('DELETE m FROM comment_metas AS m LEFT JOIN comments AS c ON c.id = m.comment_id WHERE article_type = ? AND article_id = ? AND user_id = ?', [$type, $id, $comment->user_id]);
                    }
                }
            }
            $comment->comment_metas()->saveMany($metas);
        }
        DB::statement('CALL update_comment_statistics(?, ?, ?);', [$type, $id, $comment->user_id]);
        return redirect(str_ireplace('{id}', $id, $config['redirect']));
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

        if (!array_key_exists($type, $this->comment_config)) abort(404);
        $config = $this->comment_config[$type];

        if (!permission($config['auth_moderate'])) abort(404);

        $comment->delete();

        return redirect(str_ireplace('{id}', $id, $config['redirect']));
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

        if (!array_key_exists($type, $this->comment_config)) abort(404);
        $config = $this->comment_config[$type];

        if (!permission($config['auth_moderate'])) abort(404);

        $comment->restore();

        return redirect(str_ireplace('{id}', $id, $config['redirect']));
    }
}

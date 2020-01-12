<?php namespace App\Models\Comments;

use App\Models\Accounts\UserNotification;
use App\Models\Accounts\UserSubscription;
use App\Models\Journal;
use App\Models\News;
use App\Models\Polls\Poll;
use App\Models\Vault\VaultItem;
use App\Models\Vault\VaultItemReview;
use App\Models\Wiki\WikiObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;
use DB;

class Comment extends Model {

    const NEWS = 'n';
    const JOURNAL = 'j';
    const VAULT = 'v';
    const REVIEW = 'r';
    const POLL = 'p';
    const WIKI = 'w';

    public static function getSubscription($user, $article_type, $article_id, $clear = false)
    {
        if (!$user || !$user->id) return null;

        $ty = UserNotification::GetTypeFromCommentType($article_type);
        $sub = UserSubscription::whereUserId($user->id)
                ->whereArticleType($ty)
                ->whereArticleId($article_id)
                ->first();
        if ($sub && $clear) {
            DB::statement('CALL clear_user_notifications(?, ?, ?);', [$user->id, $ty, $article_id]);
        }
        return $sub;
    }

    use SoftDeletes;

	protected $table = 'comments';
    protected $fillable = ['article_type', 'article_id', 'user_id', 'content_text', 'content_html'];
    public $visible = ['id', 'user_id', 'article_id', 'article_type', 'content_text', 'content_html', 'created_at', 'updated_at', 'comment_metas', 'user'];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User', 'user_id', 'id');
    }

    public function comment_metas()
    {
        return $this->hasMany('App\Models\Comments\CommentMeta', 'comment_id', 'id');
    }

    public function hasRating() {
        return $this->getMeta(CommentMeta::RATING) != null;
    }

    public function getRating() {
        return intval($this->getMeta(CommentMeta::RATING));
    }

    public function hasTemplate() {
        return $this->getMeta(CommentMeta::TEMPLATE) != null;
    }

    public function getTemplate() {
        return $this->getMeta(CommentMeta::TEMPLATE);
    }

    public function getTemplateArticleObject() {
        $d = $this->getMeta(CommentMeta::TEMPLATE_ARTICLE_ID);
        $t = $this->getMeta(CommentMeta::TEMPLATE_ARTICLE_TYPE);
        if ($d == null || $t == null) return null;

        $d = intval($d);
        switch ($t) {
            case 'VaultItemReview':
                return VaultItemReview::with(['user'])->find($d);
        }
        return null;
    }

    public function getRatingStars()
    {
        $score = $this->getRating();
        $rounded = ceil($score * 2) / 2; // Round up to closest 0.5

        $full = floor($rounded);
        $half = $rounded - $full > 0;
        $empty = 5 - ceil($rounded);

        $stars = [];

        for ($i = 0; $i < $full; $i++) $stars[] = 'full';
        if ($half) $stars[] = 'half';
        for ($i = 0; $i < $empty; $i++) $stars[] = 'empty';

        return $stars;
    }

    private function getMeta($type) {
        foreach ($this->comment_metas as $meta) {
            if ($meta->key == $type) return $meta->value;
        }
        return null;
    }

    public function getArticle() {
        switch ($this->article_type) {
            case Comment::NEWS;
                return News::findOrFail($this->article_id);
            case Comment::JOURNAL;
                return Journal::findOrFail($this->article_id);
            case Comment::VAULT;
                return VaultItem::findOrFail($this->article_id);
            case Comment::REVIEW;
                return VaultItemReview::findOrFail($this->article_id);
            case Comment::POLL:
                return Poll::findOrFail($this->article_id);
            case Comment::WIKI:
                return WikiObject::findOrFail($this->article_id);
            default:
                throw new \Exception('Undefined comment type in getArticle: ' . $this->article_type);
        }
    }

    public function getArticleTypeDescription() {
        switch ($this->article_type) {
            case Comment::NEWS;
                return 'news';
            case Comment::JOURNAL;
                return 'journal';
            case Comment::VAULT;
                return 'vault item';
            case Comment::REVIEW;
                return 'review';
            case Comment::POLL:
                return 'poll';
            case Comment::WIKI:
                return 'wiki page';
            default:
                throw new \Exception('Undefined comment type in getArticle: ' . $this->article_type);
        }
    }

    public function getArticleUrl() {
        switch ($this->article_type) {
            case Comment::NEWS;
                return act('news', 'view', $this->article_id);
            case Comment::JOURNAL;
                return act('journal', 'view', $this->article_id);
            case Comment::VAULT;
                return act('vault', 'view', $this->article_id);
            case Comment::REVIEW;
                return act('review', 'view', $this->article_id);
            case Comment::POLL:
                return act('poll', 'view', $this->article_id);
            case Comment::WIKI:
                return act('wiki', 'view', $this->article_id);
            default:
                throw new \Exception('Undefined comment type in getArticle: ' . $this->article_type);
        }
    }

    public function getArticleTitle($article) {
        $title = '';
        switch ($this->article_type) {
            case Comment::NEWS;
            case Comment::JOURNAL;
            case Comment::POLL:
                $title = $article->title;
            break;
            case Comment::VAULT;
                $title = $article->name;
                break;
            case Comment::REVIEW;
                $title = 'Review #' . $article->id;
                break;
            case Comment::WIKI:
                $title = $article->current_revision->title;
                break;
            default:
                throw new \Exception('Undefined comment type in getArticleTitle: ' . $this->article_type);
        }
        if (strlen($title) == 0) $title = '#'.$this->article_id;
        return $title;
    }

    public static function canCreate($type)
    {
        // User must be logged in
        $user = Auth::user();
        if (!$user) return false;

        $permission = null;
        switch ($type) {
            case Comment::NEWS;
                $permission = 'News';
                break;
            case Comment::JOURNAL;
                $permission = 'Journal';
                break;
            case Comment::VAULT;
            case Comment::REVIEW;
                $permission = 'Vault';
                break;
            case Comment::POLL:
                $permission = 'Poll';
                break;
            case Comment::WIKI:
                $permission = 'Wiki';
                break;
            default:
                throw new \Exception('Undefined comment type in isEditable: ' . $permission);
        }

        // Admins of the section and global admins can do anything
        return permission($permission . 'Comment') || permission('Admin') || permission($permission . 'Admin');
    }

    /**
     * @param array $comments All the comments in the article. If null, the list will be fetched from the database.
     * @throws \Exception
     * @return bool
     */
    public function isEditable($comments = null) {

        // Templated comments can't be changed by anybody else
        if ($this->hasTemplate()) return false;

        // User must be logged in
        $user = Auth::user();
        if (!$user) return false;

        $permission = null;
        switch ($this->article_type) {
            case Comment::NEWS;
                $permission = 'News';
                break;
            case Comment::JOURNAL;
                $permission = 'Journal';
                break;
            case Comment::VAULT;
            case Comment::REVIEW;
                $permission = 'Vault';
                break;
            case Comment::POLL:
                $permission = 'Poll';
                break;
            case Comment::WIKI:
                $permission = 'Wiki';
                break;
            default:
                throw new \Exception('Undefined comment type in isEditable: ' . $permission);
        }

        // Admins of the section and global admins can do anything
        if (permission('Admin') || permission($permission . 'Admin')) return true;

        // User must own the post and have the comment permission on the section
        if ($user->id != $this->user_id || !permission($permission . 'Comment')) return false;

        // Comments less than an hour old are always editable
        if ($this->created_at->diffInMinutes() <= 60) return true;

        if ($comments == null) {
            $comments = Comment::whereArticleType($this->article_type)->whereArticleId($this->article_id)->get();
        }
        $last = $comments->last();

        // The last comment in a thread is always editable
        if ($this->id == $last->id) return true;

        return false;
    }

    public function isDeletable() {

        // Templated comments can't be changed by anybody else
        if ($this->hasTemplate()) return false;

        // User must be logged in
        $user = Auth::user();
        if (!$user) return false;

        $permission = null;
        switch ($this->article_type) {
            case Comment::NEWS;
                $permission = 'News';
                break;
            case Comment::JOURNAL;
                $permission = 'Journal';
                break;
            case Comment::VAULT;
            case Comment::REVIEW;
                $permission = 'Vault';
                break;
            case Comment::POLL:
                $permission = 'Poll';
                break;
            case Comment::WIKI:
                $permission = 'Wiki';
                break;
            default:
                throw new \Exception('Undefined comment type in isDeletable: ' . $permission);
        }

        return permission('Admin') || permission($permission . 'Admin');
    }
}

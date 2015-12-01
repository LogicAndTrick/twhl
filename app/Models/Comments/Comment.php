<?php namespace App\Models\Comments;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class Comment extends Model {

    const NEWS = 'n';
    const JOURNAL = 'j';
    const VAULT = 'v';
    const MOTM = 'm';
    const POLL = 'p';
    const WIKI = 'w';

    use SoftDeletes;

	protected $table = 'comments';
    protected $fillable = ['article_type', 'article_id', 'user_id', 'content_text', 'content_html'];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function comment_metas()
    {
        return $this->hasMany('App\Models\Comments\CommentMeta');
    }

    public function hasRating() {
        return $this->getMeta(CommentMeta::RATING) != null;
    }

    public function getRating() {
        return intval($this->getMeta(CommentMeta::RATING));
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

    /**
     * @param array $comments All the comments in the article. If null, the list will be fetched from the database.
     * @throws \Exception
     * @return bool
     */
    public function isEditable($comments = null) {

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
                $permission = 'Vault';
                break;
            case Comment::MOTM;
                $permission = 'Motm';
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
        if ($this->id == $last->od) return true;

        return false;
    }

    public function isDeletable() {

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
                $permission = 'Vault';
                break;
            case Comment::MOTM;
                $permission = 'Motm';
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

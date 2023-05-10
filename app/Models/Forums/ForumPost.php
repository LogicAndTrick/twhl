<?php namespace App\Models\Forums;

use App\Helpers\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class ForumPost extends Model {

    use SoftDeletes;
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    protected $fillable = ['forum_id', 'thread_id', 'user_id', 'content_text', 'content_html'];
    public $visible = ['id', 'forum_id', 'thread_id', 'user_id', 'content_text', 'content_html', 'created_at', 'updated_at', 'forum', 'thread', 'user'];

    protected $table = 'forum_posts';

    public function forum()
    {
        return $this->belongsTo('App\Models\Forums\Forum');
    }

    public function thread()
    {
        return $this->belongsTo('App\Models\Forums\ForumThread', 'thread_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    /**
     * Returns true if this post is editable by the current user.
     * @param $thread ForumThread The thread the post belongs to. If null, the thread will be fetched.
     * @return bool
     */
    public function isEditable($thread = null)
    {
        // To edit a post:

        // 1. User must be logged in
        $user = Auth::user();
        if (!$user) return false;

        // 2. User must have ForumCreate permission
        if (!permission('ForumCreate')) return false;

        // 2a. ForumAdmin users can proceed from here
        if (permission('ForumAdmin')) return true;

        // 3. The user must own the post
        if ($user->id != $this->user_id) return false;

        if (!$thread) $thread = $this->thread;

        // 4. The post's thread must be postable
        if (!$thread->isPostable()) return false;

        // 5a. Posts less than an hour old are always editable
        if (Date::DiffMinutes(Date::Now(), $this->created_at) <= 60) return true;

        // 5b. The last post in a thread is always editable
        if ($this->id == $thread->last_post_id) return true;

        // 5c. The first post in a thread is always editable
        $fp = $thread->getFirstPost();
        if ($fp && $fp->id == $this->id) return true;

        // Otherwise, the post isn't editable.
        return false;
    }
}

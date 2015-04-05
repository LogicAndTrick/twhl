<?php namespace App\Models\Forums;

use App\Helpers\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class ForumThread extends Model {

    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = ['forum_id','user_id','title','is_open','is_sticky'];

    protected $table = 'forum_threads';

    public function forum()
    {
        return $this->belongsTo('App\Models\Forums\Forum');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Forums\ForumPost', 'thread_id');
    }

    public function last_post()
    {
        return $this->hasOne('App\Models\Forums\ForumPost', 'id', 'last_post_id');
    }

    private $firstPost = false;

    public function getFirstPost() {
        if ($this->firstPost === false) {
            $this->firstPost = $this->posts()->orderBy('created_at', 'asc')->first();
        }
        return $this->firstPost;
    }

    /**
     * Returns true if the thread can be posted in
     * @param $check_forum bool Set to true to also check the forum permissions, false by default
     * @return bool
     */
    public function isPostable($check_forum = false)
    {
        if ($check_forum) {
            if (!Forum::find($this->forum_id)) return false;
        }

        // To post in a thread:

        // 1. User must be logged in
        $user = Auth::user();
        if (!$user) return false;

        // 2. User must have ForumCreate permission
        if (!permission('ForumCreate')) return false;

        // 2a. ForumAdmin users can proceed from here
        if (permission('ForumAdmin')) return true;

        // 3. The thread must be open
        if (!$this->is_open) return false;

        // 4a. If the thread is sticky, it can always be posted in
        if ($this->is_sticky) return true;

        // 4b. Normal threads are closed if they are over 90 days old
        if (Date::DiffMinutes(Date::Now(), $this->last_post->updated_at) > 90) return false;

        return true;
    }

    /**
     * Returns the reason why the thread cannot be posted in. Doesn't check for forum access.
     * @return null|string
     */
    public function getUnpostableReason()
    {
        if (!Auth::user()) return 'You must be logged in to post a response.';
        if (!permission('ForumCreate')) return 'You do not have permission to post a response.';
        if (!$this->is_open) return 'This thread has been closed, responses cannot be posted.';
        if (Date::DiffMinutes(Date::Now(), $this->last_post->updated_at) > 90) return 'This thread has automatically been locked because it has been idle for over 90 days.';
        return null;
    }
}

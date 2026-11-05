<?php namespace App\Models\Forums;

use App\Models\ModelWithExtraTypeInfo;
use App\Models\Scopes\ForumPermissionScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy(ForumPermissionScope::class)]
class Forum extends ModelWithExtraTypeInfo {

    use SoftDeletes;

    protected $casts = [
        'order_index' => 'integer',
        'stat_posts' => 'integer',
        'stat_threads' => 'integer',
    ];
    protected $nullable = [
        'permission_id',
    ];
    protected $fillable = ['name', 'description', 'slug', 'permission_id'];
    public $visible = [ 'id', 'slug', 'name', 'description', 'stat_threads', 'stat_posts', 'last_post_id', 'order_index', 'last_post' ];

    protected $table = 'forums';

    public function last_post()
    {
        return $this->hasOne('App\Models\Forums\ForumPost', 'id', 'last_post_id');
    }

    public function threads()
    {
        return $this->hasMany('App\Models\Forums\ForumThread');
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Forums\ForumPost');
    }

    /**
     * Checks if a forum has new posts or not. If the user is logged in, the last access time of the previous
     * session is used. Otherwise, anything less than a day old is new.
     * @return bool
     */
    public function hasNewPosts()
    {
        if ($this->last_post == null) return false;

        $last_access = session('last_access_time');
        if (!$last_access || !($last_access instanceof Carbon)) $last_access = Carbon::now()->addDays(1);

        return $this->last_post->updated_at > $last_access;
    }

    public function getIconClasses()
    {
        $str = [$this->slug, 'forum'];

        if ($this->hasNewPosts()) $str[] = 'thread_active';
        else $str[] = 'thread_inactive';

        return implode(' ', $str);
    }
}

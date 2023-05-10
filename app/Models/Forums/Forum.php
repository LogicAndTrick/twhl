<?php namespace App\Models\Forums;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class Forum extends Model {

    use SoftDeletes;
    use ForumPermission;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
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

/**
 * The forum permission trait ensures that the logged in user can access each forum object.
 * If the permission is null, anybody can view the forum.
 * Otherwise, the user must be logged in and have the specified permission to view the forum.
 *
 * @package App\Models\Forums
 */
trait ForumPermission
{
    public static function bootForumPermission()
    {
        static::addGlobalScope(new ForumPermissionScope);
    }
}

class ForumPermissionScope implements Scope
{
    private $permission_sql = '(
    permission_id is null
    or permission_id in (
        select up.permission_id from user_permissions up
        left join users u on up.user_id = u.id
        where u.id = ?
    ))';

    public function apply(Builder $builder, Model $model)
    {
        $user = Auth::user();
        $id = $user ? $user->id : 0;
        $builder->whereRaw($this->permission_sql, [$id]);
    }
}

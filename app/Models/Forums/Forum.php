<?php namespace App\Models\Forums;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class Forum extends Model {

    use SoftDeletes;
    use ForumPermission;

    protected $dates = ['deleted_at'];
    protected $fillable = ['name', 'description', 'slug', 'permission_id'];

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

class ForumPermissionScope implements ScopeInterface
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

    public function remove(Builder $builder, Model $model)
    {
        $query = $builder->getQuery();

        foreach ((array) $query->wheres as $key => $where)
        {
            if ($where['type'] == 'raw' && $where['sql'] == $this->permission_sql)
            {
                unset($query->wheres[$key]);
                $query->wheres = array_values($query->wheres);
            }
        }
    }
}

<?php

namespace App\Models\Scopes;

use App\Models\Forums\Forum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ForumPermissionScope implements Scope
{
    /**
     * The forum permission scope ensures that the logged in user can access each forum object.
     * If the permission is null, anybody can view the forum.
     * Otherwise, the user must be logged in and have the specified permission to view the forum.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $column = $model->qualifyColumn('permission_id');
        $user = Auth::user();
        if (!$user)
        {
            $builder->whereNull($column);
        }
        else
        {
            $permission_ids = $user->permissions->pluck('id');
            $builder->where(function(Builder $b) use ($column, $permission_ids) {
                return $b->whereNull($column)->orWhereIn($column, $permission_ids);
            });
        }
    }

    public static function CachedForumIdsForUser()
    {
        return Cache::remember('user_forum_ids:' . (Auth::id() ?? 0), now()->addHour(), function() {
            // this will use the scope above, so its correct for the current user.
            return Forum::withTrashed()->pluck('id');
        });
    }
}

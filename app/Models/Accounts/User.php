<?php namespace App\Models\Accounts;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword, SoftDeletes;

	protected $table = 'users';
	protected $fillable = ['name', 'email', 'password'];
	protected $hidden = ['password', 'remember_token'];
    protected $dates = ['deleted_at'];

    public function posts()
    {
        return $this->hasMany('App\Models\Forums\ForumPost');
    }

    public function permissions()
    {
        return $this->belongsToMany('App\Models\Accounts\Permission', 'user_permissions');
    }

    public function hasPermission($name)
    {
        return count($this->permissions->filter(function($p) use ($name) {
            return $p->name == $name;
        })) > 0;
    }
}

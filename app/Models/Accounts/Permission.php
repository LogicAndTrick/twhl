<?php namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model {

    protected $table = 'permissions';
    protected $visible = [ 'id', 'name', 'description', 'is_default' ];

    public function users()
    {
        return $this->belongsToMany('App\Models\Accounts\User', 'user_permissions');
    }
}

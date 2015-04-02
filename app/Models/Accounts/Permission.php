<?php namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model {

    protected $table = 'permissions';

    public function users()
    {
        return $this->belongsToMany('App\Models\Accounts\User', 'user_permissions');
    }
}

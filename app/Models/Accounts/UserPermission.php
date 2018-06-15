<?php namespace App\Models\Accounts;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model {

    protected $table = 'user_permissions';
    protected $fillable = [ 'user_id', 'permission_id' ];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function permission()
    {
        return $this->belongsTo('App\Models\Accounts\Permission');
    }
}

<?php namespace App\Models\Accounts;

use App\Models\ModelWithExtraTypeInfo;

class Permission extends ModelWithExtraTypeInfo {

    protected $table = 'permissions';
    protected $fillable = [ 'name', 'description', 'is_default' ];
    public $visible = [ 'id', 'name', 'description', 'is_default' ];
    public $timestamps = false;
    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany('App\Models\Accounts\User', 'user_permissions');
    }
}

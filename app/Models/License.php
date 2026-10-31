<?php namespace App\Models;

use App\Models\ModelWithExtraTypeInfo;

class License extends ModelWithExtraTypeInfo {

    public $table = 'licenses';
    public $timestamps = false;
    public $fillable = ['name', 'description', 'orderindex'];
    public $visible = ['id', 'name', 'description', 'orderindex'];
    protected $casts = [
        'orderindex' => 'integer',
    ];

}

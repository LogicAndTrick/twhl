<?php namespace App\Models;

use App\Models\ModelWithExtraTypeInfo;

class Engine extends ModelWithExtraTypeInfo {

	public $table = 'engines';
    public $timestamps = false;
    public $fillable = ['name', 'orderindex'];
    public $visible = ['id', 'name', 'orderindex'];
    protected $casts = [
        'orderindex' => 'integer',
    ];

    public function games()
    {
        return $this->hasMany('App\Models\Game');
    }

}

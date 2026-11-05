<?php namespace App\Models;

use App\Models\ModelWithExtraTypeInfo;

class Game extends ModelWithExtraTypeInfo {

    public $table = 'games';
    public $timestamps = false;
    public $fillable = ['engine_id', 'name', 'name_variants', 'abbreviation', 'orderindex'];
    public $visible = ['id', 'engine_id', 'name', 'name_variants', 'abbreviation', 'orderindex', 'engine'];
    protected $casts = [
        'orderindex' => 'integer',
    ];

    public function getIconUrl()
    {
        return asset('images/games/' . $this->abbreviation . '_32.svg');
    }

    public function engine()
    {
        return $this->belongsTo('App\Models\Engine');
    }
}

<?php namespace App\Models\Wiki;

use App\Models\ModelWithExtraTypeInfo;

class WikiType extends ModelWithExtraTypeInfo {

    const PAGE = 1;
    const UPLOAD = 2;
    const CATEGORY = 3;

	//
    protected $table = 'wiki_types';
    protected $fillable = ['name', 'description'];
    protected $casts = [
        'orderindex' => 'integer',
    ];
    public $timestamps = false;

}

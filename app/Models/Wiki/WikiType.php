<?php namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

class WikiType extends Model {

    const PAGE = 1;
    const IMAGE = 2;
    const CATEGORY = 3;

	//
    protected $table = 'wiki_types';
    protected $fillable = ['name', 'description'];
    public $timestamps = false;

}

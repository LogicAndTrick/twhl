<?php namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

class WikiObject extends Model {

	//
    protected $table = 'wiki_objects';
    protected $fillable = ['type_id', 'permission_id'];

}

<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Engine extends Model {

	public $table = 'engines';
    public $timestamps = false;
    public $fillable = ['name', 'orderindex'];
    public $visible = ['id', 'name', 'orderindex'];

}

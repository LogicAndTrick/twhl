<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model {

    public $table = 'games';
    public $timestamps = false;
    public $fillable = ['engine_id', 'name', 'abbreviation', 'orderindex'];
    public $visible = ['id', 'engine_id', 'name', 'abbreviation', 'orderindex'];

}

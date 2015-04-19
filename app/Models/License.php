<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model {

    public $table = 'licenses';
    public $timestamps = false;
    public $fillable = ['name', 'description', 'orderindex'];
    public $visible = ['id', 'name', 'description', 'orderindex'];

}

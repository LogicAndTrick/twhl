<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shout extends Model {

    protected $table = 'shouts';
    protected $fillable = ['user_id', 'content'];
    protected $visible = ['id', 'user_id', 'content', 'created_at', 'user'];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

}

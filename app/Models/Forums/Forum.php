<?php namespace App\Models\Forums;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Forum extends Model {

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $table = 'forums';

    public function last_post()
    {
        return $this->hasOne('App\Models\Forums\ForumPost', 'id', 'last_post_id');
    }

    public function threads()
    {
        return $this->hasMany('App\Models\Forums\ForumThread');
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Forums\ForumPost');
    }

}

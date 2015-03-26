<?php namespace App\Models\Forums;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumThread extends Model {

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $table = 'forum_threads';

    public function forum()
    {
        return $this->belongsTo('App\Models\Forums\Forum');
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Forums\ForumPost');
    }

}

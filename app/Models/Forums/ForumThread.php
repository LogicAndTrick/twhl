<?php namespace App\Models\Forums;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumThread extends Model {

    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = ['forum_id','user_id','title','is_open','is_sticky'];

    protected $table = 'forum_threads';

    public function forum()
    {
        return $this->belongsTo('App\Models\Forums\Forum');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function posts()
    {
        return $this->hasMany('App\Models\Forums\ForumPost', 'thread_id');
    }

    public function last_post()
    {
        return $this->hasOne('App\Models\Forums\ForumPost', 'id', 'last_post_id');
    }

}

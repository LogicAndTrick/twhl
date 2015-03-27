<?php namespace App\Models\Forums;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumPost extends Model {

    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $table = 'forum_posts';

    public function forum()
    {
        return $this->belongsTo('App\Models\Forums\Forum');
    }

    public function thread()
    {
        return $this->belongsTo('App\Models\Forums\ForumThread', 'thread_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

}

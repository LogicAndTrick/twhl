<?php namespace App\Models\Forums;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumPost extends Model {

    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = ['forum_id', 'thread_id', 'user_id', 'content_text', 'content_html'];

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

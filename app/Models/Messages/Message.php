<?php namespace App\Models\Messages;

use Illuminate\Database\Eloquent\Model;

class Message extends Model {

    protected $table = 'messages';
    protected $fillable = ['user_id', 'thread_id', 'content_text', 'content_html'];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function thread()
    {
        return $this->belongsTo('App\Models\Messages\MessageThread');
    }

    public function recipients()
    {
        return $this->belongsToMany('App\Models\Accounts\User', 'message_users', 'message_id', 'user_id');
    }
}

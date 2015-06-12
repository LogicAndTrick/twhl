<?php namespace App\Models\Messages;

use Illuminate\Database\Eloquent\Model;

class MessageUser extends Model {

    protected $table = 'message_users';
    protected $fillable = ['user_id', 'thread_id', 'message_id', 'is_unread'];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function message()
    {
        return $this->belongsTo('App\Models\Messages\Message');
    }

    public function thread()
    {
        return $this->belongsTo('App\Models\Messages\MessageThread');
    }

}

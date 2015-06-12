<?php namespace App\Models\Messages;

use Illuminate\Database\Eloquent\Model;

class MessageThreadUser extends Model {

    protected $table = 'message_thread_users';
    protected $fillable = ['user_id', 'thread_id'];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function thread()
    {
        return $this->belongsTo('App\Models\Messages\MessageThread');
    }
}

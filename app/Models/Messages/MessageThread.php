<?php namespace App\Models\Messages;

use Illuminate\Database\Eloquent\Model;
use Auth;

class MessageThread extends Model {

    protected $table = 'message_threads';
    protected $fillable = ['user_id', 'subject', 'last_message_id'];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function participants()
    {
        return $this->belongsToMany('App\Models\Accounts\User', 'message_thread_users', 'thread_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany('App\Models\Messages\Message', 'thread_id');
    }

    public function last_message()
    {
        return $this->belongsTo('App\Models\Messages\Message', 'last_message_id');
    }

    public function canView()
    {
        return Auth::user() && (permission('Admin') || $this->participants->contains(Auth::user()->id));
    }
}

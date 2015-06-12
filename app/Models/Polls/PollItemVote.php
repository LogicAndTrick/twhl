<?php namespace App\Models\Polls;

use Illuminate\Database\Eloquent\Model;

class PollItemVote extends Model {

    protected $table = 'poll_item_votes';
    protected $fillable = ['poll_id', 'item_id', 'user_id'];
    public $timestamps = false;

    public function poll()
    {
        return $this->belongsTo('App\Models\Polls\Poll');
    }

    public function poll_item()
    {
        return $this->belongsTo('App\Models\Polls\PollItem', 'item_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

}

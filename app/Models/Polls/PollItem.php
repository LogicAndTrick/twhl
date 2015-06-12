<?php namespace App\Models\Polls;

use Illuminate\Database\Eloquent\Model;

class PollItem extends Model {

    protected $table = 'poll_items';
    protected $fillable = ['poll_id', 'text', 'stat_votes'];
    public $timestamps = false;

    public function poll()
    {
        return $this->belongsTo('App\Models\Polls\Poll');
    }

    public function votes()
    {
        return $this->hasMany('App\Models\Polls\PollItemVote', 'item_id');
    }

}

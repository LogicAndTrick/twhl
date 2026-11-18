<?php namespace App\Models\Polls;

use App\Models\ModelWithExtraTypeInfo;

class PollItem extends ModelWithExtraTypeInfo {

    protected $table = 'poll_items';
    protected $fillable = ['poll_id', 'text', 'stat_votes'];
    protected $casts = [
        'stat_votes' => 'integer',
    ];
    protected $formats = [
        'stat_votes' => 'uint32',
    ];
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

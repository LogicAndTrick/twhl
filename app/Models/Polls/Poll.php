<?php namespace App\Models\Polls;

use App\Models\ModelWithExtraTypeInfo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poll extends ModelWithExtraTypeInfo {

    use SoftDeletes;

    protected $table = 'polls';
    protected $fillable = ['title', 'content_text', 'content_html', 'close_date'];
    protected $casts = [
        'close_date' => 'datetime',
        'flag_locked' => 'boolean',
        'stat_comments' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany('App\Models\Polls\PollItem', 'poll_id');
    }

    public function votes()
    {
        return $this->hasMany('App\Models\Polls\PollItemVote', 'poll_id');
    }

    public function isOpen() {
        return !$this->isClosed();
    }

    public function isClosed() {
        return $this->close_date->diffInSeconds(null, false) > 0;
    }

    public function commentsIsLocked() {
        return $this->flag_locked;
    }

    public function commentsCanAddMeta($meta) {
        return true;
    }
}

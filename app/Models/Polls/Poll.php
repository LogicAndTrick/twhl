<?php namespace App\Models\Polls;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poll extends Model {

    use SoftDeletes;

    protected $table = 'polls';
    protected $fillable = ['title', 'content_text', 'content_html', 'close_date'];

    public function getDates()
    {
        return ['close_date', 'created_at', 'updated_at'];
    }

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

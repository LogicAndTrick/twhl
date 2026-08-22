<?php namespace App\Models;

use App\Models\Reactions\Reaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Journal extends Model {

    use SoftDeletes;

	protected $table = 'journals';
    protected $fillable = ['user_id', 'title', 'content_text', 'content_html', 'stat_comments', 'flag_locked'];
    protected $with = ['reactions'];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class, 'entity_id')->withAttributes([ 'entity_type' => Reaction::JOURNAL ]);
    }

    public function getTitle() {
        return $this->title ? $this->title : 'Journal #'.$this->id;
    }

    public function isEditable() {
        return Auth::user() != null && (Auth::user()->id == $this->user_id || permission('JournalAdmin'));
    }

    public function commentsIsLocked() {
        return $this->flag_locked;
    }

    public function commentsCanAddMeta($meta) {
        return true;
    }
}

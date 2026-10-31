<?php namespace App\Models;

use App\Models\ModelWithExtraTypeInfo;
use App\Models\Reactions\Reaction;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Journal extends ModelWithExtraTypeInfo {

    use SoftDeletes;

	protected $table = 'journals';
    protected $fillable = ['user_id', 'title', 'content_text', 'content_html', 'stat_comments', 'flag_locked'];
    protected $casts = [
        'flag_locked' => 'boolean',
        'stat_comments' => 'integer',
    ];
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

<?php namespace App\Models;

use App\Models\ModelWithExtraTypeInfo;
use App\Models\Reactions\Reaction;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends ModelWithExtraTypeInfo {

    use SoftDeletes;

	protected $table = 'news';
    protected $fillable = ['user_id', 'title', 'content_text', 'content_html', 'stat_comments', 'flag_locked'];
    protected $casts = [
        'flag_locked' => 'boolean',
    ];
    protected $with = ['reactions'];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class, 'entity_id')->withAttributes([ 'entity_type' => Reaction::NEWS ]);
    }

    public function commentsIsLocked() {
        return $this->flag_locked;
    }

    public function commentsCanAddMeta($meta) {
        return true;
    }

}

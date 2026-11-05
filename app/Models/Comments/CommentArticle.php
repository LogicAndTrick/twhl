<?php namespace App\Models\Comments;

use App\Models\ModelWithExtraTypeInfo;

class CommentArticle extends ModelWithExtraTypeInfo {

	protected $table = 'comment_articles';
    protected $casts = [
        'stat_comments' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }
}

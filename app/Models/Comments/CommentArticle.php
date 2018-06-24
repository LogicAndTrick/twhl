<?php namespace App\Models\Comments;

use Illuminate\Database\Eloquent\Model;

class CommentArticle extends Model {

	protected $table = 'comment_articles';

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }
}

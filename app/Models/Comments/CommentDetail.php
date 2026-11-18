<?php namespace App\Models\Comments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CommentDetail extends Comment {
    protected $table = 'comment_details';
    protected $casts = [
        'article_created_at' => 'datetime',
        'article_flag_locked' => 'boolean',
        'article_stat_comments' => 'integer',
        'article_updated_at' => 'datetime',
    ];
    protected $nullable = [
        'article_created_at',
        'article_updated_at',
        'article_user_id',
    ];
}

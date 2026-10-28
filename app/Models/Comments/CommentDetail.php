<?php namespace App\Models\Comments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CommentDetail extends Comment {
    protected $table = 'comment_details';
    protected $casts = [
        'article_created_at' => 'datetime',
        'article_flag_locked' => 'boolean',
        'article_updated_at' => 'datetime',
        'stat_comments' => 'integer',
    ];
    protected $nullable = [
        'article_created_at',
        'article_updated_at',
    ];
}

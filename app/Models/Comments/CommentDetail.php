<?php namespace App\Models\Comments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CommentDetail extends Comment {
    protected $table = 'comment_details';
}
<?php namespace App\Models\Comments;

use Illuminate\Database\Eloquent\Model;

class CommentMeta extends Model {

    const RATING = 'r';
    const TEMPLATE = 't';
    const TEMPLATE_ARTICLE_TYPE = 'a';
    const TEMPLATE_ARTICLE_ID = 'i';

	protected $table = 'comment_metas';
    protected $fillable = ['comment_id', 'key', 'value'];
    public $timestamps = false;

    public static function GetMetaFor($article_type) {
        switch ($article_type) {
            case Comment::VAULT:
                return [ CommentMeta::RATING ];
            default:
                return [];
        }
    }

}

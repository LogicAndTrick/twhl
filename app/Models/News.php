<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model {

    use SoftDeletes;

	protected $table = 'news';
    protected $fillable = ['user_id', 'title', 'content_text', 'content_html', 'stat_comments', 'flag_locked'];

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

}

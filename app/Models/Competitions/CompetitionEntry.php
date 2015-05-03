<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;

class CompetitionEntry extends Model {

    public $table = 'competition_entries';
    public $fillable = ['competition_id', 'user_id', 'title', 'content_html', 'content_text', 'is_hosted_externally', 'file_location'];
    public $visible = ['id', 'competition_id', 'user_id', 'title'];

    public function competition()
    {
        return $this->belongsTo('App\Models\Competitions\Competition');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

}

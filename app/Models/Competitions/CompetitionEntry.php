<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitionEntry extends Model {

    use SoftDeletes;

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

    public function result()
    {
        return $this->hasOne('App\Models\Competitions\CompetitionResult', 'entry_id');
    }

    public function screenshots()
    {
        return $this->hasMany('App\Models\Competitions\CompetitionEntryScreenshot', 'entry_id');
    }

    public function getLinkUrl()
    {
        return $this->is_hosted_externally ? $this->file_location : asset('uploads/competition/entries/'.$this->file_location);
    }

}

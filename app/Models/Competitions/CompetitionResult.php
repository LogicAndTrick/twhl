<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;

class CompetitionResult extends Model {

    public $table = 'competition_results';
    public $fillable = ['competition_id', 'entry_id', 'rank', 'content_text', 'content_html'];
    public $visible = ['id', 'competition_id', 'user_id', 'entry_id', 'rank', 'content_text', 'content_html'];

}

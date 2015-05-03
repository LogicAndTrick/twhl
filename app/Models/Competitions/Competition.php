<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;

class Competition extends Model {

    public $table = 'competitions';
    public $fillable = ['status_id', 'type_id', 'judge_type_id', 'name', 'brief_text', 'brief_html', 'brief_attachment', 'open_date', 'close_date', 'voting_close_date', 'outro_text', 'outro_html'];

}

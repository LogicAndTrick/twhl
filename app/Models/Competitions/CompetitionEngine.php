<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;

class CompetitionEngine extends Model {

	public $table = 'competition_engines';
    public $timestamps = false;
    public $fillable = ['engine_id', 'competition_id'];

    public function competition()
    {
        return $this->belongsTo('App\Models\Competitions\Competition');
    }

    public function engine()
    {
        return $this->belongsTo('App\Models\Engine');
    }

}

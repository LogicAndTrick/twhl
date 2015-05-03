<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;

class CompetitionRule extends Model {

    public $table = 'competition_rules';
    public $timestamps = false;
    public $fillable = ['competition_id', 'restriction_id'];

    public function competition()
    {
        return $this->belongsTo('App\Models\Competitions\Competition');
    }

    public function restriction()
    {
        return $this->belongsTo('App\Models\Competitions\CompetitionRestriction', 'restriction_id');
    }

}

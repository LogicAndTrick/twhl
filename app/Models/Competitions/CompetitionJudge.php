<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;

class CompetitionJudge extends Model {

    public $table = 'competition_judges';
    public $timestamps = false;
    public $fillable = ['competition_id', 'user_id'];

    public function competition()
    {
        return $this->belongsTo('App\Models\Competitions\Competition');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }

}

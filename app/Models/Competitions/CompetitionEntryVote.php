<?php namespace App\Models\Competitions;

use App\Models\ModelWithExtraTypeInfo;

class CompetitionEntryVote extends ModelWithExtraTypeInfo {

    public $table = 'competition_entry_votes';
    public $timestamps = false;
    public $fillable = ['entry_id', 'competition_id', 'user_id'];

    public function entry()
    {
        return $this->belongsTo('App\Models\Competitions\CompetitionEntry', 'entry_id');
    }

    public function competition()
    {
        return $this->belongsTo('App\Models\Competitions\Competition');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Accounts\User');
    }
}

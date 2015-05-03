<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;

class CompetitionEntryScreenshot extends Model {

	public $table = 'competition_entry_screenshots';
    public $timestamps = false;
    public $fillable = ['entry_id', 'image_thumb', 'image_full'];

    public function entry()
    {
        return $this->belongsTo('App\Models\Competitions\CompetitionEntry', 'entry_id');
    }

}

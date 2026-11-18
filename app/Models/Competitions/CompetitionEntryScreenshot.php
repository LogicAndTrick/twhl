<?php namespace App\Models\Competitions;

use App\Models\ModelWithExtraTypeInfo;

class CompetitionEntryScreenshot extends ModelWithExtraTypeInfo {

	public $table = 'competition_entry_screenshots';
    public $timestamps = false;
    public $fillable = ['entry_id', 'image_thumb', 'image_full'];

    public function entry()
    {
        return $this->belongsTo('App\Models\Competitions\CompetitionEntry', 'entry_id');
    }

}

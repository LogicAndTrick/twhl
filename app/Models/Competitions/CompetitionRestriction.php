<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;

class CompetitionRestriction extends Model {

    public $table = 'competition_restrictions';
    public $timestamps = false;
    public $fillable = ['group_id', 'content_text', 'content_html'];
    public $visible = ['id', 'group_id', 'content_text', 'content_html', 'group'];

    public function group()
    {
        return $this->belongsTo('App\Models\Competitions\CompetitionRestrictionGroup', 'group_id');
    }

}

<?php namespace App\Models\Competitions;

use App\Models\ModelWithExtraTypeInfo;

class CompetitionRestrictionGroup extends ModelWithExtraTypeInfo {

    public $table = 'competition_restriction_groups';
    public $timestamps = false;
    public $fillable = ['title', 'is_multiple'];
    public $visible = ['id','title','is_multiple'];
    public $casts = [
        'is_multiple' => 'boolean',
    ];

    public function restrictions()
    {
        return $this->hasMany('App\Models\Competitions\CompetitionRestriction', 'group_id');
    }

}

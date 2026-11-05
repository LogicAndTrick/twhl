<?php namespace App\Models\Competitions;

use App\Models\ModelWithExtraTypeInfo;

class CompetitionType extends ModelWithExtraTypeInfo {

    protected $table = 'competition_types';
    protected $fillable = ['name'];
    public $visible = ['id', 'name'];
    public $timestamps = false;

}

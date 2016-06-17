<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;

class CompetitionType extends Model {

    protected $table = 'competition_types';
    protected $fillable = ['name'];
    public $visible = ['id', 'name'];
    public $timestamps = false;

}

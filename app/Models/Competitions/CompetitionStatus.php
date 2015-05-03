<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;

class CompetitionStatus extends Model {

    const DRAFT = 1;
    const ACTIVE = 2;
    const VOTING = 3;
    const JUDGING = 4;
    const CLOSED = 5;
    //
	protected $table = 'competition_statuses';
    protected $fillable = ['name'];
    public $timestamps = false;

}

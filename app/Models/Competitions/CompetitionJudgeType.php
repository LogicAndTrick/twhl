<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;

class CompetitionJudgeType extends Model {

    const JUDGED_SEPARATE = 1;
    const JUDGED_TOGETHER = 2;
    const COMMUNITY_VOTE = 3;
	//
    public $table = 'competition_judge_types';
    public $timestamps = false;
    public $fillable = ['name'];
    public $visible = ['id', 'name'];

}

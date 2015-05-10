<?php namespace App\Models\Competitions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Competition extends Model {

    use SoftDeletes;

    public $table = 'competitions';
    public $fillable = ['status_id', 'type_id', 'judge_type_id', 'name', 'brief_text', 'brief_html', 'brief_attachment', 'open_date', 'close_date', 'voting_close_date', 'outro_text', 'outro_html'];

    public function getDates()
    {
        return ['open_date', 'close_date', 'voting_close_date', 'created_at', 'updated_at', 'deleted_at'];
    }

    public function engines()
    {
        return $this->belongsToMany('App\Models\Engine', 'competition_engines', 'competition_id', 'engine_id');
    }

    public function judges()
    {
        return $this->belongsToMany('App\Models\Accounts\User', 'competition_judges', 'competition_id', 'user_id');
    }

    public function restrictions()
    {
        return $this->belongsToMany('App\Models\Competitions\CompetitionRestriction', 'competition_rules', 'competition_id', 'restriction_id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Competitions\CompetitionStatus', 'status_id');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\Competitions\CompetitionType', 'type_id');
    }

    public function judge_type()
    {
        return $this->belongsTo('App\Models\Competitions\CompetitionJudgeType', 'judge_type_id');
    }

    public function isOpen()
    {
        return $this->status_id == CompetitionStatus::ACTIVE;
    }

    public function hasRestriction($id)
    {
        foreach ($this->restrictions as $r) {
            if ($r->id == $id) return true;
        }
        return false;
    }

    public function hasRestrictionInGroup($id)
    {
        foreach ($this->restrictions as $r) {
            if ($r->group_id == $id) return true;
        }
        return false;
    }
}

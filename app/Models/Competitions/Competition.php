<?php namespace App\Models\Competitions;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class Competition extends Model {

    use SoftDeletes;

    public $table = 'competitions';
    public $fillable = [
        'status_id', 'type_id', 'judge_type_id', 'name',
        'brief_text', 'brief_html', 'brief_attachment',
        'open_date', 'close_date', 'voting_close_date',
        'results_intro_text', 'results_intro_html', 'results_outro_text', 'results_outro_html'
    ];

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

    public function entries()
    {
        return $this->hasMany('App\Models\Competitions\CompetitionEntry', 'competition_id');
    }

    public function results()
    {
        return $this->hasMany('App\Models\Competitions\CompetitionResult', 'competition_id');
    }

    public function votes()
    {
        return $this->hasMany('App\Models\Competitions\CompetitionEntryVote', 'competition_id');
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

    /**
     * @return Carbon
     */
    public function getOpenTime()
    {
        return $this->open_date;
    }

    /**
     * @return Carbon
     */
    public function getCloseTime()
    {
        return $this->close_date->copy()->setTime(23,59,59);
    }

    /**
     * @return Carbon
     */
    public function getVotingOpenTime()
    {
        return $this->getEntryCloseTime()->addMinute()->second(0);
    }

    /**
     * @return Carbon
     */
    public function getVotingCloseTime()
    {
        if (!$this->voting_close_date) return null;
        return $this->voting_close_date->copy()->setTime(23,59,59);
    }

    /**
     * @return Carbon
     */
    public function getEntryCloseTime()
    {
        // People uploading or typing or whatever have a grace period of 1 hour
        return $this->getCloseTime()->addMinutes(60);
    }

    public function getStatusText() {
        if ($this->isClosed()) return 'Closed';
        if ($this->isDraft()) return 'Draft';
        if ($this->isJudging()) return 'Judging in Progress';
        if ($this->isVoting()) {
            if ($this->isVotingOpen()) return 'Voting in Progress';
            return 'Pending Vote Results';
        }
        if ($this->isActive()) {
            if ($this->isOpen()) return 'Open';
            if ($this->isVoted()) return 'Pending Vote Start';
            return 'Pending Judging';
        }
        return '';
    }

    public function isDraft()   { return $this->status_id == CompetitionStatus::DRAFT;   }
    public function isActive()  { return $this->status_id == CompetitionStatus::ACTIVE;  }
    public function isVoting()  { return $this->status_id == CompetitionStatus::VOTING;  }
    public function isJudging() { return $this->status_id == CompetitionStatus::JUDGING; }
    public function isClosed()  { return $this->status_id == CompetitionStatus::CLOSED;  }

    public function isJudged() { return $this->judge_type_id != CompetitionJudgeType::COMMUNITY_VOTE; }
    public function isVoted() { return $this->judge_type_id == CompetitionJudgeType::COMMUNITY_VOTE; }

    public function isOpen()
    {
        $now = Carbon::now();
        $open = $this->open_date;
        $close = $this->getCloseTime();
        return $this->isActive() && $open <= $now && $close >= $now;
    }

    public function isVotingOpen()
    {
        $now = Carbon::now();
        $open = $this->getVotingOpenTime();
        $close = $this->getVotingCloseTime();
        return $this->isVoting() && $open <= $now && $close >= $now;
    }

    public function canEnter()
    {
        $now = Carbon::now();
        $open = $this->getOpenTime();
        $close = $this->getCloseTime();
        return permission('CompetitionEnter')
            && ($this->isActive() || $this->isVoting() || $this->isJudging())
            && $open <= $now
            && $close >= $now;
    }

    public function canVote()
    {
        // User must be able to vote and voting must be open
        if (!permission('CompetitionEnter') || !$this->isVotingOpen()) return false;

        // Accounts created after competition start cannot vote
        if (Auth::user()->created_at > $this->getOpenTime()) return false;

        // Users who entered the competition cannot vote
        $uid = Auth::user()->id;
        foreach ($this->entries as $entry) {
            if ($entry->user_id == $uid) return false;
        }
        return true;
    }

    public function cantVoteReason() {
        if (!Auth::user()) return 'You are not logged in.';
        if (!permission('CompetitionEnter')) return 'You do not have permission to interact with competitions.';
        if (!$this->isVotingOpen()) return 'Voting has closed for this competition';
        if (Auth::user()->created_at > $this->getOpenTime()) return 'Your account was created after the competition started.';
        $uid = Auth::user()->id;
        foreach ($this->entries as $entry) {
            if ($entry->user_id == $uid) return 'You have entered this competition.';
        }
        return 'Unknown';
    }

    public function canJudge() {
        if (permission('CompetitionAdmin')) return true;
        return $this->isJudging() && $this->judges->contains(Auth::user());
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

    public function getEntriesForResults() {
        $entries = [0 => [], 1 => [], 2 => [], 3 => []];
        foreach ($this->entries as $entry) {
            $result = $this->results->where('entry_id', $entry->id)->first();
            if ($result) {
                $entries[$result->rank][] = $entry;
            }
        }
        return array_merge($entries[1], $entries[2], $entries[3], $entries[0]);
    }

    public function getEntriesForWinners() {
        $entries = [1 => [], 2 => [], 3 => []];
        foreach ($this->entries as $entry) {
            $result = $this->results->where('entry_id', $entry->id)->first();
            if ($result && $result->rank > 0) {
                $entries[$result->rank][] = $entry;
            }
        }
        return array_merge($entries[1], $entries[2], $entries[3]);
    }

    public function getEntriesForJudging() {
        $votes = $this->votes;
        return array_sort($this->entries, function($e) use ($votes) {
            $count = 0;
            foreach ($votes as $v) {
                if ($v->entry_id == $e->id) $count++;
            }
            return -$count;
        });
    }

    public function countVotesFor($id) {
        $count = 0;
        foreach ($this->votes as $v) {
            if ($v->entry_id == $id) $count++;
        }
        return $count;
    }
}

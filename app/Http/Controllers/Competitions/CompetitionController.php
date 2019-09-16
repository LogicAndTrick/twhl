<?php namespace App\Http\Controllers\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competitions\Competition;
use App\Models\Competitions\CompetitionEntry;
use App\Models\Competitions\CompetitionEntryVote;
use App\Models\Competitions\CompetitionStatus;
use Illuminate\Support\Facades\Auth;

class CompetitionController extends Controller {

	public function __construct() {
        $this->permission(['create', 'edit', 'delete', 'restore'], 'CompetitionAdmin');
	}

	public function getIndex() {
        $comps = Competition::with(['type', 'judge_type', 'results', 'entries', 'entries.user'])->where('status_id', '!=', CompetitionStatus::DRAFT)->get();
        return view('competitions/competition/index', [
            'comps' => $comps
        ]);
	}

    public function getBrief($id) {
        $comp = Competition::with(['engines', 'judges', 'restrictions', 'restrictions.group', 'status', 'type', 'judge_type', 'results', 'entries', 'entries.user', 'entries.screenshots'])->findOrFail($id);
        $rule_groups = [];
        foreach ($comp->restrictions as $r) {
            $t = $r->group->title;
            if (!array_key_exists($t, $rule_groups)) $rule_groups[$t] = [];
            $d = $r->content_html;
            $d = str_ireplace('{competition_id}', $comp->id, $d);
            $d = str_ireplace('{username}', Auth::user() ? Auth::user()->name : '[your_username]', $d);
            $rule_groups[$t][] = $d;
        }
        $user_entry = null;
        if (Auth::user()) {
            $user_entry = CompetitionEntry::with(['screenshots', 'user'])->whereUserId(Auth::user()->id)->whereCompetitionId($id)->first();
        }
        return view('competitions/competition/brief', [
            'comp' => $comp,
            'rule_groups' => $rule_groups,
            'user_entry' => $user_entry
        ]);
    }

    public function getVote($id) {
        $comp = Competition::with(['entries', 'entries.user', 'entries.screenshots'])->findOrFail($id);
        $user_votes = CompetitionEntryVote::whereUserId(Auth::user() ? Auth::user()->id : 0)->whereCompetitionId($id)->get();
        $voted_ids = $user_votes->map(function ($v) { return $v->entry_id; })->toBase();
        return view('competitions/competition/vote', [
            'comp' => $comp,
            'votes' => $voted_ids
        ]);
    }

    public function getEntryScreenshotGallery($id) {
        $entry = CompetitionEntry::with(['screenshots'])->findOrFail($id);
        return view('competitions/entry/_gallery', [
            'entry' => $entry
        ]);
    }

    public function getAddVote($id) {
        $entry = CompetitionEntry::findOrFail($id);
        $comp = Competition::findOrFail($entry->competition_id);
        if (!$comp->canVote()) abort(422);

        $user_votes = CompetitionEntryVote::whereUserId(Auth::user()->id)->whereCompetitionId($comp->id);
        $voted_ids = $user_votes->get()->map(function ($v) { return $v->entry_id; })->toBase();

        $result = '';
        $is_voted_for = false;
        if ($voted_ids->contains($entry->id)) {
            $vote = $user_votes->whereEntryId($entry->id)->first();
            $vote->delete();
            $is_voted_for = false;
            $result = 'Vote for this entry';
        } else if ($voted_ids->count() >= 3) {
            $is_voted_for = false;
            $result = 'You can only vote for 3 entries';
        } else {
            $vote = CompetitionEntryVote::Create(['competition_id' => $comp->id, 'user_id' => Auth::user()->id, 'entry_id' => $entry->id]);
            $is_voted_for = true;
            $result = 'You voted for this entry!';
        }
        return response()->json([
            'is_voted_for' => $is_voted_for,
            'status' => $result,
            'voted_ids' => $user_votes->get()
        ]);
    }
}

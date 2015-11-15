<?php namespace App\Http\Controllers\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competitions\Competition;
use App\Models\Competitions\CompetitionEntry;
use App\Models\Competitions\CompetitionJudgeType;
use App\Models\Competitions\CompetitionRestrictionGroup;
use App\Models\Competitions\CompetitionResult;
use App\Models\Competitions\CompetitionStatus;
use Carbon\Carbon;
use Request;
use Input;
use Auth;
use DB;
use Validator;

class CompetitionJudgingController extends Controller {

	public function __construct() {
        $this->permission(['publish'], 'CompetitionAdmin');
	}

	public function getView($id) {
        $comp = Competition::with(['judges', 'results', 'entries', 'entries.user', 'entries.screenshots', 'votes'])->findOrFail($id);
        if (!$comp->canJudge()) abort(404);
        return view('competitions/judging/view', [
            'comp' => $comp
        ]);
	}

    public function getPreview($id) {
        $comp = Competition::with(['judges', 'results', 'entries', 'entries.user', 'entries.screenshots'])->findOrFail($id);
        if (!$comp->canJudge()) return abort(404);

        return view('competitions/judging/preview', [
            'comp' => $comp
        ]);
    }

    public function postEdit() {
        $id = Request::input('id');
        $entry = CompetitionEntry::with(['competition', 'result'])->findOrFail($id);
        if (!$entry->competition->canJudge()) abort(404);
        $this->validate(Request::instance(), [
            'rank' => 'numeric',
            'content_text' => 'max:2000'
        ]);
        $text = Request::input('content_text');
        $rank = intval(Request::input('rank'));
        $create = $text || $rank;
        $values = ['content_text' => $text, 'content_html' => app('bbcode')->Parse($text), 'rank' => $rank];
        $result = $entry->result;
        if ($result) {
            if ($create) $result->update($values);
            else $result->delete();
        } else if ($create) {
            CompetitionResult::Create(array_merge(['competition_id' => $entry->competition_id, 'entry_id' => $entry->id], $values));
        }
        return response()->json(['success' => true, 'action' => $create ? 'created' : 'removed']);
    }

    public function getCreateEntry($id) {
        $comp = Competition::findOrFail($id);
        if (!$comp->canJudge()) abort(404);

        return view('competitions/judging/create-entry', [
            'comp' => $comp
        ]);
    }

    public function postCreateEntry() {
       $id = Request::input('id');
       $comp = Competition::findOrFail($id);
       if (!$comp->canJudge()) return abort(404);

       Validator::extend('no_entry', function($attribute, $value, $parameters) use ($id) {
           return !CompetitionEntry::whereUserId($value)->whereCompetitionId($id)->first();
       });
        $this->validate(Request::instance(), [
            'user_id' => 'required|numeric|no_entry'
        ], [
            'no_entry' => 'This user already has an entry.'
        ]);

       $entry = CompetitionEntryController::CreateOrUpdateEntry($this, $comp, true);
       return redirect('competition-judging/view/'.$id);
   	}

    public function getEditEntry($id) {
        $entry = CompetitionEntry::findOrFail($id);
        $comp = Competition::findOrFail($entry->competition_id);
        if (!$comp->canJudge()) abort(404);

        return view('competitions/judging/edit-entry', [
            'comp' => $comp,
            'entry' => $entry
        ]);
    }

    public function postEditEntry() {
       $id = Request::input('id');
       $comp = Competition::findOrFail($id);
       if (!$comp->canJudge()) return abort(404);

       $entry = CompetitionEntryController::CreateOrUpdateEntry($this, $comp, true);
       return redirect('competition-judging/view/'.$id);
   	}

    public function getPublish($id) {
        $comp = Competition::findOrFail($id);
        if (!$comp->isJudging()) return abort(404);

        return view('competitions/judging/publish', [
            'comp' => $comp
        ]);
    }

    public function postPublish() {
        $id = Request::input('id');
        $comp = Competition::findOrFail($id);
        $comp->update([
            'status_id' => CompetitionStatus::CLOSED
        ]);
        return redirect('competition/results/'.$id);
   	}
}

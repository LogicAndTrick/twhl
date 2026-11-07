<?php

namespace App\Http\Controllers\Competitions;

use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Competitions\Competition;
use App\Models\Competitions\CompetitionEntry;
use App\Models\Competitions\CompetitionResult;
use App\Models\Competitions\CompetitionStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

#[TypedRouting]
class CompetitionJudgingController extends Controller
{

    public function __construct()
    {
        // logged in users can see the judging panel, if they are judges for the competition
        $this->permission(['view', 'edit', 'preview', 'createEntry', 'editEntry'], true);
        $this->permission(['publish'], 'CompetitionAdmin');
    }

    public function getView(int $id)
    {
        $comp = Competition::with(['judges', 'results', 'entries', 'entries.user', 'entries.screenshots', 'votes'])->findOrFail($id);
        if (!$comp->canJudge()) abort(404);

        return view('competitions/judging/view', [
            'comp' => $comp
        ]);
    }

    public function getPreview(int $id)
    {
        $comp = Competition::with(['judges', 'results', 'entries', 'entries.user', 'entries.screenshots'])->findOrFail($id);
        if (!$comp->canJudge()) return abort(404);

        return view('competitions/judging/preview', [
            'comp' => $comp
        ]);
    }

    public function postEdit(Request $request)
    {
        $id = $request->integer('id');
        $entry = CompetitionEntry::with(['competition', 'result'])->findOrFail($id);
        if (!$entry->competition->canJudge()) abort(404);

        $this->validate($request, [
            'rank' => ['integer'],
            'content_text' => ['max:10000']
        ]);
        $text = $request->input('content_text');
        $rank = $request->integer('rank');
        $create = $text || $rank;
        $values = ['content_text' => $text, 'content_html' => bbcode($text), 'rank' => $rank];
        $result = $entry->result;
        if ($result) {
            if ($create) $result->update($values);
            else $result->delete();
        } else if ($create) {
            CompetitionResult::Create(array_merge(['competition_id' => $entry->competition_id, 'entry_id' => $entry->id], $values));
        }
        return response()->json(['success' => true, 'action' => $create ? 'created' : 'removed']);
    }

    public function getCreateEntry(int $id)
    {
        $comp = Competition::findOrFail($id);
        if (!$comp->canJudge()) abort(404);

        return view('competitions/judging/create-entry', [
            'comp' => $comp
        ]);
    }

    public function postCreateEntry(Request $request)
    {
        $id = $request->integer('id');
        $comp = Competition::findOrFail($id);
        if (!$comp->canJudge()) return abort(404);

        $this->validate($request, [
            'user_id' => [
                'required',
                'numeric',
                Rule::unique('competition_entries', 'user_id')
                    ->where('competition_id', $id)
                    ->withoutTrashed()
            ]
        ], [
            'unique' => 'This user already has an entry.'
        ]);

        $entry = CompetitionEntryController::CreateOrUpdateEntry($request, $this, $comp, true);
        return redirect('competition-judging/view/' . $id);
    }

    public function getEditEntry(int $id)
    {
        $entry = CompetitionEntry::findOrFail($id);
        $comp = Competition::findOrFail($entry->competition_id);
        if (!$comp->canJudge()) abort(404);

        return view('competitions/judging/edit-entry', [
            'comp' => $comp,
            'entry' => $entry
        ]);
    }

    public function postEditEntry(Request $request)
    {
        $id = $request->integer('id');
        $comp = Competition::findOrFail($id);
        if (!$comp->canJudge()) return abort(404);

        $entry = CompetitionEntryController::CreateOrUpdateEntry($request, $this, $comp, true);
        return redirect('competition-judging/view/' . $id);
    }

    public function getPublish(int $id)
    {
        $comp = Competition::findOrFail($id);
        if (!$comp->isJudging()) return abort(404);

        return view('competitions/judging/publish', [
            'comp' => $comp
        ]);
    }

    public function postPublish(Request $request)
    {
        $id = $request->integer('id');
        $comp = Competition::findOrFail($id);
        $comp->update([
            'status_id' => CompetitionStatus::CLOSED
        ]);
        return redirect('competition/results/' . $id);
    }
}

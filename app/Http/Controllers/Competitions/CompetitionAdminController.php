<?php namespace App\Http\Controllers\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competitions\Competition;
use App\Models\Competitions\CompetitionJudgeType;
use App\Models\Competitions\CompetitionRestrictionGroup;
use App\Models\Competitions\CompetitionStatus;
use Carbon\Carbon;
use Request;
use Input;
use Auth;
use DB;

class CompetitionAdminController extends Controller {

	public function __construct() {
        $this->permission(['create', 'edit', 'editrules', 'delete', 'restore'], 'CompetitionAdmin');
	}

	public function getCreate() {
        return view('competitions/admin/create', [

        ]);
	}

    public function postCreate() {
        $this->validate(Request::instance(), [
            'competition_name' => 'required|max:255',
            'status_id' => 'required|numeric',
            'type_id' => 'required|numeric',
            'judge_type_id' => 'required|numeric',
            'engines' => 'required|array',
            'judges' => 'array',
            'brief_text' => 'required|max:10000',
            'open_date' => 'required|date_format:d/m/Y',
            'close_date' => 'required|date_format:d/m/Y',
            'voting_close_date' => 'required_if:judge_type_id,' . CompetitionJudgeType::COMMUNITY_VOTE . '|date_format:d/m/Y',
            'brief_attachment' => 'max:16384'
        ]);

        $competition = Competition::Create([
            'status_id' => Request::input('status_id'),
            'type_id' => Request::input('type_id'),
            'judge_type_id' => Request::input('judge_type_id'),
            'name' => Request::input('competition_name'),
            'brief_text' => Request::input('brief_text'),
            'brief_html' => app('bbcode')->Parse(Request::input('brief_text')),
            'open_date' => Carbon::createFromFormat('d/m/Y', Request::input('open_date')),
            'close_date' => Carbon::createFromFormat('d/m/Y', Request::input('close_date')),
            'voting_close_date' => Request::input('voting_close_date') ? Carbon::createFromFormat('d/m/Y', Request::input('voting_close_date')) : null,
        ]);

        $engines = Request::input('engines');
        $judges = Request::input('judges');
        if (is_array($engines)) $competition->engines()->attach($engines);
        if (is_array($judges)) $competition->judges()->attach($judges);

        $attachment = Request::file('brief_attachment');
        if ($attachment) {
            $dir = public_path('uploads/competition/attachments');
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $name = 'twhl-competition-' . $competition->id . '.' . strtolower($attachment->getClientOriginalExtension());
            $attachment->move($dir, $name);
            $competition->brief_attachment = $name;
            $competition->save();
        }

        return redirect('competition/brief/'.$competition->id);
    }

    public function getEdit($id) {
        $comp = Competition::findOrFail($id);
        return view('competitions/admin/edit', [
            'comp' => $comp
        ]);
    }

    public function postEdit() {
        $this->validate(Request::instance(), [
            'competition_name' => 'required|max:255',
            'status_id' => 'required|numeric',
            'type_id' => 'required|numeric',
            'judge_type_id' => 'required|numeric',
            'engines' => 'required|array',
            'judges' => 'array',
            'brief_text' => 'required|max:10000',
            'results_intro_text' => 'required_if:status_id,' . CompetitionStatus::CLOSED . '|max:10000',
            'results_outro_text' => 'max:10000',
            'open_date' => 'required|date_format:d/m/Y',
            'close_date' => 'required|date_format:d/m/Y',
            'voting_close_date' => 'required_if:judge_type_id,' . CompetitionJudgeType::COMMUNITY_VOTE . '|date_format:d/m/Y',
            'brief_attachment' => 'max:16384'
        ]);
        $id = Request::input('id');
        $comp = Competition::findOrFail($id);

        $in = Request::input('results_intro_text');
        if (!$in) $in = '';
        $out = Request::input('results_outro_text');
        if (!$out) $out = '';

        $brief_attachment = $comp->brief_attachment;
        $dir = public_path('uploads/competition/attachments');
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $path = strlen($comp->brief_attachment) == 0 ? null : $dir . '/' . $comp->brief_attachment;

        $attachment = Request::file('brief_attachment');
        if ($attachment) {
            if ($path && file_exists($path) && is_file($path)) unlink($path);
            $name = 'twhl-competition-' . $comp->id . '.' . strtolower($attachment->getClientOriginalExtension());
            $attachment->move($dir, $name);
            $brief_attachment = $name;
        } else if (Request::input('delete_attachment')) {
            if ($path && file_exists($path) && is_file($path)) unlink($path);
            $brief_attachment = '';
        }

        $comp->update([
            'status_id' => Request::input('status_id'),
            'type_id' => Request::input('type_id'),
            'judge_type_id' => Request::input('judge_type_id'),
            'name' => Request::input('competition_name'),
            'brief_text' => Request::input('brief_text'),
            'brief_html' => app('bbcode')->Parse(Request::input('brief_text')),
            'brief_attachment' => $brief_attachment,
            'open_date' => Carbon::createFromFormat('d/m/Y', Request::input('open_date')),
            'close_date' => Carbon::createFromFormat('d/m/Y', Request::input('close_date')),
            'voting_close_date' => Request::input('voting_close_date') ? Carbon::createFromFormat('d/m/Y', Request::input('voting_close_date')) : null,
            'results_intro_text' => $in,
            'results_intro_html' => app('bbcode')->Parse($in),
            'results_outro_text' => $out,
            'results_outro_html' => app('bbcode')->Parse($out),
        ]);

        $engines = Request::input('engines');
        $judges = Request::input('judges');
        if (is_array($engines)) $comp->engines()->sync($engines);
        if (is_array($judges)) $comp->judges()->sync($judges);

        return redirect('competition/brief/'.$comp->id);
    }

    public function getEditRules($id) {
        $comp = Competition::with(['restrictions'])->findOrFail($id);
        $groups = CompetitionRestrictionGroup::with(['restrictions'])->get();
        return view('competitions/admin/edit-rules', [
            'comp' => $comp,
            'groups' => $groups
        ]);
    }

    public function postEditRules() {
        $id = Request::input('id');
        $comp = Competition::findOrFail($id);

        $groups = Request::input('restrictions');
        if (!is_array($groups)) $groups = [];

        $ids = [];
        foreach ($groups as $g) {
            if (!is_array($g)) continue;
            foreach ($g as $id) {
                if ($id > 0) $ids[] = $id;
            }
        }

        $comp->restrictions()->sync($ids);
        return redirect('competition/brief/'.$comp->id);
    }

    public function getDelete($id) {
        return view('competitions/admin/delete', [

        ]);
    }

    public function getRestore($id) {
        return view('competitions/admin/restore', [

        ]);
    }
}

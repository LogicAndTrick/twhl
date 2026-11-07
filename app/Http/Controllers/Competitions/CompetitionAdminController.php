<?php

namespace App\Http\Controllers\Competitions;

use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Competitions\Competition;
use App\Models\Competitions\CompetitionJudgeType;
use App\Models\Competitions\CompetitionRestrictionGroup;
use App\Models\Competitions\CompetitionStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

#[TypedRouting]
class CompetitionAdminController extends Controller
{
    public function __construct()
    {
        $this->permission(['create', 'edit', 'editRules', 'delete', 'restore'], 'CompetitionAdmin');
    }

    public function getCreate()
    {
        return view('competitions/admin/create');
    }

    public function postCreate(Request $request)
    {
        $this->validate($request, [
            'competition_name' => ['required', 'max:255'],
            'status_id' => ['required', 'numeric'],
            'type_id' => ['required', 'numeric'],
            'judge_type_id' => ['required', 'numeric'],
            'engines' => ['required', 'array'],
            'judges' => ['array'],
            'brief_text' => ['required', 'max:10000'],
            'open_date' => ['required', 'date_format:d/m/Y'],
            'close_date' => ['required', 'date_format:d/m/Y'],
            'voting_close_date' => [
                Rule::requiredIf(fn() => $request->integer('judge_type_id') === CompetitionJudgeType::COMMUNITY_VOTE),
                'date_format:d/m/Y',
            ],
            'brief_attachment' => [
                'max:16384',
                'extensions:zip,rar,7z,pdf,png,jpg,jpeg',
                'mimetypes:application/pdf,image/png,image/jpeg,application/zip,application/x-zip-compressed,application/x-rar-compressed,application/vnd.rar,application/x-7z-compressed',
            ],
        ], [
            'extensions' => 'Only the following file formats are allowed: zip, rar, 7z, pdf, png, jpg, jpeg'
        ]);

        $competition = Competition::Create([
            'status_id' => $request->integer('status_id'),
            'type_id' => $request->integer('type_id'),
            'judge_type_id' => $request->integer('judge_type_id'),
            'name' => $request->input('competition_name'),
            'brief_text' => $request->input('brief_text'),
            'brief_html' => bbcode($request->input('brief_text')),
            'open_date' => Carbon::createFromFormat('d/m/Y', $request->input('open_date')),
            'close_date' => Carbon::createFromFormat('d/m/Y', $request->input('close_date')),
            'voting_close_date' => $request->input('voting_close_date') ? Carbon::createFromFormat('d/m/Y', $request->input('voting_close_date')) : null,
        ]);

        $engines = $request->input('engines');
        $judges = $request->input('judges');
        if (is_array($engines)) $competition->engines()->attach($engines);
        if (is_array($judges)) $competition->judges()->attach($judges);

        $attachment = $request->file('brief_attachment');
        if ($attachment) {
            $dir = public_path('uploads/competition/attachments');
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $name = 'twhl-competition-' . $competition->id . '.' . strtolower($attachment->getClientOriginalExtension());
            $attachment->move($dir, $name);
            $competition->brief_attachment = $name;
            $competition->save();
        }

        return redirect('competition/brief/' . $competition->id);
    }

    public function getEdit(int $id)
    {
        $comp = Competition::findOrFail($id);
        return view('competitions/admin/edit', [
            'comp' => $comp
        ]);
    }

    public function postEdit(Request $request)
    {
        $this->validate($request, [
            'competition_name' => ['required', 'max:255'],
            'status_id' => ['required', 'numeric'],
            'type_id' => ['required', 'numeric'],
            'judge_type_id' => ['required', 'numeric'],
            'engines' => ['required', 'array'],
            'judges' => ['array'],
            'brief_text' => ['required', 'max:10000'],
            'results_intro_text' => [
                Rule::requiredIf(fn() => $request->integer('status_id') === CompetitionStatus::CLOSED),
                'max:10000',
            ],
            'results_outro_text' => ['max:10000'],
            'open_date' => ['required', 'date_format:d/m/Y'],
            'close_date' => ['required', 'date_format:d/m/Y'],
            'voting_close_date' => [
                Rule::requiredIf(fn() => $request->integer('judge_type_id') === CompetitionJudgeType::COMMUNITY_VOTE),
                'date_format:d/m/Y',
            ],
            'brief_attachment' => [
                'max:16384',
                'extensions:zip,rar,7z,pdf,png,jpg,jpeg',
                'mimetypes:application/pdf,image/png,image/jpeg,application/zip,application/x-zip-compressed,application/x-rar-compressed,application/vnd.rar,application/x-7z-compressed',
            ],
        ], [
            'extensions' => 'Only the following file formats are allowed: zip, rar, 7z, pdf, png, jpg, jpeg'
        ]);
        $id = $request->integer('id');
        $comp = Competition::findOrFail($id);

        $in = $request->input('results_intro_text');
        if (!$in) $in = '';
        $out = $request->input('results_outro_text');
        if (!$out) $out = '';

        $brief_attachment = $comp->brief_attachment;
        $dir = public_path('uploads/competition/attachments');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $path = strlen($comp->brief_attachment) == 0 ? null : $dir . '/' . $comp->brief_attachment;

        $attachment = $request->file('brief_attachment');
        if ($attachment) {
            if ($path && file_exists($path) && is_file($path)) unlink($path);
            $name = 'twhl-competition-' . $comp->id . '.' . strtolower($attachment->getClientOriginalExtension());
            $attachment->move($dir, $name);
            $brief_attachment = $name;
        } else if ($request->input('delete_attachment')) {
            if ($path && file_exists($path) && is_file($path)) unlink($path);
            $brief_attachment = '';
        }

        $comp->update([
            'status_id' => $request->integer('status_id'),
            'type_id' => $request->integer('type_id'),
            'judge_type_id' => $request->integer('judge_type_id'),
            'name' => $request->input('competition_name'),
            'brief_text' => $request->input('brief_text'),
            'brief_html' => bbcode($request->input('brief_text')),
            'brief_attachment' => $brief_attachment,
            'open_date' => Carbon::createFromFormat('d/m/Y', $request->input('open_date')),
            'close_date' => Carbon::createFromFormat('d/m/Y', $request->input('close_date')),
            'voting_close_date' => $request->input('voting_close_date') ? Carbon::createFromFormat('d/m/Y', $request->input('voting_close_date')) : null,
            'results_intro_text' => $in,
            'results_intro_html' => bbcode($in),
            'results_outro_text' => $out,
            'results_outro_html' => bbcode($out),
        ]);

        $engines = $request->input('engines');
        $judges = $request->input('judges');
        if (is_array($engines)) $comp->engines()->sync($engines);
        if (is_array($judges)) $comp->judges()->sync($judges);

        return redirect('competition/brief/' . $comp->id);
    }

    public function getEditRules(int $id)
    {
        $comp = Competition::with(['restrictions'])->findOrFail($id);
        $groups = CompetitionRestrictionGroup::with(['restrictions'])->get();
        return view('competitions/admin/edit-rules', [
            'comp' => $comp,
            'groups' => $groups
        ]);
    }

    public function postEditRules(Request $request)
    {
        $id = $request->integer('id');
        $comp = Competition::findOrFail($id);

        $groups = $request->input('restrictions');
        if (!is_array($groups)) $groups = [];

        $ids = [];
        foreach ($groups as $g) {
            if (!is_array($g)) continue;
            foreach ($g as $id) {
                if ($id > 0) $ids[] = $id;
            }
        }

        $comp->restrictions()->sync($ids);
        return redirect('competition/brief/' . $comp->id);
    }

    public function getDelete(int $id)
    {
        $comp = Competition::findOrFail($id);
        return view('competitions/admin/delete', [
            'comp' => $comp
        ]);
    }

    public function getRestore(int $id)
    {
        $comp = Competition::findOrFail($id);
        return view('competitions/admin/restore', [
            'comp' => $comp
        ]);
    }
}

<?php

namespace App\Http\Controllers\Competitions;

use App\Helpers\Image;
use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Competitions\Competition;
use App\Models\Competitions\CompetitionEntry;
use App\Models\Competitions\CompetitionEntryScreenshot;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

#[TypedRouting]
class CompetitionEntryController extends Controller
{

    public function __construct()
    {
        $this->permission(['submit', 'manage', 'delete', 'addScreenshot', 'deleteScreenshot'], 'CompetitionEnter');
        $this->permission(['restore'], 'CompetitionAdmin');
    }

    public static function CreateOrUpdateEntry(Request $request, Controller $controller, Competition $competition, bool $request_user_id = false): CompetitionEntry
    {
        $user = $request_user_id ? User::find($request->integer('user_id')) : Auth::user();
        $entry = !$user ? null : CompetitionEntry::whereUserId($user->id)->whereCompetitionId($competition->id)->first();

        $controller->validate($request, [
            'id' => ['required', 'integer'],
            'user_id' => [Rule::requiredIf($request_user_id), 'integer'],
            'title' => ['required', 'max:255'],
            'content_text' => ['max:2000'],
            'screenshot' => [
                Rule::requiredIf(!$entry),
                'max:2048',
                'extensions:jpeg,jpg,png',
                'mimetypes:image/jpeg,image/png',
                Rule::dimensions()->maxWidth(3000)->maxHeight(3000),
                function ($attr, $value, Closure $fail) use ($entry) {
                    if ($entry && $entry->screenshots->count() >= 10) {
                        $fail('You can\'t add more than 10 screenshots to a competition entry');
                    }
                }
            ],
            '__upload_method' => [
                Rule::requiredIf(!$competition->isVoted()),
                Rule::in(['file', 'link'])
            ],
            'link' => [
                Rule::requiredIf($request->input('__upload_method') == 'link'),
                'max:512',
                Rule::url()->protocols(['http', 'https'])
            ],
            'file' => [
                Rule::requiredIf($request->input('__upload_method') == 'file'),
                'max:16384',
                'extensions:zip,rar,7z',
                'mimetypes:application/zip,application/x-zip-compressed,application/x-rar-compressed,application/vnd.rar,application/x-7z-compressed'
            ]
        ], [
            'file.extensions' => 'Only the following file formats are allowed: zip, rar, 7z',
            'screenshot.extensions' => 'Only the following file formats are allowed: jpg, png',
            'dimensions' => 'The image cannot have a width or height of more than 3000 pixels',
        ]);

        if (!$user) abort(404);

        // Create the entry if it doesn't exist
        if (!$entry) {
            $entry = CompetitionEntry::Create([
                'competition_id' => $competition->id,
                'user_id' => $user->id
            ]);

            // Upload the screenshot
            $screen = $request->file('screenshot');
            if ($screen) {
                CompetitionEntryController::CreateEntryScreenshot($entry, $screen);
            }
        }
        $uploaded = $request->input('__upload_method') == 'file';
        $location = $request->input('link');
        if (!$location) $location = '';

        if ($uploaded) {
            $file = $request->file('file');
            $dir = public_path('uploads/competition/entries');
            $location = 'entry-' . $entry->id . '-' . preg_replace('/[^a-zA-Z0-9-_]/sm', '-', $user->name) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($dir, $location);
        }

        $entry->update([
            'title' => $request->input('title'),
            'content_text' => $request->input('content_text'),
            'content_html' => bbcode($request->input('content_text')),
            'is_hosted_externally' => !$uploaded,
            'file_location' => $location
        ]);

        return $entry;
    }

    protected static function CreateEntryScreenshot(CompetitionEntry $entry, UploadedFile $screen): CompetitionEntryScreenshot
    {
        // We need the id to save the files, so create the db object first
        $shot = CompetitionEntryScreenshot::Create([
            'entry_id' => $entry->id,
            'image_thumb' => '',
            'image_full' => ''
        ]);

        // Save the screenshot at various sizes
        $temp_dir = storage_path('app/uploads/competition/temp');
        $temp_name = $shot->id . '_temp.' . strtolower($screen->getClientOriginalExtension());
        $screen->move($temp_dir, $temp_name);
        $thumbs = Image::MakeThumbnails(
            $temp_dir . '/' . $temp_name,
            Image::$comp_image_sizes,
            public_path('uploads/competition/'),
            strval($shot->id),
            true
        );
        unlink($temp_dir . '/' . $temp_name);

        // Update the shot object
        $shot->update([
            'image_thumb' => $thumbs[0] ? $thumbs[0] : $thumbs[1],
            'image_full' => $thumbs[1]
        ]);

        return $shot;
    }

    public function postSubmit(Request $request)
    {
        $id = $request->input('id');
        $comp = Competition::findOrFail($id);
        if (!$comp->canEnter()) return abort(404);

        $entry = CompetitionEntryController::CreateOrUpdateEntry($request, $this, $comp, false);
        return redirect('competition/brief/' . $id);
    }

    public function getManage(int $id)
    {
        $entry = CompetitionEntry::with(['screenshots'])->findOrFail($id);
        $comp = Competition::findOrFail($entry->competition_id);

        if (!permission('CompetitionAdmin') && !$comp->canJudge() && $entry->user_id != Auth::id()) abort(404);
        if (!$comp->canEnter() && !$comp->canJudge()) return abort(404);

        return view('competitions/entry/manage', [
            'entry' => $entry,
            'comp' => $comp
        ]);
    }

    public function postAddScreenshot(Request $request)
    {
        $id = $request->integer('id');
        $entry = CompetitionEntry::findOrFail($id);

        $this->validate($request, [
            'id' => ['required', 'integer'],
            'screenshot' => [
                'required',
                'file',
                'max:2048',
                'extensions:jpeg,jpg,png',
                'mimetypes:image/jpeg,image/png',
                Rule::dimensions()->maxWidth(3000)->maxHeight(3000),
                function ($attr, $value, Closure $fail) use ($entry) {
                    if ($entry && $entry->screenshots->count() >= 10) {
                        $fail('You can\'t add more than 10 screenshots to a competition entry');
                    }
                }
            ],
        ], [
            'extensions' => 'Only the following file formats are allowed: jpg, png',
            'dimensions' => 'The image cannot have a width or height of more than 3000 pixels'
        ]);

        $comp = Competition::findOrFail($entry->competition_id);

        if (!permission('CompetitionAdmin') && !$comp->canJudge() && $entry->user_id != Auth::user()->id) abort(404);
        if (!$comp->canEnter() && !$comp->canJudge()) return abort(404);

        // Upload the screenshot
        $screen = $request->file('screenshot');
        if ($screen) {
            CompetitionEntryController::CreateEntryScreenshot($entry, $screen);
        }
        return redirect('competition-entry/manage/' . $entry->id);
    }

    public function postDeleteScreenshot(Request $request)
    {
        $id = $request->input('id');
        $shot = CompetitionEntryScreenshot::findOrFail($id);

        $entry = CompetitionEntry::findOrFail($shot->entry_id);
        $comp = Competition::findOrFail($entry->competition_id);

        if (!permission('CompetitionAdmin') && !$comp->canJudge() && $entry->user_id != Auth::user()->id) abort(404);
        if (!$comp->canEnter() && !$comp->canJudge()) return abort(404);

        $shot->delete();
        $location = public_path('uploads/competition/' . $shot->image_thumb);
        if (is_file($location)) unlink($location);
        $location = public_path('uploads/competition/' . $shot->image_full);
        if (is_file($location)) unlink($location);

        return redirect('competition-entry/manage/' . $entry->id);
    }

    public function getDelete(int $id)
    {
        $entry = CompetitionEntry::findOrFail($id);
        $comp = Competition::findOrFail($entry->competition_id);

        if (!permission('CompetitionAdmin') && !$comp->canJudge() && $entry->user_id != Auth::user()->id) abort(404);
        if (!$comp->canEnter() && !$comp->canJudge()) return abort(404);

        return view('competitions/entry/delete', [
            'entry' => $entry,
            'comp' => $comp
        ]);
    }
    public function postDelete(Request $request)
    {
        $id = $request->integer('id');

        $entry = CompetitionEntry::findOrFail($id);
        $comp = Competition::findOrFail($entry->competition_id);

        if (!permission('CompetitionAdmin') && !$comp->canJudge() && $entry->user_id != Auth::user()->id) abort(404);
        if (!$comp->canEnter() && !$comp->canJudge()) return abort(404);

        $entry->delete();
        return redirect('competition/brief/' . $comp->id);
    }

    public function getRestore(int $id)
    {
        $entry = CompetitionEntry::withTrashed()->findOrFail($id);
        $comp = Competition::findOrFail($entry->competition_id);

        return view('competitions/entry/restore', [
            'entry' => $entry,
            'comp' => $comp
        ]);
    }
}

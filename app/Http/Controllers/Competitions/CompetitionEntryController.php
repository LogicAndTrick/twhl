<?php namespace App\Http\Controllers\Competitions;

use App\Helpers\Image;
use App\Http\Controllers\Controller;
use App\Models\Competitions\Competition;
use App\Models\Competitions\CompetitionEntry;
use App\Models\Competitions\CompetitionEntryScreenshot;
use Request;
use Input;
use Auth;
use Validator;

class CompetitionEntryController extends Controller {

	public function __construct() {
        $this->permission(['create', 'edit', 'delete', 'addscreenshot', 'deletescreenshot'], 'CompetitionEnter');
        $this->permission(['restore'], 'CompetitionAdmin');
	}

	public function postSubmit() {
        $func = function($attribute, $value, $parameters) {
            return in_array(strtolower($value->getClientOriginalExtension()), $parameters);
        };
        Validator::extend('valid_extension_file', $func);
        Validator::extend('valid_extension_screen', $func);

        $id = Request::input('id');
        $comp = Competition::findOrFail($id);
        if (!$comp->canEnter()) return abort(404);

        $entry = CompetitionEntry::whereUserId(Auth::user()->id)->whereCompetitionId($id)->first();
        $screen_valid = $entry ? '' : 'required|';

        $file_valid = $comp->isVoted() ? '' : 'required|';

        $this->validate(Request::instance(), [
            'id' => 'required|numeric',
            'title' => 'required|max:255',
            'content_text' => 'max:2000',
            'screenshot' => $screen_valid . 'max:2048|valid_extension_screen:jpeg,jpg,png',

            '__upload_method' => $file_valid.'in:file,link',
            'link' => 'required_if:__upload_method,link|max:512',
            'file' => 'required_if:__upload_method,file|max:16384|valid_extension_file:zip,rar,7z'
        ], [
            'valid_extension_file' => 'Only the following file formats are allowed: zip, rar, 7z',
            'valid_extension_screen' => 'Only the following file formats are allowed: jpg, png',
        ]);

        // Create the entry if it doesn't exist
        if (!$entry) {
            $entry = CompetitionEntry::Create([
                'competition_id' => $id,
                'user_id' => Auth::user()->id
            ]);

            // Upload the screenshot
            $screen = Request::file('screenshot');
            if ($screen) {
                $this->makeScreenshot($entry, $screen);
            }
        }

        $uploaded = Request::input('__upload_method') == 'file';
        $location = Request::input('link');
        if (!$location) $location = '';

        if ($uploaded && !$comp->isVoted()) {
            $file = Request::file('file');
            $dir = public_path('uploads/competition/entries');
            $location = 'entry-' . $entry->id . '-' . preg_replace('/[^a-z0-9-_]/sm', '-', Auth::user()->name) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($dir, $location);
        }

        $entry->update([
            'title' => Request::input('title'),
            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text')),
            'is_hosted_externally' => !$uploaded,
            'file_location' => $location
        ]);

        return redirect('competition/brief/'.$id);
	}

    public function getManage($id) {
        $entry = CompetitionEntry::with(['screenshots'])->findOrFail($id);
        if (!permission('CompetitionAdmin') || $entry->user_id != Auth::user()->id) abort(404);

        $comp = Competition::findOrFail($entry->competition_id);
        if (!$comp->canEnter()) return abort(404);

        return view('competitions/entry/manage', [
            'entry' => $entry,
            'comp' => $comp
        ]);
    }

    public function postAddScreenshot() {
        Validator::extend('valid_extension', function($attribute, $value, $parameters) {
            return in_array(strtolower($value->getClientOriginalExtension()), $parameters);
        });
        $this->validate(Request::instance(), [
            'id' => 'required|numeric',
            'screenshot' => 'required|max:2048|valid_extension:jpeg,jpg,png',
        ], [
            'valid_extension' => 'Only the following file formats are allowed: jpg, png',
        ]);

        $id = Request::input('id');
        $entry = CompetitionEntry::findOrFail($id);
        if (!permission('CompetitionAdmin') || $entry->user_id != Auth::user()->id) abort(404);

        $comp = Competition::findOrFail($entry->competition_id);
        if (!$comp->canEnter()) return abort(404);

        // Upload the screenshot
        $screen = Request::file('screenshot');
        if ($screen) {
            $this->makeScreenshot($entry, $screen);
        }
        return redirect('competition-entry/manage/'.$entry->id);
    }

    public function postDeleteScreenshot() {
        $id = Request::input('id');
        $shot = CompetitionEntryScreenshot::findOrFail($id);
        $entry = CompetitionEntry::findOrFail($shot->entry_id);
        if (!permission('CompetitionAdmin') || $entry->user_id != Auth::user()->id) abort(404);

        $comp = Competition::findOrFail($entry->competition_id);
        if (!$comp->canEnter()) return abort(404);

        $shot->delete();
        $location = public_path('uploads/competition/'.$shot->image_thumb);
        if (is_file($location)) unlink($location);
        $location = public_path('uploads/competition/'.$shot->image_full);
        if (is_file($location)) unlink($location);

        return redirect('competition-entry/manage/'.$entry->id);
    }

    private function makeScreenshot($entry, $screen) {
        // We need the id to save the files, so create the db object first
        $shot = CompetitionEntryScreenshot::Create([
            'entry_id' => $entry->id,
            'image_thumb' => '',
            'image_full' => ''
        ]);

        // Save the screenshot at various sizes
        $temp_dir = public_path('uploads/competition/temp');
        $temp_name = $shot->id . '_temp.' . strtolower($screen->getClientOriginalExtension());
        $screen->move($temp_dir, $temp_name);
        $thumbs = Image::MakeThumbnails(
            $temp_dir . '/' . $temp_name, Image::$comp_image_sizes,
            public_path('uploads/competition/'), $shot->id . '.' . strtolower($screen->getClientOriginalExtension())
        );
        unlink($temp_dir . '/' . $temp_name);

        // Update the shot object
        $shot->update([
            'image_thumb' => $thumbs[0] ? $thumbs[0] : $thumbs[1],
            'image_full' => $thumbs[1]
        ]);

        return $shot;
    }

    public function getDelete($id) {
        $entry = CompetitionEntry::findOrFail($id);
        if (!permission('CompetitionAdmin') || $entry->user_id != Auth::user()->id) abort(404);

        $comp = Competition::findOrFail($entry->competition_id);
        if (!$comp->canEnter()) return abort(404);

        return view('competitions/entry/delete', [
            'entry' => $entry,
            'comp' => $comp
        ]);
    }
    public function postDelete() {
        $id = Request::input('id');

        $entry = CompetitionEntry::findOrFail($id);
        if (!permission('CompetitionAdmin') || $entry->user_id != Auth::user()->id) abort(404);

        $comp = Competition::findOrFail($entry->competition_id);
        if (!$comp->canEnter()) return abort(404);

        $entry->delete();
        return redirect('competition/brief/'.$comp->id);
    }

    public function getRestore($id) {
        return view('competitions/entry/restore', [

        ]);
    }
}

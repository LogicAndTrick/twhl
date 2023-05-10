<?php namespace App\Http\Controllers\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competitions\CompetitionRestriction;
use App\Models\Competitions\CompetitionRestrictionGroup;
use Illuminate\Support\Facades\Request;

class CompetitionRestrictionController extends Controller {

	public function __construct() {
        $this->permission(['index', 'create', 'edit', 'delete', 'restore'], 'CompetitionAdmin');
	}

    public function getIndex() {
        $groups = CompetitionRestrictionGroup::with(['restrictions'])->get();
        return view('competitions/restriction/index', [
            'groups' => $groups
        ]);
   	}

    public function getCreate($group_id = null) {
        return view('competitions/restriction/create', [
            'group_id' => $group_id
        ]);
   	}

    public function postCreate() {
        $this->validate(Request::instance(), [
            'group_id' => 'required|numeric',
            'content_text' => 'required|max:1000'
        ]);
        CompetitionRestriction::Create([
            'group_id' => Request::input('group_id'),
            'content_text' => Request::input('content_text'),
            'content_html' => bbcode(Request::input('content_text'))
        ]);
        return redirect('competition-restriction/index');
    }

    public function getEdit($id) {
        $restriction = CompetitionRestriction::findOrFail($id);
        return view('competitions/restriction/edit', [
            'restriction' => $restriction
        ]);
    }

    public function postEdit() {
        $this->validate(Request::instance(), [
            'id' => 'required|numeric',
            'group_id' => 'required|numeric',
            'content_text' => 'required|max:1000'
        ]);
        $id = Request::input('id');
        $restriction = CompetitionRestriction::findOrFail($id);
        $restriction->update([
            'group_id' => Request::input('group_id'),
            'content_text' => Request::input('content_text'),
            'content_html' => bbcode(Request::input('content_text'))
        ]);
        return redirect('competition-restriction/index');
    }

    public function getDelete($id) {
        $restriction = CompetitionRestriction::findOrFail($id);
        return view('competitions/restriction/delete', [
            'restriction' => $restriction
        ]);
    }

    public function postDelete() {
        $id = Request::input('id');
        $restriction = CompetitionRestriction::findOrFail($id);
        $restriction->delete();
        return redirect('competition-restriction/index');
    }
}

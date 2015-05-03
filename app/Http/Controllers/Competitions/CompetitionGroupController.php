<?php namespace App\Http\Controllers\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competitions\CompetitionRestrictionGroup;
use Request;
use Input;
use Auth;
use DB;

class CompetitionGroupController extends Controller {

	public function __construct() {
        $this->permission(['index', 'create', 'edit', 'delete', 'restore'], 'CompetitionAdmin');
	}

    public function postCreate() {
        $this->validate(Request::instance(), [
            'title' => 'required|max:255'
        ]);
        CompetitionRestrictionGroup::Create([
            'title' => Request::input('title'),
            'is_multiple' => !!Request::input('is_multiple')
        ]);
        return redirect('competition-restriction/index');
   	}

    public function getEdit($id) {
        $group = CompetitionRestrictionGroup::findOrFail($id);
        return view('competitions/group/edit', [
            'group' => $group
        ]);
    }

    public function postEdit() {
        $this->validate(Request::instance(), [
            'id' => 'required|numeric',
            'title' => 'required|max:255'
        ]);
        $id = Request::input('id');
        $group = CompetitionRestrictionGroup::findOrFail($id);
        $group->update([
            'title' => Request::input('title'),
            'is_multiple' => !!Request::input('is_multiple')
        ]);
        return redirect('competition-restriction/index');
    }

    public function getDelete($id) {
        $group = CompetitionRestrictionGroup::findOrFail($id);
        return view('competitions/group/delete', [
            'group' => $group
        ]);
    }

    public function postDelete() {
        $id = Request::input('id');
        $group = CompetitionRestrictionGroup::findOrFail($id);
        $group->delete();
        return redirect('competition-restriction/index');
    }
}

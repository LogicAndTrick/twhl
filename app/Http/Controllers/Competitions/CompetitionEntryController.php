<?php namespace App\Http\Controllers\Competitions;

use App\Http\Controllers\Controller;
use Request;
use Input;
use Auth;
use DB;

class CompetitionEntryController extends Controller {

	public function __construct() {
        $this->permission(['create', 'edit', 'delete'], 'CompetitionEnter');
        $this->permission(['restore'], 'CompetitionAdmin');
	}

	public function getCreate() {
        return view('competitions/entry/create', [

        ]);
	}

    public function getEdit($id) {
        return view('competitions/entry/edit', [

        ]);
    }

    public function getDelete($id) {
        return view('competitions/entry/delete', [

        ]);
    }
    public function getRestore($id) {
        return view('competitions/entry/restore', [

        ]);
    }
}

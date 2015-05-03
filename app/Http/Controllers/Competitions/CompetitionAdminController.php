<?php namespace App\Http\Controllers\Competitions;

use App\Http\Controllers\Controller;
use Request;
use Input;
use Auth;
use DB;

class CompetitionAdminController extends Controller {

	public function __construct() {
        $this->permission(['create', 'edit', 'delete', 'restore'], 'CompetitionAdmin');
	}

	public function getCreate() {
        return view('competitions/admin/create', [

        ]);
	}

    public function getEdit($id) {
        return view('competitions/admin/edit', [

        ]);
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

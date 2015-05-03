<?php namespace App\Http\Controllers\Competitions;

use App\Http\Controllers\Controller;
use Request;
use Input;
use Auth;
use DB;

class CompetitionController extends Controller {

	public function __construct() {
        $this->permission(['create', 'edit', 'delete', 'restore'], 'CompetitionAdmin');
	}

	public function getIndex() {
        return view('competitions/competition/index', [

        ]);
	}

    public function getBrief($id) {
        return view('competitions/competition/brief', [

        ]);
    }

    public function getResults($id) {
        return view('competitions/competition/results', [

        ]);
    }

    public function getVote($id) {
        return view('competitions/competition/vote', [

        ]);
    }
}

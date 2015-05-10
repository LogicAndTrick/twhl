<?php namespace App\Http\Controllers\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Competitions\Competition;
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
        $comp = Competition::with(['engines', 'judges', 'restrictions', 'restrictions.group', 'status', 'type', 'judge_type'])->findOrFail($id);
        $rule_groups = [];
        foreach ($comp->restrictions as $r) {
            $t = $r->group->title;
            if (!array_key_exists($t, $rule_groups)) $rule_groups[$t] = [];
            $d = $r->content_html;
            $d = str_ireplace('{competition_id}', $comp->id, $d);
            $d = str_ireplace('{username}', Auth::user() ? Auth::user()->name : '[your_username]', $d);
            $rule_groups[$t][] = $d;
        }
        return view('competitions/competition/brief', [
            'comp' => $comp,
            'rule_groups' => $rule_groups
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

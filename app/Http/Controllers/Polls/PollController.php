<?php namespace App\Http\Controllers\Polls;

use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Comments\Comment;
use App\Models\Polls\Poll;
use App\Models\Polls\PollItem;
use App\Models\Polls\PollItemVote;
use Carbon\Carbon;
use Request;
use Input;
use Auth;
use DB;

class PollController extends Controller {

	public function __construct() {
        $this->permission(['vote'], true);
        $this->permission(['create', 'edit', 'delete', 'restore'], 'PollAdmin');
	}

	public function getIndex() {
        $polls = Poll::with(['items'])->orderBy('close_date', 'desc')->orderBy('id', 'desc')->paginate(5);
        $user_votes = [];
        $user_polls = [];
        if (Auth::user()) {
            $pids = $polls->map(function($x) { return $x->id; })->toArray();
            $uvotes = PollItemVote::whereIn('poll_id', $pids)->whereUserId(Auth::user()->id)->get();
            $user_votes = $uvotes->map(function($x) { return $x->item_id; })->toArray();
            $user_polls = $uvotes->map(function($x) { return $x->poll_id; })->toArray();
        }
        return view('polls/index', [
            'polls' => $polls,
            'user_votes' => $user_votes,
            'user_polls' => $user_polls
        ]);
	}

    public function getView($id) {
        $poll = Poll::with(['items'])->findOrFail($id);
        $comments = Comment::with(['comment_metas', 'user'])->whereArticleType(Comment::POLL)->whereArticleId($id)->get();
        $user_vote = null;
        if (Auth::user()) {
            $v = PollItemVote::wherePollId($id)->whereUserId(Auth::user()->id)->first();
            $user_vote = $v ? $v->item_id : null;
        }
        return view('polls/view', [
            'poll' => $poll,
            'comments' => $comments,
            'subscription' => Comment::getSubscription(Auth::user(), Comment::POLL, $id, true),
            'user_vote' => $user_vote,
            'user_votes' => [$user_vote]
        ]);
    }

    public function postVote() {
        $this->validate(Request::instance(), [
            'id' => 'required|numeric',
            'item_id' => 'required|numeric'
        ]);
        $poll = Poll::findOrFail(Request::input('id'));
        $item = PollItem::findOrFail(Request::input('item_id'));
        if ($item->poll_id != $poll->id || $poll->isClosed()) abort(404);

        DB::statement('DELETE FROM poll_item_votes WHERE poll_id = ? AND user_id = ?', [ $poll->id, Auth::user()->id ]);

        $vote = PollItemVote::Create([
            'poll_id' => $item->poll_id,
            'item_id' => $item->id,
            'user_id' => Auth::user()->id
        ]);
        return redirect('poll/view/'.$poll->id);
    }

    public function getCreate() {
        return view('polls/create', [

        ]);
    }

    public function postCreate() {
        $this->validate(Request::instance(), [
            'title' => 'required|max:100',
            'close_date' => 'required|date_format:d/m/Y',
            'content_text' => 'max:2000',
            'items' => 'required'
        ]);
        $poll = Poll::Create([
            'title' => Request::input('title'),
            'close_date' => Carbon::createFromFormat('d/m/Y', Request::input('close_date')),
            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text'))
        ]);
        $items = explode("\n", trim(Request::input('items')));
        foreach ($items as $i) {
            PollItem::Create([
                'poll_id' => $poll->id,
                'text' => trim($i),
                'stat_votes' => 0
            ]);
        }
        return redirect('poll/view/'.$poll->id);
    }

    public function getEdit($id) {
        $poll = Poll::with(['items'])->findOrFail($id);
        $items = implode("\n", $poll['items']->map(function($x) { return $x->text; })->toArray());
        return view('polls/edit', [
            'poll' => $poll,
            'items' => $items
        ]);
    }

    public function postEdit() {
        $this->validate(Request::instance(), [
            'id' => 'required|numeric',
            'title' => 'required|max:100',
            'close_date' => 'required|date_format:d/m/Y',
            'content_text' => 'max:2000',
            'items' => 'required'
        ]);
        $id = Request::input('id');
        $poll = Poll::with(['items'])->findOrFail($id);
        $poll->update([
            'title' => Request::input('title'),
            'close_date' => Carbon::createFromFormat('d/m/Y', Request::input('close_date')),
            'content_text' => Request::input('content_text'),
            'content_html' => app('bbcode')->Parse(Request::input('content_text'))
        ]);
        $items = explode("\n", trim(Request::input('items')));
        $loop = max(count($items), count($poll['items']));
        for ($k = 0; $k < $loop; $k++) {
            if ($k >= count($items)) $poll['items'][$k]->delete();
            else if ($k >= count($poll['items'])) PollItem::Create([ 'poll_id' => $poll->id, 'text' => trim($items[$k]), 'stat_votes' => 0 ]);
            else $poll['items'][$k]->update(['text' => trim($items[$k]) ]);
        }
        return redirect('poll/view/'.$poll->id);
    }

    public function getDelete($id) {
        $poll = Poll::with(['items'])->findOrFail($id);
        return view('polls/delete', [
            'poll' => $poll
        ]);
    }

    public function postDelete() {
        $id = Request::input('id');
        $poll = Poll::findOrFail($id);
        $poll->delete();
        return redirect('poll/index');
    }

    public function getRestore() {

    }

    public function postRestore() {

    }
}

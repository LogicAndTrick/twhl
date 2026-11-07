<?php

namespace App\Http\Controllers\Polls;

use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Comments\Comment;
use App\Models\Polls\Poll;
use App\Models\Polls\PollItem;
use App\Models\Polls\PollItemVote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[TypedRouting]
class PollController extends Controller
{

    public function __construct()
    {
        $this->anonymous(['index', 'view']);
        $this->permission(['vote'], true);
        $this->permission(['create', 'edit', 'delete', 'restore'], 'PollAdmin');
    }

    public function getIndex()
    {
        $polls = Poll::with(['items'])->orderBy('close_date', 'desc')->orderBy('id', 'desc')->paginate(5);
        $user_votes = [];
        $user_polls = [];
        if (Auth::user()) {
            $pids = $polls->map(function ($x) {
                return $x->id;
            })->toArray();
            $uvotes = PollItemVote::whereIn('poll_id', $pids)->whereUserId(Auth::user()->id)->get();
            $user_votes = $uvotes->map(function ($x) {
                return $x->item_id;
            })->toArray();
            $user_polls = $uvotes->map(function ($x) {
                return $x->poll_id;
            })->toArray();
        }
        return view('polls/index', [
            'polls' => $polls,
            'user_votes' => $user_votes,
            'user_polls' => $user_polls
        ]);
    }

    public function getView(int $id)
    {
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

    public function postVote(Request $request)
    {
        $this->validate($request, [
            'id' => ['required', 'integer'],
            'item_id' => ['required', 'integer']
        ]);
        $poll = Poll::findOrFail($request->integer('id'));
        $item = PollItem::findOrFail($request->integer('item_id'));
        if ($item->poll_id != $poll->id || $poll->isClosed()) abort(404);

        DB::statement('DELETE FROM poll_item_votes WHERE poll_id = ? AND user_id = ?', [$poll->id, Auth::user()->id]);

        $vote = PollItemVote::Create([
            'poll_id' => $item->poll_id,
            'item_id' => $item->id,
            'user_id' => Auth::user()->id
        ]);
        return redirect('poll/view/' . $poll->id);
    }

    public function getCreate()
    {
        return view('polls/create');
    }

    public function postCreate(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'max:100'],
            'close_date' => ['required', 'date_format:d/m/Y'],
            'content_text' => ['max:2000'],
            'items' => ['required']
        ]);
        $poll = Poll::Create([
            'title' => $request->input('title'),
            'close_date' => Carbon::createFromFormat('d/m/Y', $request->input('close_date')),
            'content_text' => $request->input('content_text'),
            'content_html' => bbcode($request->input('content_text'))
        ]);
        $items = explode("\n", trim($request->input('items')));
        foreach ($items as $i) {
            PollItem::Create([
                'poll_id' => $poll->id,
                'text' => trim($i),
                'stat_votes' => 0
            ]);
        }
        return redirect('poll/view/' . $poll->id);
    }

    public function getEdit(int $id)
    {
        $poll = Poll::with(['items'])->findOrFail($id);
        $items = implode("\n", $poll['items']->map(function ($x) {
            return $x->text;
        })->toArray());
        return view('polls/edit', [
            'poll' => $poll,
            'items' => $items
        ]);
    }

    public function postEdit(Request $request)
    {
        $this->validate($request, [
            'id' => ['required', 'integer'],
            'title' => ['required', 'max:100'],
            'close_date' => ['required', 'date_format:d/m/Y'],
            'content_text' => ['max:2000'],
            'items' => ['required']
        ]);
        $id = $request->integer('id');
        $poll = Poll::with(['items'])->findOrFail($id);
        $poll->update([
            'title' => $request->input('title'),
            'close_date' => Carbon::createFromFormat('d/m/Y', $request->input('close_date')),
            'content_text' => $request->input('content_text'),
            'content_html' => bbcode($request->input('content_text'))
        ]);
        $items = explode("\n", trim($request->input('items')));
        $loop = max(count($items), count($poll['items']));
        for ($k = 0; $k < $loop; $k++) {
            if ($k >= count($items)) $poll['items'][$k]->delete();
            else if ($k >= count($poll['items'])) PollItem::Create(['poll_id' => $poll->id, 'text' => trim($items[$k]), 'stat_votes' => 0]);
            else $poll['items'][$k]->update(['text' => trim($items[$k])]);
        }
        return redirect('poll/view/' . $poll->id);
    }

    public function getDelete(int $id)
    {
        $poll = Poll::with(['items'])->findOrFail($id);
        return view('polls/delete', [
            'poll' => $poll
        ]);
    }

    public function postDelete(Request $request)
    {
        $id = $request->integer('id');
        $poll = Poll::findOrFail($id);
        $poll->delete();
        return redirect('poll/index');
    }

    public function getRestore(int $id)
    {
        $poll = Poll::onlyTrashed()->findOrFail($id);
        return view('polls/restore', [
            'poll' => $poll
        ]);
    }

    public function postRestore(Request $request)
    {
        $id = $request->integer('id');
        $poll = Poll::onlyTrashed()->findOrFail($id);
        $poll->restore();
        return redirect('poll/view/' . $id);
    }
}

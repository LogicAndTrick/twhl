<?php

namespace App\Http\Controllers\Journals;

use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Accounts\User;
use App\Models\Accounts\UserSubscription;
use App\Models\Comments\Comment;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

#[TypedRouting]
class JournalController extends Controller
{
    public function __construct()
    {
        $this->anonymous(['index', 'view']);
        $this->permission(['create', 'edit', 'delete', 'restore'], 'JournalCreate');
    }

    public function getIndex(Request $request)
    {
        $query = Journal::with(['user'])->orderBy('created_at', 'desc');

        $user = $request->integer('user');
        $user = $user > 0 ? User::find($user) : null;
        if ($user) $query = $query->whereUserId($user->id);

        $journals = $query->paginate();
        return view('journal/index', [
            'journals' => $journals->appends($request->except('page')),
            'user' => $user
        ]);
    }

    public function getView(int $id)
    {
        $journal = Journal::findOrFail($id);
        $comments = Comment::with(['comment_metas', 'user'])->whereArticleType(Comment::JOURNAL)->whereArticleId($id)->get();
        return view('journal/view', [
            'journal' => $journal,
            'comments' => $comments,
            'subscription' => Comment::getSubscription(Auth::user(), Comment::JOURNAL, $id, true)
        ]);
    }

    public function getCreate()
    {
        return view('journal/create');
    }

    public function postCreate(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'max:150'],
            'text' => ['required', 'max:10000']
        ]);
        $journal = Journal::Create([
            'user_id' => Auth::user()->id,
            'title' => $request->input('title'),
            'content_text' => $request->input('text'),
            'content_html' => bbcode($request->input('text')),
            'stat_comments' => 0,
            'flag_locked' => false
        ]);
        $sub = UserSubscription::Create([
            'user_id' => Auth::user()->id,
            'article_type' => UserSubscription::JOURNAL,
            'article_id' => $journal->id,
            'send_email' => true,
            'send_push_notification' => false,
            'is_own_article' => true
        ]);
        return redirect('journal/view/' . $journal->id);
    }

    public function getEdit(int $id)
    {
        $journal = Journal::findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        return view('journal/edit', [
            'journal' => $journal
        ]);
    }

    public function postEdit(Request $request)
    {
        $id = $request->integer('id');
        $journal = Journal::findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        $this->validate($request, [
            'title' => ['required', 'max:150'],
            'text' => ['required', 'max:10000']
        ]);
        $journal->update([
            'title' => $request->input('title'),
            'content_text' => $request->input('text'),
            'content_html' => bbcode($request->input('text')),
        ]);
        return redirect('journal/view/' . $journal->id);
    }

    public function getDelete(int $id)
    {
        $journal = Journal::findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        return view('journal/delete', [
            'journal' => $journal
        ]);
    }

    public function postDelete(Request $request)
    {
        $id = $request->integer('id');
        $journal = Journal::findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        $journal->delete();
        return redirect('journal/index');
    }

    public function getRestore(int $id)
    {
        $journal = Journal::onlyTrashed()->findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        return view('journal/restore', [
            'journal' => $journal
        ]);
    }

    public function postRestore(Request $request)
    {
        $id = $request->integer('id');
        $journal = Journal::onlyTrashed()->findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        $journal->restore();
        return redirect('journal/view/' . $id);
    }
}

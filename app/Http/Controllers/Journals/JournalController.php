<?php namespace App\Http\Controllers\Journals;

use App\Http\Controllers\Controller;
use App\Models\Comments\Comment;
use App\Models\Journal;
use Request;
use Input;
use Auth;

class JournalController extends Controller {

	public function __construct()
	{
        $this->permission(['create', 'edit', 'delete', 'restore'], 'JournalCreate');
	}

	public function getIndex()
	{
        $journals = Journal::with(['user'])->orderBy('created_at', 'desc')->paginate();
		return view('journal/index', [
            'journals' => $journals
        ]);
	}

    public function getView($id)
    {
        $journal = Journal::findOrFail($id);
        $comments = Comment::with(['comment_metas', 'user'])->whereArticleType(Comment::JOURNAL)->whereArticleId($id)->get();
        return view('journal/view', [
            'journal' => $journal,
            'comments' => $comments
        ]);
    }

    public function getCreate() {
        return view('journal/create', [

        ]);
    }

    public function postCreate() {
        $this->validate(Request::instance(), [
            'text' => 'required|max:10000'
        ]);
        $journal = Journal::Create([
            'user_id' => Auth::user()->id,
            'content_text' => Request::input('text'),
            'content_html' => app('bbcode')->Parse(Request::input('text')),
            'stat_comments' => 0,
            'flag_locked' => false
        ]);
        return redirect('journal/view/'.$journal->id);
    }

    public function getEdit($id) {
        $journal = Journal::findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        return view('journal/edit', [
            'journal' => $journal
        ]);
    }

    public function postEdit() {
        $id = intval(Request::input('id'));
        $journal = Journal::findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        $this->validate(Request::instance(), [
            'text' => 'required|max:10000'
        ]);
        $journal->update([
            'content_text' => Request::input('text'),
            'content_html' => app('bbcode')->Parse(Request::input('text')),
        ]);
        return redirect('journal/view/'.$journal->id);
    }

    public function getDelete($id) {
        $journal = Journal::findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        return view('journal/delete', [
            'journal' => $journal
        ]);
    }

    public function postDelete() {
        $id = intval(Request::input('id'));
        $journal = Journal::findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        $journal->delete();
        return redirect('journal/index');
    }

    public function getRestore($id) {
        $journal = Journal::onlyTrashed()->findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        return view('journal/restore', [
            'journal' => $journal
        ]);
    }

    public function postRestore() {
        $id = intval(Request::input('id'));
        $journal = Journal::onlyTrashed()->findOrFail($id);
        if (!$journal->isEditable()) abort(404);
        $journal->restore();
        return redirect('journal/view/'.$id);
    }

}

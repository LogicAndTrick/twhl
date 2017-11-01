<?php namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Models\Comments\Comment;
use App\Models\News;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Request;
use Input;
use Auth;

class NewsController extends Controller {

	public function __construct()
	{
        $this->permission(['create', 'edit', 'delete', 'restore'], 'NewsAdmin');
	}

	public function getIndex()
	{
        $newses = News::with(['user'])->orderBy('created_at', 'desc')->paginate();
		return view('news/index', [
            'newses' => $newses
        ]);
	}

    public function getView($id)
    {
        $news = News::findOrFail($id);
        $comments = Comment::with(['comment_metas', 'user'])->whereArticleType(Comment::NEWS)->whereArticleId($id)->get();
        return view('news/view', [
            'news' => $news,
            'comments' => $comments,
            'subscription' => Comment::getSubscription(Auth::user(), Comment::NEWS, $id, true)
        ]);
    }

    // Administrative Tasks

    public function getCreate() {
        return view('news/create', [

        ]);
    }

    public function postCreate() {
        $this->validate(Request::instance(), [
            'title' => 'required|max:255',
            'text' => 'required|max:10000'
        ]);
        $news = News::Create([
            'user_id' => Auth::user()->id,
            'title' => Request::input('title'),
            'content_text' => Request::input('text'),
            'content_html' => app('bbcode')->Parse(Request::input('text')),
            'stat_comments' => 0,
            'flag_locked' => false
        ]);
        return redirect('news/view/'.$news->id);
    }

    public function getEdit($id) {
        $news = News::findOrFail($id);
        return view('news/edit', [
            'news' => $news
        ]);
    }

    public function postEdit() {
        $id = intval(Request::input('id'));
        $news = News::findOrFail($id);
        $this->validate(Request::instance(), [
            'title' => 'required|max:255',
            'text' => 'required|max:10000'
        ]);
        $news->update([
            'title' => Request::input('title'),
            'content_text' => Request::input('text'),
            'content_html' => app('bbcode')->Parse(Request::input('text')),
        ]);
        return redirect('news/view/'.$news->id);
    }

    public function getDelete($id) {
        $news = News::findOrFail($id);
        return view('news/delete', [
            'news' => $news
        ]);
    }

    public function postDelete() {
        $id = intval(Request::input('id'));
        $news = News::findOrFail($id);
        $news->delete();
        return redirect('news/index');
    }

    public function getRestore($id) {
        $news = News::onlyTrashed()->findOrFail($id);
        return view('news/restore', [
            'news' => $news
        ]);
    }

    public function postRestore() {
        $id = intval(Request::input('id'));
        $news = News::onlyTrashed()->findOrFail($id);
        $news->restore();
        return redirect('news/view/'.$id);
    }

}

<?php

namespace App\Http\Controllers\News;

use App\Helpers\TypedRouting;
use App\Http\Controllers\Controller;
use App\Models\Comments\Comment;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

#[TypedRouting]
class NewsController extends Controller
{
    public function __construct()
    {
        $this->anonymous(['index', 'view']);
        $this->permission(['create', 'edit', 'delete', 'restore'], 'NewsAdmin');
    }

    public function getIndex()
    {
        $newses = News::with(['user', 'reactions'])->orderBy('created_at', 'desc')->paginate();
        return view('news/index', [
            'newses' => $newses
        ]);
    }

    public function getView(int $id)
    {
        $news = News::query()->with(['reactions'])->findOrFail($id);
        $comments = Comment::with(['comment_metas', 'user'])->whereArticleType(Comment::NEWS)->whereArticleId($id)->get();
        return view('news/view', [
            'news' => $news,
            'comments' => $comments,
            'subscription' => Comment::getSubscription(Auth::user(), Comment::NEWS, $id, true)
        ]);
    }

    // Administrative Tasks

    public function getCreate()
    {
        return view('news/create');
    }

    public function postCreate(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'max:255'],
            'text' => ['required', 'max:10000']
        ]);
        $news = News::Create([
            'user_id' => Auth::user()->id,
            'title' => $request->input('title'),
            'content_text' => $request->input('text'),
            'content_html' => bbcode($request->input('text')),
            'stat_comments' => 0,
            'flag_locked' => false
        ]);
        return redirect('news/view/' . $news->id);
    }

    public function getEdit(int $id)
    {
        $news = News::findOrFail($id);
        return view('news/edit', [
            'news' => $news
        ]);
    }

    public function postEdit(Request $request)
    {
        $id = $request->integer('id');
        $news = News::findOrFail($id);
        $this->validate($request, [
            'title' => ['required', 'max:255'],
            'text' => ['required', 'max:10000']
        ]);
        $news->update([
            'title' => $request->input('title'),
            'content_text' => $request->input('text'),
            'content_html' => bbcode($request->input('text')),
        ]);
        return redirect('news/view/' . $news->id);
    }

    public function getDelete(int $id)
    {
        $news = News::findOrFail($id);
        return view('news/delete', [
            'news' => $news
        ]);
    }

    public function postDelete(Request $request)
    {
        $id = $request->integer('id');
        $news = News::findOrFail($id);
        $news->delete();
        return redirect('news/index');
    }

    public function getRestore(int $id)
    {
        $news = News::onlyTrashed()->findOrFail($id);
        return view('news/restore', [
            'news' => $news
        ]);
    }

    public function postRestore(Request $request)
    {
        $id = $request->integer('id');
        $news = News::onlyTrashed()->findOrFail($id);
        $news->restore();
        return redirect('news/view/' . $id);
    }
}

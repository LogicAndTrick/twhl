@extends('app')

@section('content')
    @include('wiki.nav', ['revision' => $revision])
    @if ($cat_name !== null)
        @include('wiki.view.category', ['object' => $object, 'revision' => $revision, 'cat_name' => $cat_name, 'cat_pages' => $cat_pages])
    @elseif ($object == null)
        @include('wiki.view.null', ['slug' => $slug])
    @elseif ($object->type_id == \App\Models\Wiki\WikiType::PAGE)
        @include('wiki.view.page', ['object' => $object, 'revision' => $revision])
    @elseif ($object->type_id == \App\Models\Wiki\WikiType::UPLOAD)
        @include('wiki.view.upload', ['object' => $object, 'revision' => $revision, 'upload' => $upload])
    @endif
    @if ($object != null && $object->permission_id == null)
        <hr />
        <div class="alert alert-warning">
            <strong>A note about comments on wiki pages</strong><br/>
            Wiki comments are used to discuss the content of the article, and they may be deleted by an admin if they are no longer relevant.<br/>
            If you have additional information about the topic, you should include that information on the page itself by editing it, not by posting a comment.<br/>
            If you have a question or are having trouble with a topic, please <a href="{{ act('forum', 'index') }}">post in the forums</a> instead of commenting here.
        </div>
        @include('comments.list', [ 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::WIKI, 'article_id' => $object->id ])
    @endif
@endsection
@extends('app')
<?php
    if ($revision) $meta_description = $revision->content_text;
?>

@section('content')
    @include('wiki.nav', ['revision' => $revision, 'cat_name' => $cat_name])
    @if ($cat_name !== null)
        @title('Wiki: Category: ' . str_replace('_', ' ', str_replace('+', ' > ', $cat_name)))
        @include('wiki.view.category', ['object' => $object, 'revision' => $revision, 'cat_name' => $cat_name, 'cat_pages' => $cat_pages])
    @elseif ($object == null)
        @title('Page Not Found')
        @include('wiki.view.null', ['slug' => $slug])
    @elseif ($object->type_id == \App\Models\Wiki\WikiType::PAGE)
        @title('Wiki: '.$revision->getNiceTitle($object))
        @include('wiki.view.page', ['object' => $object, 'revision' => $revision])
    @elseif ($object->type_id == \App\Models\Wiki\WikiType::UPLOAD)
        @title('Wiki: '.$revision->getNiceTitle($object))
        @include('wiki.view.upload', ['object' => $object, 'revision' => $revision, 'upload' => $upload])
    @endif
    @if ($object != null && $object->permission_id == null)
        @include('comments.list', [ 'article' => $object, 'comments' => $comments, 'article_type' => \App\Models\Comments\Comment::WIKI, 'article_id' => $object->id, 'inject_add' => ['wiki.comment-info' => []] ])
    @endif
@endsection
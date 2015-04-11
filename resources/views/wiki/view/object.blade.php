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
@endsection
@title('Special pages')
@extends('app')

@section('content')
    @include('wiki.nav')

    <h1>
        <span class="fa fa-star"></span>
        Links to and from pages
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('wiki', 'index') }}">Wiki</a></li>
        <li><a href="{{ url('/wiki-special') }}">Special pages</a></li>
        <li class="active">Links to and from pages</li>
    </ol>

    <p>
        Enter a page title here to see which other pages are linked to it.
    </p>

    <form action="{{ url('wiki-special/query-links') }}" method="get">
        <div class="input-group">
            <span class="input-group-text"><span class="fa fa-search"></span></span>
            <input type="text" class="form-control" name="title" placeholder="Enter page title" value="{{ $title }}">
            <button type="submit" class="btn btn-primary">Go</button>
        </div>
    </form>

    <div class="row">
        @if ($links_to)
            <div class="col-6">
                <h3>Pages that link to <a href="{{ url('wiki/page/' . \App\Models\Wiki\WikiRevision::CreateSlug($title)) }}">{{$title}}</a></h3>
                <ul>
                    @foreach($links_to as $link)
                        <li>
                            <a href="{{ url('wiki/page/' . \App\Models\Wiki\WikiRevision::CreateSlug($link->title)) }}">{{$link->title}}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if ($links_from)
            <div class="col-6">
                <h3>Pages that are linked by <a href="{{ url('wiki/page/' . \App\Models\Wiki\WikiRevision::CreateSlug($title)) }}">{{$title}}</a></h3>
                <ul>
                    @foreach($links_from as $link)
                        <li>
                            <a class="{{ $link->page_exists ? '' : 'text-danger' }}" href="{{ url('wiki/page/' . \App\Models\Wiki\WikiRevision::CreateSlug($link->title)) }}">{{$link->title}}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>


@endsection
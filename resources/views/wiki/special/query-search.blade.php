@title('Special pages')
@extends('app')

@section('content')
    @include('wiki.nav')

    <h1>
        <span class="fa fa-star"></span>
        Search content
    </h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('wiki', 'index') }}">Wiki</a></li>
        <li><a href="{{ url('/wiki-special') }}">Special pages</a></li>
        <li class="active">Search content</li>
    </ol>

    <p>
        Enter a text string to search pages for it.
        Text will be searched <strong>exactly</strong>, with no splitting on spaces or otherwise.
    </p>

    <form action="{{ url('wiki-special/query-search') }}" method="get">
        <div class="input-group">
            <span class="input-group-text"><span class="fa fa-search"></span></span>
            <input type="text" class="form-control" name="search" placeholder="Enter search text" value="{{ $search }}">
            <button type="submit" class="btn btn-primary">Go</button>
        </div>
    </form>

    <h3>Pages that contain this text</h3>
    <ul>
        @foreach($pages as $page)
            <li>
                <a href="{{ url('wiki/page/' . $page->slug) }}">{{$page->title}}</a>
                <?php
                    $pos = 0;
                    $search_length = mb_strlen($search);
                    while ($pos !== false) {
                        $idx = mb_stripos($page->content_text, $search, $pos);
                        if ($idx === false) break;

                        $content_length = mb_strlen($page->content_text);
                        $st = max(0, $idx - 50);
                        $en = min($content_length, $idx + $search_length + 50);
                        echo '<br>';
                        echo '<span class="text-muted">' . ($st > 0 ? '...' : '') . mb_substr($page->content_text, $st, $idx - $st) . '</span>';
                        echo '<strong>' . mb_substr($page->content_text, $idx, $search_length) . '</strong>';
                        echo '<span class="text-muted">' . mb_substr($page->content_text, $idx + $search_length, $en - ($idx + $search_length)) . ($en < $content_length ? '...' : '') . '</span>';

                        $pos = $idx + 1;
                    }
                ?>
            </li>
        @endforeach
    </ul>


@endsection
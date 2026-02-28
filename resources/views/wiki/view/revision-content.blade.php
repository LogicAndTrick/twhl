@if (!$revision->is_active)
    <div class="alert alert-warning">
        <span class="fa fa-exclamation-triangle"></span> You are viewing an older revision of this wiki page. The current revision may be more detailed and up-to-date.
        <a href="{{ act('wiki', 'page', $latest_revision->slug) }}">Click here</a> to see the current revision of this page.
    </div>
@endif

@foreach ($revision->wiki_revision_books as $book)
    @php
        $prev = $books->first(function ($b) use ($book) { return $b->page_number === 1 && $b->chapter_number === $book->chapter_number - 1; });
        $next = $books->first(function ($b) use ($book) { return $b->page_number === 1 && $b->chapter_number === $book->chapter_number + 1; });
        $pages = $books->filter(function ($b) use ($book) { return $b->chapter_number === $book->chapter_number; })->sortBy('page_number');
    @endphp
    <div class="wiki-book card">
        <div class="card-header">
            <h3 class="book-name">
                <span class="fa fa-book"></span> {{$book->book_name}}
            </h3>
        </div>
        <div class="card-body">
            <nav>
                <div class="previous">
                    @if ($prev)
                        <a href="{{ act('wiki', 'page', $prev->slug) }}"><span class="fa fa-chevron-left"></span> {{$prev->chapter_number}}: {{$prev->chapter_name}}</a>
                    @endif
                </div>
                <div class="current">
                    Chapter {{$book->chapter_number}}: {{$book->chapter_name}}
                </div>
                <div class="next">
                    @if ($next)
                        <a href="{{ act('wiki', 'page', $next->slug) }}">{{$next->chapter_number}}: {{$next->chapter_name}} <span class="fa fa-chevron-right"></span></a>
                    @endif
                </div>
            </nav>
            <ul>
                @foreach ($pages as $page)
                    @if ($page->slug === $revision->slug)
                        <li class="current">{{$page->title}}</li>
                    @else
                        <li><a href="{{ act('wiki', 'page', $page->slug) }}">{{$page->title}}</a></li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
@endforeach

<div class="wiki bbcode {{$revision->user->getClasses()}}">
    {!! $revision->content_html !!}
</div>
@if ($revision && $revision->hasCategories())
    <ul class="wiki-categories">
        <li class="header">Categories</li>
        @foreach ($revision->getCategories() as $cat)
            <li><a href="{{ act('wiki', 'page', 'category:'.$cat) }}">{{ $cat }}</a></li>
        @endforeach
    </ul>
@endif
@extends('app')

@section('content')
    <div>
        <a href="{{ act('vault', 'create') }}">Upload to the Vault</a>
    </div>

    <form method="get" action="{{ act('vault', 'index') }}">
        <input type="hidden" data-filter="filter-games" name="games" value="{{ Request::get('games') }}"/>
        <input type="hidden" data-filter="filter-categories" name="cats" value="{{ Request::get('cats') }}"/>
        <input type="hidden" data-filter="filter-includes" name="incs" value="{{ Request::get('incs') }}"/>
        <input type="hidden" data-filter="filter-rating" name="rate" value="{{ Request::get('rate') }}"/>
        <input type="hidden" data-filter="filter-sort" name="sort" value="{{ Request::get('sort') }}"/>
        <div class="input-group">
            <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
            <input name="search" type="text" value="{{ Request::get('search') }}" placeholder="Type here to search" class="form-control">
            <div class="input-group-btn">
                <div class="btn-group">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info"><span class="glyphicon glyphicon-refresh"></span> Games</span> <span class="caret"></span></button>
                    <ul class="dropdown-menu vault-filter filter-games">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info"><span class="glyphicon glyphicon-refresh"></span> Categories</span> <span class="caret"></span></button>
                    <ul class="dropdown-menu vault-filter filter-categories">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info"><span class="glyphicon glyphicon-refresh"></span> Includes</span> <span class="caret"></span></button>
                    <ul class="dropdown-menu vault-filter filter-includes">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info">Min. Rating: Any</span> <span class="caret"></span></button>
                    <ul class="dropdown-menu vault-filter filter-one filter-rating">
                        <li data-filter-value="5">5</li>
                        <li data-filter-value="4.5">4.5</li>
                        <li data-filter-value="4">4</li>
                        <li data-filter-value="3.5">3.5</li>
                        <li data-filter-value="3">3</li>
                        <li data-filter-value="2.5">2.5</li>
                        <li data-filter-value="2">2</li>
                        <li data-filter-value="1.5">1.5</li>
                        <li data-filter-value="1">1</li>
                        <li data-filter-value="0.5">0.5</li>
                        <li data-filter-value="0">0</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info">Sort: Date</span> <span class="caret"></span></button>
                    <ul class="dropdown-menu vault-filter filter-one filter-sort">
                        <li data-filter-value="date">Date</li>
                        <li data-filter-value="rating">Rating</li>
                        <li data-filter-value="num_ratings">Number of Ratings</li>
                        <li data-filter-value="num_views">Number of Views</li>
                        <li data-filter-value="num_downloads">Number of Downloads</li>
                    </ul>
                </div>
                <button type="submit" class="btn btn-info">
                    Search
                </button>
            </div>
        </div>
    </form>

    <ul>
        @foreach ($items as $item)
            <li>
                <a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a><br/>
                <span>{{ $item->game->name }}</span><br/>
                @if ($item->hasPrimaryScreenshot())
                    <img src="{{ asset('uploads/vault/'.$item->getPrimaryScreenshot()->image_small) }}" alt="{{ $item->name }}" />
                @else
                    <img src="{{ asset('images/image-not-found.png') }}" alt="{{ $item->name }}" />
                @endif
                <br/>
                By: {{ $item->user->name }}<br/>
                {{ Date::TimeAgo( $item->created_at ) }}
            </li>
        @endforeach
    </ul>

    {!! $items->render() !!}
@endsection

@section('scripts')
    <script type="text/javascript">

        function populate_filter(cls, items, templ, obj) {
            var el = $('.' + cls).empty();
            obj = obj || {};
            for (var i = 0; i < items.length; i++) {
                var data = $.extend(obj, items[i]);
                var html = template(templ, data);
                el.append(html);
            }
            el.append('<li class="clear-filter"><span class="glyphicon glyphicon-remove"></span> Clear Filter</li>');
            var selected = ($('[data-filter=' + cls + ']').val() || '').split('-');
            for (var j = 0; j < selected.length; j++) {
                if (!selected[j].length) continue;
                el.find('[data-filter-value=' + selected[j] + ']').addClass('active');
            }
            if (selected.length) update_filters(cls);
        }

        function update_filters(cls) {
            var classes = ['filter-games', 'filter-categories', 'filter-includes', 'filter-rating', 'filter-sort'];
            var zero = ['All Games', 'All Categories', 'Any Includes', 'Min. Rating: Any', 'Sort: Date'];
            var one = ['{text}', '{text}', '{text}', 'Min. Rating: {text}', 'Sort: {text}'];
            var many = ['{count} Games', '{count} Categories', '{count} Includes', 'Min. Rating: {text}', 'Sort: {text}'];

            for (var i = 0; i < classes.length; i++) {
                if (cls && cls != classes[i]) continue;
                var active = $('.' + classes[i] + ' .active');
                var count = active.length, text = (active.text() || '').trim();
                var templ = count == 0 ? zero[i] : count == 1 ? one[i] : many[i];
                $('.' + classes[i]).siblings('button').find('.filter-info').text(template(templ, { text: text, count: count }));
                $('[data-filter=' + classes[i] + ']').val(active.map(function () { return $(this).data('filter-value'); }).toArray().join('-'));
            }
        }

        $(function() {

            $('.vault-filter').on('click', 'li:not(.loading)', function() {
                var clr = $(this).is('.clear-filter');
                var par = $(this).closest('.vault-filter');
                if (par.is('.filter-one') || clr) {
                    par.find('.active').removeClass('active');
                }
                if (!clr) $(this).toggleClass('active');
                update_filters();
            });

            // Load dynamic content

            $.get('{{ url("api/games") }}', { all: true }, function(data) {
                var templ = '<li class="stop-close" data-filter-value="{id}"><img src="{{ asset("images/games/{abbreviation}_{size}.png") }}" alt="{name}" /> {name}</li>';
                populate_filter('filter-games', data, templ, { size: 32 });
            });
            $.get('{{ url("api/vault-categories") }}', { all: true }, function(data) {
                populate_filter('filter-categories', data, '<li class="stop-close" data-filter-value="{id}">{name}</li>');
            });
            $.get('{{ url("api/vault-includes") }}', { all: true }, function(data) {
                populate_filter('filter-includes', data, '<li class="stop-close" data-filter-value="{id}">{name}</li>');
            });

            // Update static content

            var sel_rating = $('[data-filter=filter-rating]').val();
            if (sel_rating) {
                $('.filter-rating [data-filter-value="' + sel_rating + '"]').addClass('active');
                update_filters('filter-rating');
            }
            var sel_sort = $('[data-filter=filter-sort]').val();
            if (sel_sort) {
                $('.filter-sort [data-filter-value="' + sel_sort + '"]').addClass('active');
                update_filters('filter-sort');
            }
        });
    </script>
@endsection
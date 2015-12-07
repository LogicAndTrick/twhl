@extends('app')

@section('content')
    <hc>
        <a class="btn btn-primary btn-xs" href="{{ act('vault', 'create') }}"><span class="glyphicon glyphicon-plus"></span> Upload to the Vault</a>
        <h1>Vault Items</h1>
        <ol class="breadcrumb">
            @if ($filtering)
                <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
                <li class="active">Filter Vault Items</li>
            @else
                <li class="active">Vault</li>
            @endif
        </ol>
        {!! $items->render() !!}
    </hc>

    <form method="get" action="{{ act('vault', 'index') }}">
        <input type="hidden" data-filter="filter-games" name="games" value="{{ Request::get('games') }}"/>
        <input type="hidden" data-filter="filter-categories" name="cats" value="{{ Request::get('cats') }}"/>
        <input type="hidden" data-filter="filter-types" name="types" value="{{ Request::get('types') }}"/>
        <input type="hidden" data-filter="filter-includes" name="incs" value="{{ Request::get('incs') }}"/>
        <input type="hidden" data-filter="filter-rating" name="rate" value="{{ Request::get('rate') }}"/>
        <input type="hidden" data-filter="filter-sort" name="sort" value="{{ Request::get('sort') }}"/>
        <div class="input-group">
            <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
            <input name="search" type="text" value="{{ Request::get('search') }}" placeholder="Type here to search" class="form-control">
            <div class="input-group-btn vault-filter-container">
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
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info"><span class="glyphicon glyphicon-refresh"></span> Types</span> <span class="caret"></span></button>
                    <ul class="dropdown-menu vault-filter filter-types">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info"><span class="glyphicon glyphicon-refresh"></span> Includes</span> <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right vault-filter filter-includes">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info">Min. Rating: Any</span> <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right vault-filter filter-one filter-rating">
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
                    <ul class="dropdown-menu pull-right vault-filter filter-one filter-sort">
                        <li data-filter-value="date">Date</li>
                        <li data-filter-value="rating">Rating</li>
                        <li data-filter-value="num_ratings" data-text="Ratings">Number of Ratings</li>
                        <li data-filter-value="num_views" data-text="Views">Number of Views</li>
                        <li data-filter-value="num_downloads" data-text="Downloads">Number of Downloads</li>
                    </ul>
                </div>
                <button type="submit" class="btn btn-info">
                    Search
                </button>
            </div>
        </div>
    </form>

    <ul class="row vault-list">
        @foreach ($items as $item)
            <li class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                <div class="vault-item">
                    <img class="game-icon" src="{{ asset('images/games/' . $item->game->abbreviation . '_32.png') }}" alt="{{ $item->game->name }}" title="{{ $item->game->name }}" />
                    <a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a>
                    <a class="screenshot" href="{{ act('vault', 'view', $item->id) }}">
                        <img src="{{ asset($item->getThumbnailAsset()) }}" alt="{{ $item->name }}" />
                    </a>
                    @if ($item->flag_ratings && $item->stat_ratings > 0)
                        <span class="stars">
                            @foreach ($item->getRatingStars() as $star)
                                <img src="{{ asset('images/stars/gold_'.$star.'_16.png') }}" alt="{{ $star }} star" />
                            @endforeach
                            ({{$item->stat_ratings}})
                        </span>
                    @elseif ($item->flag_ratings)
                        No Ratings Yet
                    @else
                        Ratings Disabled
                    @endif
                    <br/>
                    By @avatar($item->user inline)<br/>
                    @date($item->created_at)
                </div>
            </li>
        @endforeach
    </ul>
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
            var classes = ['filter-games', 'filter-categories', 'filter-types', 'filter-includes', 'filter-rating', 'filter-sort'];
            var zero = ['All Games', 'All Categories', 'All Types', 'Any Includes', 'Min. Rating: Any', 'Sort: Date'];
            var one = ['{text}', '{text}', '{text}', '{text}', 'Min. Rating: {text}', 'Sort: {text}'];
            var many = ['{count} Games', '{count} Categories', '{count} Types', '{count} Includes', 'Min. Rating: {text}', 'Sort: {text}'];

            for (var i = 0; i < classes.length; i++) {
                if (cls && cls != classes[i]) continue;
                var active = $('.' + classes[i] + ' .active');
                var count = active.length, text = (active.text() || '').trim();
                var templ = count == 0 ? zero[i] : count == 1 ? one[i] : many[i];
                $('.' + classes[i]).siblings('button').find('.filter-info').text(template(templ, $.extend({ text: text, count: count }, active.data())));
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

            $('.filter-games').parent().on('show.bs.dropdown', function() {
                var s = $('[name=search]'),
                    fg = $('.filter-games'),
                    bt = fg.siblings('[data-toggle="dropdown"]'),
                    w = s.outerWidth() + s.siblings('.input-group-addon').outerWidth(),
                    cw = fg.outerWidth(),
                    bw = bt.outerWidth(),
                    max = cw - bw;
                fg.css({
                    left: -Math.min(max, w)
                })
            });

            // Load dynamic content

            $.get('{{ url("api/games") }}', { all: true }, function(data) {
                var templ = '<li class="stop-close" data-filter-value="{id}" data-text="{abbreviation}"><img src="{{ asset("images/games/{abbreviation}_{size}.png") }}" alt="{name}" /> {name}</li>';
                populate_filter('filter-games', data, templ, { size: 32 });
            });
            $.get('{{ url("api/vault-categories") }}', { all: true }, function(data) {
                populate_filter('filter-categories', data, '<li class="stop-close" data-filter-value="{id}">{name}</li>');
            });
            $.get('{{ url("api/vault-types") }}', { all: true }, function(data) {
                populate_filter('filter-types', data, '<li class="stop-close" data-filter-value="{id}">{name}</li>');
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
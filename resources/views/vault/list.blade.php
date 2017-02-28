@title('Vault items')
@extends('app')

@section('content')

    <h1>
        <span class="fa fa-database"></span>
        Vault items
        @if (permission('VaultCreate'))
            <a class="btn btn-primary btn-xs" href="{{ act('vault', 'create') }}"><span class="fa fa-plus"></span> Upload to the Vault</a>
        @endif
    </h1>

    @if ($filtering)
        <ol class="breadcrumb">
            <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
            <li class="active">Filter Vault Items</li>
        </ol>
    @endif

    {!! $items->render() !!}

    <form method="get" action="{{ act('vault', 'index') }}" class="vault-filter-form">
        <input type="hidden" data-filter="filter-games" name="games" value="{{ Request::get('games') }}"/>
        <input type="hidden" data-filter="filter-categories" name="cats" value="{{ Request::get('cats') }}"/>
        <input type="hidden" data-filter="filter-types" name="types" value="{{ Request::get('types') }}"/>
        <input type="hidden" data-filter="filter-users" name="users" value="{{ Request::get('users') }}"/>
        <input type="hidden" data-filter="filter-includes" name="incs" value="{{ Request::get('incs') }}"/>
        <input type="hidden" data-filter="filter-rating" name="rate" value="{{ Request::get('rate') }}"/>
        <input type="hidden" data-filter="filter-sort" name="sort" value="{{ Request::get('sort') }}"/>
        <div class="input-group">
            <span class="input-group-addon"><span class="fa fa-search"></span></span>
            <input name="search" type="text" value="{{ Request::get('search') }}" placeholder="Type here to search" class="form-control">
            <div class="input-group-btn vault-filter-container">
                <div class="btn-group">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info"><span class="fa fa-refresh"></span> Games</span></button>
                    <ul class="dropdown-menu vault-filter filter-games">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info"><span class="fa fa-refresh"></span> Categories</span></button>
                    <ul class="dropdown-menu vault-filter filter-categories">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info"><span class="fa fa-refresh"></span> Types</span></button>
                    <ul class="dropdown-menu vault-filter filter-types">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info"><span class="fa fa-refresh"></span> Users</span></button>
                    <ul class="dropdown-menu vault-filter remove-item filter-users">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info"><span class="fa fa-refresh"></span> Includes</span></button>
                    <ul class="dropdown-menu pull-right vault-filter filter-includes">
                        <li class="loading">Loading...</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info">Min. Rating: Any</span></button>
                    <ul class="dropdown-menu pull-right vault-filter filter-one filter-rating">
                        <li class="filter-action" data-filter-value="5">5</li>
                        <li class="filter-action" data-filter-value="4.5">4.5</li>
                        <li class="filter-action" data-filter-value="4">4</li>
                        <li class="filter-action" data-filter-value="3.5">3.5</li>
                        <li class="filter-action" data-filter-value="3">3</li>
                        <li class="filter-action" data-filter-value="2.5">2.5</li>
                        <li class="filter-action" data-filter-value="2">2</li>
                        <li class="filter-action" data-filter-value="1.5">1.5</li>
                        <li class="filter-action" data-filter-value="1">1</li>
                        <li class="filter-action" data-filter-value="0.5">0.5</li>
                        <li class="filter-action" data-filter-value="0">0</li>
                    </ul>
                </div>
                <div class="btn-group">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"><span class="filter-info">Sort: Created</span></button>
                    <ul class="dropdown-menu pull-right vault-filter filter-one filter-sort">
                        <li class="filter-action" data-filter-value="date" data-text="Created">Date Created</li>
                        <li class="filter-action" data-filter-value="update" data-text="Updated">Date Updated</li>
                        <li class="filter-action" data-filter-value="rating">Rating</li>
                        <li class="filter-action" data-filter-value="num_ratings" data-text="Ratings">Number of Ratings</li>
                        <li class="filter-action" data-filter-value="num_views" data-text="Views">Number of Views</li>
                        <li class="filter-action" data-filter-value="num_downloads" data-text="Downloads">Number of Downloads</li>
                    </ul>
                </div>
                <button type="submit" class="btn btn-info">
                    Search
                </button>
            </div>
        </div>
    </form>

    <div class="row vault-list">
        @foreach ($items as $item)
            <div class="col col-md-6 col-lg-4 d-flex">
                <a href="{{ act('vault', 'view', $item->id) }}" class="tile vault-item">
                    <span class="tile-heading">
                        <img class="game-icon" src="{{ $item->game->getIconUrl() }}" alt="{{ $item->game->name }}" title="{{ $item->game->name }}" />
                        <span title="{{ $item->name }}">{{ $item->name }}</span>
                    </span>
                    <span class="tile-main">
                        <img alt="{{ $item->name }}" src="{{ asset($item->getMediumAsset()) }}">
                    </span>
                    <span class="tile-title">By @avatar($item->user inline link=false)</span>
                    <span class="tile-subtitle">
                        @date($item->created_at) &bull;
                        <span class="stars">
                            @if ($item->flag_ratings && $item->stat_ratings > 0)
                                @foreach ($item->getRatingStars() as $star)
                                    <img src="{{ asset('images/stars/rating_'.$star.'.svg') }}" alt="{{ $star }} star" />
                                @endforeach
                                ({{$item->stat_ratings}})
                            @elseif ($item->flag_ratings)
                                No Ratings Yet
                            @else
                                Ratings Disabled
                            @endif
                        </span>
                    </span>
                </a>
            </div>
            <div class="w-100 hidden-md-up"></div>
        @endforeach
    </div>
    <div class="footer-container">
        {!! $items->render() !!}
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">

        function populate_filter(cls, items, templ, obj, append) {
            var el = $('.' + cls).empty();
            obj = obj || {};
            for (var i = 0; i < items.length; i++) {
                var data = $.extend(obj, items[i]);
                var html = template(templ, data);
                el.append(html);
            }
            el.append('<li class="clear-filter static-control filter-action"><span class="fa fa-remove"></span> Clear Filter</li>');
            if (append) el.append(append);
            var selected = ($('[data-filter=' + cls + ']').val() || '').split('-');
            for (var j = 0; j < selected.length; j++) {
                if (!selected[j].length) continue;
                el.find('[data-filter-value=' + selected[j] + ']').addClass('active');
            }
            if (selected.length) update_filters(cls);
        }

        function update_filters(cls) {
            var classes = ['filter-games', 'filter-categories', 'filter-types', 'filter-users', 'filter-includes', 'filter-rating', 'filter-sort'];
            var zero = ['All Games', 'All Categories', 'All Types', 'All Users', 'Any Includes', 'Min. Rating: Any', 'Sort: Date'];
            var one = ['{text}', '{text}', '{text}', '<img src="{avatar}" alt="avatar" /> {text}', '{text}', 'Min. Rating: {text}', 'Sort: {text}'];
            var many = ['{count} Games', '{count} Categories', '{count} Types', '{count} Users', '{count} Includes', 'Min. Rating: {text}', 'Sort: {text}'];

            for (var i = 0; i < classes.length; i++) {
                if (cls && cls != classes[i]) continue;
                var active = $('.' + classes[i] + ' .active');
                var count = active.length, text = (active.text() || '').trim();
                var templ = count == 0 ? zero[i] : count == 1 ? one[i] : many[i];
                $('.' + classes[i]).siblings('button').find('.filter-info').html(template(templ, $.extend({ text: text, count: count }, active.data())));
                $('[data-filter=' + classes[i] + ']').val(active.map(function () { return $(this).data('filter-value'); }).toArray().join('-'));
            }
        }

        $(function() {

            var timeout = null;

            $('.vault-filter').on('click', '.filter-action', function() {
                var clr = $(this).is('.clear-filter');
                var par = $(this).closest('.vault-filter');
                if (par.is('.filter-one') || clr) {
                    par.find('.active').removeClass('active');
                }
                if (par.is('.remove-item') && clr) par.find('li:not(.static-control)').remove();
                else if (par.is('.remove-item')) $(this).closest('li').remove();
                else if (!clr) $(this).toggleClass('active');
                update_filters();
            }).on('keyup', '.user-search input', function() {
                var $t = $(this);
                $t.siblings('.search-results').html('<li class="loading">Searching...</li>');
                if (timeout) clearTimeout(timeout);
                timeout = setTimeout(function() {
                    clearTimeout(timeout);
                    $.get('{{ url("api/users") }}', { filter: $t.val() }, function(data) {
                        $t.siblings('.search-results').empty().append(data.map(function(u) {
                            return template('<li class="stop-close user-item" data-filter-value="{id}" data-avatar="{avatar_inline}"><img src="{avatar_inline}" alt="avatar" /> {name} <span class="fa fa-remove filter-action"></span></li>', u);
                        }));
                    });
                }, 500);
            }).on('click', '.user-search .user-item', function() {
                if ($('.filter-users > [data-filter-value="' + $(this).data('filter-value') + '"]').length) return;
                $(this).clone().addClass('active').insertBefore('.filter-users .clear-filter');
                update_filters('filter-users');
                $('.filter-users .user-search input').val('').focus();
            });

            $('.filter-users').parent().on('shown.bs.dropdown', function() {
                $(this).find('input').val('').focus();
            });

            $('.filter-games').parent().on('show.bs.dropdown', function() {
                var s = $('.vault-filter-form [name=search]'),
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

            $.get('{{ url("api/games") }}', { count: 100 }, function(data) {
                var templ = '<li class="stop-close filter-action" data-filter-value="{id}" data-text="{abbreviation}"><img src="{{ asset("images/games/{abbreviation}_{size}.svg") }}" alt="{name}" /> {name}</li>';
                populate_filter('filter-games', data, templ, { size: 32 });
            });
            $.get('{{ url("api/vault-categories") }}', { count: 100 }, function(data) {
                populate_filter('filter-categories', data, '<li class="stop-close filter-action" data-filter-value="{id}">{name}</li>');
            });
            $.get('{{ url("api/vault-types") }}', { count: 100 }, function(data) {
                populate_filter('filter-types', data, '<li class="stop-close filter-action" data-filter-value="{id}">{name}</li>');
            });
            $.get('{{ url("api/vault-includes") }}', { count: 100 }, function(data) {
                populate_filter('filter-includes', data, '<li class="stop-close filter-action" data-filter-value="{id}">{name}</li>');
            });

            var user_search_template = '<li class="static-control search-form user-search stop-close"><input class="form-control w-100" type="text" placeholder="Search users..." /><ul class="search-results"></ul></li>';
            var users = ($('[name=users]').val() || '').split('-').join(',');
            if (users) {
                $.get('{{ url("api/users") }}', { id: users, count: 100 }, function(data) {
                    populate_filter(
                        'filter-users', data,
                        '<li class="stop-close user-item" data-filter-value="{id}" data-avatar="{avatar_inline}"><img src="{avatar_inline}" alt="avatar" /> {name} <span class="fa fa-remove filter-action"></span></li>',
                        null,
                        user_search_template
                    );
                });
            } else {
                populate_filter('filter-users', [], '', null, user_search_template);
            }

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
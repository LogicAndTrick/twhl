@title('Vault items')
@extends('app')

@section('content')

    <h1>
        <span class="fa fa-database"></span>
        Vault items
        @if (permission('VaultCreate') && !$items->hasPages())
            <a class="btn btn-outline-primary btn-xs" href="{{ act('vault', 'create') }}"><span class="fa fa-plus"></span> Upload to the Vault</a>
        @endif
    </h1>

    <div class="d-flex flex-row">
        @if ($filtering)
            <ol class="breadcrumb">
                <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
                <li class="active">Filter Vault Items</li>
            </ol>
        @endif

        @if (permission('VaultCreate') && $items->hasPages())
            <a class="btn btn-primary ms-auto" href="{{ act('vault', 'create') }}"><span class="fa fa-plus"></span> Upload to the Vault</a>
        @endif
    </div>

    {!! $items->render() !!}

    <form method="get" action="{{ act('vault', 'index') }}" class="vault-filter-form">
        <input type="hidden" data-filter="filter-games" name="games" value="{{ Request::get('games') }}"/>
        <input type="hidden" data-filter="filter-categories" name="cats" value="{{ Request::get('cats') }}"/>
        <input type="hidden" data-filter="filter-types" name="types" value="{{ Request::get('types') }}"/>
        <input type="hidden" data-filter="filter-users" name="users" value="{{ Request::get('users') }}"/>
        <input type="hidden" data-filter="filter-includes" name="incs" value="{{ Request::get('incs') }}"/>
        <input type="hidden" data-filter="filter-rating" name="rate" value="{{ Request::get('rate') }}"/>
        <input type="hidden" data-filter="filter-sort" name="sort" value="{{ Request::get('sort') }}"/>
        <div class="input-group vault-filter-container">
            <span class="input-group-text"><span class="fa fa-search"></span></span>
            <input name="search" type="text" value="{{ Request::get('search') }}" placeholder="Type here to search" class="form-control">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"><span class="filter-info"><span class="fa fa-refresh"></span> Games</span></button>
            <ul class="dropdown-menu vault-filter filter-games">
                <li class="loading">Loading...</li>
            </ul>
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"><span class="filter-info"><span class="fa fa-refresh"></span> Categories</span></button>
            <ul class="dropdown-menu vault-filter filter-categories">
                <li class="loading">Loading...</li>
            </ul>
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"><span class="filter-info"><span class="fa fa-refresh"></span> Types</span></button>
            <ul class="dropdown-menu vault-filter filter-types">
                <li class="loading">Loading...</li>
            </ul>
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"><span class="filter-info"><span class="fa fa-refresh"></span> Users</span></button>
            <ul class="dropdown-menu vault-filter remove-item filter-users">
                <li class="loading">Loading...</li>
            </ul>
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"><span class="filter-info"><span class="fa fa-refresh"></span> Includes</span></button>
            <ul class="dropdown-menu pull-right vault-filter filter-includes">
                <li class="loading">Loading...</li>
            </ul>
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"><span class="filter-info">Min. Rating: Any</span></button>
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
                <li class="clear-filter static-control filter-action"><span class="fa fa-remove"></span> Clear Filter</li>
            </ul>
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"><span class="filter-info">Sort: Created</span></button>
            <ul class="dropdown-menu pull-right vault-filter filter-one filter-sort">
                <li class="filter-action" data-filter-value="date" data-text="Created">Date Created</li>
                <li class="filter-action" data-filter-value="update" data-text="Updated">Date Updated</li>
                <li class="filter-action" data-filter-value="rating">Rating</li>
                <li class="filter-action" data-filter-value="num_ratings" data-text="Ratings">Number of Ratings</li>
                <li class="filter-action" data-filter-value="num_views" data-text="Views">Number of Views</li>
                <li class="filter-action" data-filter-value="num_downloads" data-text="Downloads">Number of Downloads</li>
            </ul>
            <button type="submit" class="btn btn-info">
                Search
            </button>
        </div>
    </form>

    <div class="row vault-list">
        @foreach ($items as $item)
            <div class="col col-md-6 col-lg-4 d-flex">
                <a href="{{ act('vault', 'view', $item->id) }}" class="tile vault-item {{ $item->link_broken ? 'broken-download' : '' }}">
                    <span class="tile-heading">
                        <img class="game-icon" src="{{ $item->game->getIconUrl() }}" alt="{{ $item->game->name }}" title="{{ $item->game->name }}" />
                        <span title="{{ $item->name }}">{{ $item->name }}</span>
                    </span>
                    <span class="tile-main">
                        <img alt="{{ $item->name }}" src="{{ asset($item->getMediumAsset()) }}">
                    </span>
                    <span class="tile-title">{{ $item->vault_category->adjective }} {{ strtolower($item->vault_type->name) }} by @avatar($item->user inline link=false)</span>
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
                        @if ($item->link_broken)
                            &bull;
                            <span class="broken-download-warning">
                                <span class="fa fa-chain-broken broken-download-icon"></span>
                                Broken Download
                            </span>
                        @endif
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

        const templates = {
            'item:filter-games': '<li class="filter-action" data-filter-value="{id}" data-text="{abbreviation}"><img src="{{ asset("images/games/{abbreviation}_{size}.svg") }}" alt="{name}" /> {name}</li>',
            'item:filter-categories': '<li class="filter-action" data-filter-value="{id}">{name}</li>',
            'item:filter-types': '<li class="filter-action" data-filter-value="{id}">{name}</li>',
            'item:filter-includes': '<li class="filter-action" data-filter-value="{id}">{name}</li>',
            'item:filter-users': '<li class="user-item" data-filter-value="{id}" data-avatar="{avatar_inline}"><img src="{avatar_inline}" alt="avatar" /> {name} <span class="fa fa-remove filter-action"></span></li>',
            'append:filter-users': '<li class="static-control search-form user-search"><input class="form-control w-100" type="text" placeholder="Search users..." /><ul class="search-results"></ul></li>'
        };

        function populate_filter(cls, items, templ, obj, append) {
            const el = document.querySelector(`.${cls}`);
            if (!el) return;

            obj = obj || {};
            el.replaceChildren(...items.map(x => nanoTemplateHtml(templ, {...obj, ...x})));
            el.append(nanoTemplateHtml('<li class="clear-filter static-control filter-action"><span class="fa fa-remove"></span> Clear Filter</li>'));
            if (append) el.append(nanoTemplateHtml(append));

            const input = document.querySelector(`[data-filter=${cls}]`);
            const selected = (input.value || '').split('-');
            for (const sel of selected) {
                if (!sel.length) continue;
                const item = el.querySelector(`[data-filter-value="${sel}"]`);
                if (item) item.classList.add('active');
            }
            if (selected.length) update_filters(cls);
        }

        function update_filters(cls) {
            const classes = ['filter-games', 'filter-categories', 'filter-types', 'filter-users', 'filter-includes', 'filter-rating', 'filter-sort'];
            const zero = ['All Games', 'All Categories', 'All Types', 'All Users', 'Any Includes', 'Min. Rating: Any', 'Sort: Created'];
            const one = ['{text}', '{text}', '{text}', '<img src="{avatar}" alt="avatar" /> {text}', '{text}', 'Min. Rating: {text}', 'Sort: {text}'];
            const many = ['{count} Games', '{count} Categories', '{count} Types', '{count} Users', '{count} Includes', 'Min. Rating: {text}', 'Sort: {text}'];

            for (let i = 0; i < classes.length; i++) {
                if (cls && cls !== classes[i]) continue;
                const el = document.querySelector(`.${classes[i]}`);
                const input = document.querySelector(`[data-filter=${classes[i]}]`);
                if (!el || !input) continue;

                const filterInfo = el.previousElementSibling.querySelector('.filter-info');
                if (!filterInfo) continue;

                const activeQs = Array.from(el.querySelectorAll(`.active`));
                const count = activeQs.length;
                const text = activeQs.map(x => x.textContent.trim()).join(' ').trim();
                const templ = count === 0 ? zero[i] : count === 1 ? one[i] : many[i];

                const obj = { text, count };
                if (activeQs.length > 0) Object.assign(obj, activeQs[0].dataset);
                filterInfo.innerHTML = nanoTemplate(templ, obj);
                input.value = activeQs.map(x => x.dataset.filterValue).join('-');
            }
        }

        async function populate_dynamic_filter(cls, url, obj) {
            const resp = await fetch(url);
            if (!resp.ok) return;
            const data = await resp.json();
            populate_filter(cls, data, templates[`item:${cls}`], obj, templates[`append:${cls}`]);
        }

        function populate_dynamic_content() {
            populate_dynamic_filter('filter-games', '{{ url("api/games") }}?count=100', { size: 32 });
            populate_dynamic_filter('filter-categories', '{{ url("api/vault-categories") }}?count=100');
            populate_dynamic_filter('filter-types', '{{ url("api/vault-types") }}?count=100');
            populate_dynamic_filter('filter-includes', '{{ url("api/vault-includes") }}?count=100');

            const filterUsers = document.querySelector('[name=users]');
            const users = (filterUsers.value || '').split('-').join(',');
            if (users) {
                populate_dynamic_filter('filter-users', '{{ url("api/users") }}?id=' + users + '&count=100');
            } else {
                populate_filter('filter-users', [], '', null, templates['append:filter-users']);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            let timeout = null;

            document.querySelectorAll('.vault-filter').forEach(x => {
                window.filteredEventListener(x, 'click', '.filter-action', (event, el) => {
                    const clr = el.matches('.clear-filter');
                    const par = el.closest('.vault-filter');
                    if (par.matches('.filter-one') || clr) {
                        par.querySelectorAll('.active').forEach(a => a.classList.remove('active'));
                    } else {
                        event.stopPropagation();
                    }
                    if (par.matches('.remove-item') && clr) par.querySelectorAll('li:not(.static-control)').forEach(e => e.remove());
                    else if (par.matches('.remove-item')) x.closest('li').remove();
                    else if (!clr) el.classList.toggle('active');
                    update_filters();
                });
                window.filteredEventListener(x, 'keyup', '.user-search input', (event, el) => {
                    const results = el.parentElement.querySelector('.search-results');
                    results.innerHTML = '<li class="loading">Searching...</li>';

                    if (timeout) clearTimeout(timeout);
                    timeout = setTimeout(async () => {
                        clearTimeout(timeout);
                        const resp = await fetch('{{ url("api/users") }}?filter=' + encodeURIComponent(el.value));
                        const data = await resp.json();
                        results.replaceChildren(...data.map(u => {
                            return nanoTemplateHtml('<li class="user-item" data-filter-value="{id}" data-avatar="{avatar_inline}"><img src="{avatar_inline}" alt="avatar" /> {name} <span class="fa fa-remove filter-action"></span></li>', u);
                        }));
                    }, 500);
                });
                window.filteredEventListener(x, 'click', '.user-search .user-item', (event, el) => {
                    event.stopPropagation();

                    const val = el.dataset.filterValue;
                    const existing = document.querySelector('.filter-users > [data-filter-value="' + val + '"]');
                    if (existing) return;

                    const clone = el.cloneNode();
                    clone.classList.add('active');
                    document.querySelector('.filter-users .clear-filter').before(clone);

                    update_filters('filter-users');

                    const input = document.querySelector('.filter-users .user-search input');
                    input.value = '';
                    input.focus();
                });
            });

            const fu = document.querySelector('.filter-users');
            fu.parentElement.addEventListener('shown.bs.dropdown', () => {
                const input = fu.querySelector('input');
                input.value = '';
                input.focus();
            });

            // Update dynamic content
            populate_dynamic_content();

            // Update static content
            const sel_rating = document.querySelector('[data-filter=filter-rating]').value;
            if (sel_rating) {
                const rating = document.querySelector(`.filter-rating [data-filter-value="${sel_rating}"]`);
                if (rating) rating.classList.add('active');
                update_filters('filter-rating');
            }

            const sel_sort = document.querySelector('[data-filter=filter-sort]').value;
            if (sel_sort) {
                const sort = document.querySelector(`.filter-sort [data-filter-value="${sel_sort}"]`);
                if (sort) sort.classList.add('active');
                update_filters('filter-sort');
            }
        });
    </script>
@endsection
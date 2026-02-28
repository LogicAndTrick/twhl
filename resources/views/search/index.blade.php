@title($searched ? 'Search results' : 'Search TWHL')
@extends('app')

@section('scripts')
    @guest
    {!! ReCaptcha::htmlScriptTagJsApi() !!}
    @endguest
@endsection

@section('content')
    <h1>{{ $searched ? 'Search results' : 'Search TWHL' }}</h1>

    <form action="{{ url('search/index') }}" method="get">
        <div class="input-group">
            <span class="input-group-text"><span class="fa fa-search"></span></span>
            <input type="text" class="form-control" name="search" placeholder="Search" value="{{ $search }}" maxlength="{{ $max_search_length  }}">
            <button type="submit" class="btn btn-light">Search</button>
        </div>
        <span class="form-text">
            A strict limit has been applied to the search length due to spambots posting complex queries and taking the site offline.
            @guest
                Login to search for longer queries, or use Google instead.
            @endguest
        </span>

        @guest
            <div class="d-flex justify-content-center mt-2">
                {!! ReCaptcha::htmlFormSnippet() !!}
            </div>
            @error('g-recaptcha-response')
                <p class="help-block text-danger">Please check the box to prove that you're not a robot.</p>
            @enderror
        @endguest
    </form>

    <hr/>

    <div class="search-results">
        @if ($searched)

            <p>
                This search uses basic full-word matching to try and find relevant results
                for a search. However, it's not a very powerful search engine. If you are having
                trouble finding something, you may get better results if you
                <a href="https://www.google.com/search?q={{ urlencode('site:' . url('/') . ' ' . $search) }}">
                click here to repeat your search on TWHL using Google</a>.
            </p>

            <hr />

            <h2>Wiki articles</h2>
            @if ($results_wikis && $results_wikis->count() > 0)
                @auth
                {!! $results_wikis->render() !!}
                @endauth
                <table class="table table-sm table-striped table-bordered search-wikis">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th class="hidden-sm-down">Last Modified</th>
                            <th class="hidden-md-down">Excerpt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results_wikis as $wiki)
                            <tr>
                                <td>
                                    <a href="{{ act('wiki', 'page', $wiki->slug) }}">{{ $wiki->getNiceTitle() }}</a>
                                </td>
                                <td class="hidden-sm-down">
                                    @date($wiki->created_at)
                                    by @avatar($wiki->user inline)
                                </td>
                                <td class="hidden-md-down">
                                    <div class="bbcode">{!! bbcode_excerpt($wiki->content_text) !!}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No matching wiki articles were found.</p>
            @endif

            <hr/>

            <h2>Thread titles</h2>
            @if ($results_threads && $results_threads->count() > 0)
                @auth
                {!! $results_threads->render() !!}
                @endauth
                <table class="table table-sm table-striped table-bordered search-threads">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th class="hidden-sm-down">Forum</th>
                            <th class="hidden-sm-down">Created</th>
                            <th class="hidden-md-down">Last Post</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results_threads as $thread)
                            <tr>
                                <td><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></td>
                                <td class="hidden-sm-down"><a href="{{ act('forum', 'view', $thread->forum->slug) }}">{{ $thread->forum->name }}</a></td>
                                <td class="hidden-sm-down">@date($thread->created_at)</td>
                                <td class="hidden-md-down">
                                    @if ($thread->last_post)
                                        <a href="{{ act('thread', 'view', $thread->id) }}?page=last#post-{{ $thread->last_post->id }}">{{ $thread->last_post->created_at->diffForHumans() }}</a>
                                        by @avatar($thread->last_post->user inline)
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No matching thread titles were found.</p>
            @endif

            <hr/>

            <h2>Forum posts</h2>
            @if ($results_posts && $results_posts->count() > 0)
                @auth
                {!! $results_posts->render() !!}
                @endauth
                <table class="table table-sm table-striped table-bordered search-posts">
                    <thead>
                        <tr>
                            <th>In Thread</th>
                            <th class="hidden-sm-down">Posted</th>
                            <th class="hidden-md-down">Excerpt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results_posts as $post)
                            <tr>
                                <td>
                                    <a href="{{ act('thread', 'locate-post', $post->id) }}">{{ $post->thread->title }}</a><br/>
                                    in <a href="{{ act('forum', 'view', $post->thread->forum->id) }}">{{ $post->forum->name }}</a>
                                </td>
                                <td class="hidden-sm-down">
                                    @date($post->created_at)<br/>
                                    by @avatar($post->user inline)
                                </td>
                                <td class="hidden-md-down">
                                    <div class="bbcode">{!! bbcode_excerpt($post->content_text) !!}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No matching forum posts were found.</p>
            @endif

            <hr/>

            <h2>Vault items</h2>
            @if ($results_vaults && $results_vaults->count() > 0)
                @auth
                {!! $results_vaults->render() !!}
                @endauth
                <p>For a more refined vault search, go to the <a href="{{ act('vault', 'index') }}">vault listings page</a>.</p>
                <table class="table table-sm table-striped table-bordered search-wikis">
                    <thead>
                        <tr>
                            <th>Vault Item</th>
                            <th class="hidden-sm-down">Category</th>
                            <th class="hidden-sm-down">Type</th>
                            <th class="hidden-sm-down">Uploaded By</th>
                            <th class="hidden-md-down">Excerpt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results_vaults as $vault)
                            <tr>
                                <td>
                                    <a href="{{ act('vault', 'view', $vault->id) }}">{{ $vault->name }}</a>
                                </td>
                                <td class="hidden-sm-down">{{ $vault->vault_category->name }}</td>
                                <td class="hidden-sm-down">{{ $vault->vault_type->name }}</td>
                                <td class="hidden-sm-down">
                                    @date($vault->created_at)
                                    by @avatar($vault->user inline)
                                </td>
                                <td class="hidden-md-down">
                                    <div class="bbcode">{!! bbcode_excerpt($vault->content_text) !!}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No matching vault items were found.</p>
                <p>For a more refined vault search, go to the <a href="{{ act('vault', 'index') }}">vault listings page</a>.</p>
            @endif

            <hr/>

            <h2>Users</h2>
            @if ($results_users && $results_users->count() > 0)
                @auth
                {!! $results_users->render() !!}
                @endauth
                <table class="table table-sm table-striped table-bordered search-users">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th class="hidden-sm-down">Biography Excerpt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results_users as $user)
                            <tr>
                                <td>
                                    @avatar($user inline)
                                </td>
                                <td class="hidden-sm-down">
                                    <div class="bbcode">{!! bbcode_excerpt($user->info_biography_text) !!}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No matching users were found.</p>
            @endif

        @else
            <p>
                Use the form above to search TWHL. It will search users, vault items, wiki entries, thread titles, and forum posts.
            </p>
        @endif

    </div>
@endsection
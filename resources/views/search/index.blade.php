@title($searched ? 'Search results' : 'Search TWHL')
@extends('app')

@section('content')
    <hc>
        <h1>{{ $searched ? 'Search results' : 'Search TWHL' }}</h1>
    </hc>
    <form action="{{ url('search/index') }}" method="get">
        <div class="input-group">
            <div class="input-group-addon"><span class="fa fa-search"></span></div>
            <input type="text" class="form-control" name="search" placeholder="Search" value="{{ $search }}">
            <div class="input-group-btn">
                <button type="submit" class="btn btn-default">Search</button>
            </div>
        </div>
    </form>
    <hr/>
    <div class="search-results">
        @if ($searched)

            <p>
                This search uses basic full-word matching to try and find relevant results
                for a search. However, it's not a very powerful search engine. If you are having
                trouble finding something, you may get better results if you
                <a href="https://www.google.com/#q=site:{{ url('/') }}+{{ urlencode($search) }}">
                click here to repeat your search on TWHL using Google</a>.
            </p>

            <hr />

            <h2>Wiki articles</h2>
            @if ($results_wikis && $results_wikis->count() > 0)
                {!! $results_wikis->render() !!}
                <table class="table table-condensed table-striped table-bordered search-wikis">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Last Modified</th>
                            <th>Excerpt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results_wikis as $wiki)
                            <tr>
                                <td>
                                    <a href="{{ act('wiki', 'page', $wiki->slug) }}">{{ $wiki->getNiceTitle() }}</a>
                                </td>
                                <td>
                                    @date($wiki->created_at)
                                    by @avatar($wiki->user inline)
                                </td>
                                <td>
                                    <div class="bbcode">{!! app('bbcode')->ParseExcerpt($wiki->content_text) !!}</div>
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
                {!! $results_threads->render() !!}
                <table class="table table-condensed table-striped table-bordered search-threads">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Forum</th>
                            <th>Created</th>
                            <th>Last Post</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results_threads as $thread)
                            <tr>
                                <td><a href="{{ act('thread', 'view', $thread->id) }}">{{ $thread->title }}</a></td>
                                <td><a href="{{ act('forum', 'view', $thread->forum->slug) }}">{{ $thread->forum->name }}</a></td>
                                <td>@date($thread->created_at)</td>
                                <td>
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
                {!! $results_posts->render() !!}
                <table class="table table-condensed table-striped table-bordered search-posts">
                    <thead>
                        <tr>
                            <th>In Thread</th>
                            <th>Posted</th>
                            <th>Excerpt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results_posts as $post)
                            <tr>
                                <td>
                                    <a href="{{ act('thread', 'locate-post', $post->id) }}">{{ $post->thread->title }}</a><br/>
                                    in <a href="{{ act('forum', 'view', $post->thread->forum->id) }}">{{ $post->forum->name }}</a>
                                </td>
                                <td>
                                    @date($post->created_at)<br/>
                                    by @avatar($post->user inline)
                                </td>
                                <td>
                                    <div class="bbcode">{!! app('bbcode')->ParseExcerpt($post->content_text) !!}</div>
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
                {!! $results_vaults->render() !!}
                <p>For a more refined vault search, go to the <a href="{{ act('vault', 'index') }}">vault listings page</a>.</p>
                <table class="table table-condensed table-striped table-bordered search-wikis">
                    <thead>
                        <tr>
                            <th>Vault Item</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Uploaded By</th>
                            <th>Excerpt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results_vaults as $vault)
                            <tr>
                                <td>
                                    <a href="{{ act('vault', 'view', $vault->id) }}">{{ $vault->name }}</a>
                                </td>
                                <td>{{ $vault->vault_category->name }}</td>
                                <td>{{ $vault->vault_type->name }}</td>
                                <td>
                                    @date($vault->created_at)
                                    by @avatar($vault->user inline)
                                </td>
                                <td>
                                    <div class="bbcode">{!! app('bbcode')->ParseExcerpt($vault->content_text) !!}</div>
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
                {!! $results_users->render() !!}
                <table class="table table-condensed table-striped table-bordered search-users">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Biography Excerpt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results_users as $user)
                            <tr>
                                <td>
                                    @avatar($user inline)
                                </td>
                                <td>
                                    <div class="bbcode">{!! app('bbcode')->ParseExcerpt($user->info_biography_text) !!}</div>
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
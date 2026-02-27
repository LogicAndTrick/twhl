<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
        <title>{{ isset($page_title) && !!$page_title ? $page_title . ' - ' : '' }}TWHL: Half-Life and Source Mapping Tutorials and Resources</title>

        <meta content="The Whole Half-Life" property="og:site_name">
        @if (!empty($meta_description))
            <?php
                if (mb_strlen($meta_description) > 300) $meta_description = mb_substr($meta_description, 0, 300) . '...';
                // Manually do escaping here so we can encode newlines
                $meta_description = e($meta_description);
                $meta_description = str_replace(["\r\n", "\r", "\n"], '&#10;', $meta_description);
            ?>
            <meta property="og:description" content="{!! $meta_description !!}">
        @else
            <meta property="og:description" content="View this page on TWHL">
        @endif
        <meta property="og:type" content="website">
        @if (isset($meta_title) && strlen($meta_title) > 0)
            <meta property="og:title" content="{{$meta_title}}">
        @else
            <meta property="og:title" content="{{$page_title}}">
        @endif
        @if (isset($meta_images) && count($meta_images) > 0)
            @foreach ($meta_images as $img)
                <meta property="og:image" content="{{asset($img)}}">
            @endforeach
            <meta name="twitter:card" content="summary_large_image">
        @else
            <meta property="og:image" content="{{asset('images/twhl-logo-1200.png')}}">
        @endif
        <meta property="og:url" content="{{Request::url()}}">
        <meta name="theme-color" content="#e68a27">

        <link href="{{ mix('/css/app.css') }}?sl" rel="stylesheet">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
        <link rel="search" type="application/opensearchdescription+xml" href="{{ url('/opensearch.xml') }}" title="TWHL">

        @yield('styles', '')

        <!-- Fonts -->
        <link href='//fonts.googleapis.com/css?family=Roboto:400,300' rel='stylesheet' type='text/css'>

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

        <!-- Scripts -->
        <script type="text/javascript" src="{{ mix('/js/all.js') }}"></script>
    </head>
<body class="{{ egg() }}">

    @include('nav')

    @if (!app()->environment('production') && false && '<- temp, delete that')
        <div class="alert alert-warning">
            <div class="container">
                This is the public beta version of TWHL and any content posted here will be deleted once the database is refreshed.
                Please go to <a href="http://twhl.info">twhl.info</a> if you are looking for resources or information.
            </div>
        </div>
    @endif

    <div class="container{{ isset($fluid) && $fluid === true ? '-fluid' : '' }}">
        @include('nav-desktop')
	    @yield('content')
    </div>

    <footer>
        Site and non-dynamic content copyright &copy; {{\Carbon\Carbon::now()->year}}, <a href="http://logic-and-trick.com/">Logic & Trick</a>. Original site by atom. All rights reserved.<br/>
        All member-submitted resources copyright their respective authors unless otherwise specified.<br/>
        TWHL is a fan site and is not affiliated with Valve Corporation. Views expressed by users are the individual's own and do not represent the opinions of any other entity.<br/>
        {{ render_time() }}
    </footer>

    <div id="shoutbox-container"></div>

    @if (app('config')->get('app.debug'))
        <div class="container hidden-sm-down" style="padding-top: 20px;">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Query</th>
                        <th>Parameters</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                {? $debug_query_total = 0; $debug_query_count = 0; ?}
                @foreach (DB::getQueryLog() as $query)
                    {? $debug_query_total += $query['time']; $debug_query_count++; ?}
                    <tr>
                        <td>{{ $query['query'] }}</td>
                        <td>{{ print_r($query['bindings'], true) }}</td>
                        <td>{{ $query['time'] }}ms</td>
                    </tr>
                @endforeach
                <tr>
                    <th>Total</th>
                    <th>{{ $debug_query_count }} queries</th>
                    <th>{{ $debug_query_total }}ms</th>
                </tr>
                </tbody>
            </table>
        </div>
    @endif
    <script type="text/javascript">
        window.urls = {
            embed: {
                vault: '{{ url("api/vault-items") }}',
                vault_screenshot: '{{ asset("uploads/vault/{shot}") }}',
                game_icon: '{{asset('images/games/{game_abbr}_32.svg') }}'
            },
            view: {
                user: '{{ url("user/view/{id}") }}',
                vault: '{{ url("vault/view/{id}") }}'
            },
            images: {
                no_screenshot_320: '{{ asset("images/no-screenshot-320.png") }}',
                no_screenshot_640: '{{ asset("images/no-screenshot-640.png") }}',
                smiley_folder: '{{ asset('images/smilies') }}'
            },
            api: {
                image_upload: '{{ url("api/image-upload") }}',
                format: '{{ url("api/posts/format") }}'
            },
            wiki: {
                page: '{{ url('wiki/page/{slug}') }}',
                formatting_guide: '{{ url("wiki/page/TWHL:_WikiCode_Syntax") }}',
                book_info: '{{ url('wiki/book-info?book={book}') }}'
            }
        };
        $(function() {
            window.initShoutbox({
                url:'{{ url("api/shouts{action}") }}',
                userUrl:'{{ url("user/view/{id}") }}',
                active: {{ Auth::user() != null ? "true" : "false" }},
                moderator: {{ permission("ForumAdmin") ? "true" : "false" }}
            });
        });
        $('.navbar-search-dropdown .dropdown').on('shown.bs.dropdown', function () {
            $(this).find('input:text').focus();
        });
    </script>
    @yield('scripts', '')
</body>
</html>

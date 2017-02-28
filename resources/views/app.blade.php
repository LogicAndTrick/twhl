<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>{{ isset($page_title) && !!$page_title ? $page_title . ' - ' : '' }}TWHL: Half-Life and Source Mapping Tutorials and Resources</title>

        <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />

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
        <script type="text/javascript" src="{{ asset('/js/all.js') }}"></script>
    </head>
<body class="{{ egg() }}">

    @include('nav')

    @if (!app()->environment('production'))
        <div class="alert alert-danger">
            <div class="container">
                This is the beta version of TWHL and is not currently active. Please go to <a href="http://twhl.info">twhl.info</a> if you are looking for resources or information.
            </div>
        </div>
    @endif

    <div class="container{{ isset($fluid) && $fluid === true ? '-fluid' : '' }}">
	    @yield('content')
    </div>

    <footer>
        Site and non-dynamic content copyright &copy; 2016, <a href="http://logic-and-trick.com/">Logic & Trick</a>. Original site by atom. All rights reserved.<br/>
        All member-submitted resources copyright their respective authors unless otherwise specified.<br/>
        {{ render_time() }}
    </footer>

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
                no_screenshot_640: '{{ asset("images/no-screenshot-640.png") }}'
            }
        };
        $('body').shoutbox({
            url:'{{ url("api/shouts{action}") }}',
            userUrl:'{{ url("user/view/{id}") }}',
            active: '{{ Auth::user() != null ? "true" : "false" }}',
            moderator: '{{ permission("ForumAdmin") ? "true" : "false" }}'
        });
        $('.navbar-search-dropdown .dropdown').on('shown.bs.dropdown', function () {
            $(this).find('input:text').focus();
        });
    </script>
    @yield('scripts', '')
</body>
</html>

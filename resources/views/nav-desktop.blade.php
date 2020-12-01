<header class="header-desktop hidden-md-down">
    <?php $header = \App\Helpers\TwhlHeader::HeaderInfo(); ?>
    <div class="header-image" style="background-image: url({{asset('/images/header/'.$header['image'])}});">
        <div class="wrapper">
            <a href="{{ url('/') }}">
                <img class="logo-image" src="{{ asset('images/twhl-logo-64.png') }}" alt="">
                <div class="logo-text">
                    <div class="title">The Whole Half-Life</div>
                    <div class="subtitle">Level design resources for GoldSource, Source, and beyond</div>
                </div>
            </a>
            <div class="header-info">
                <span>
                    Featured {{$header['type']}}:
                    <a href="{{$header['name_link']}}">{{$header['name']}}</a>
                    by
                    <a href="{{$header['author_link']}}">{{$header['author']}}</a>
                </span>
            </div>
        </div>
    </div>
    <nav class="header-nav navbar navbar-expand navbar-dark bg-theme">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="{{ url('/forum') }}">Forums</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('/wiki') }}">Wiki</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('/vault') }}">Vault</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ url('/competition') }}">Competitions</a></li>
            <li class="nav-item"><a class="nav-link" href="https://discord.gg/jEw8EqD">Discord</a></li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="{{ url('/') }}" data-toggle="dropdown">
                    Community
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ url('/journal') }}">Journals</a>
                    <a class="dropdown-item" href="{{ url('/news') }}">News</a>
                    <a class="dropdown-item" href="{{ url('/poll') }}">Polls</a>
                    <a class="dropdown-item" href="{{ url('/user') }}">Members</a>
                </div>
            </li>
        </ul>
        <ul class="navbar-nav">
            @if (Auth::guest())
                <li class="navbar-login-dropdown nav-item dropdown">
                    <a class="nav-link dropdown-toggle"  href="{{ url('/auth/login') }}" data-toggle="dropdown">
                        Login/Register
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @form(auth/login)
                            <div class="navbar-form login-form">
                                {? $login_form_checked = true; ?}
                                <input type="text" class="form-control mb-2" name="email" placeholder="Email or username">
                                <input type="password" class="form-control mb-1" name="password" placeholder="Password">
                                @checkbox(remember $login_form_checked) = Remember Me
                                <button type="submit" class="btn btn-primary btn-block mb-1">Login</button>
                                <a href="{{ url('/password/email') }}">Forgot Your Password?</a>
                                <hr />
                                <div>New user?</div>
                                <a class="btn btn-success btn-block mt-1" href="{{ url('/auth/register') }}">Create an account</a>
                            </div>
                        @endform
                    </div>
                </li>
            @else
                {? $unread_count = Auth::user()->unreadPrivateMessageCount(); ?}
                {? $notify_count = Auth::user()->unreadNotificationCount(); ?}
                <li class="nav-item dropdown {{ $unread_count + $notify_count > 0 ? 'has-notification' : '' }}">
                    <a class="nav-link dropdown-toggle nav-avatar" href="{{ act('panel', 'index') }}" data-toggle="dropdown">
                        <img src="{{ Auth::user()->getAvatarUrl('inline') }}" alt="{{ Auth::user()->name }}"/>
                        @if ($unread_count + $notify_count > 0)
                            <span class="fa fa-exclamation-triangle"></span>
                        @endif
                        <span class="name">{{ Auth::user()->name }}</span>
                        <span class="caret"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item {{ $notify_count > 0 ? 'has-notification' : '' }}" href="{{ act('panel', 'notifications') }}">
                            <span class="fa fa-bell"></span> {{ $notify_count != 0 ? $notify_count : '' }} Notification{{ $notify_count == 1 ? '' : 's' }}
                        </a>
                        <a class="dropdown-item {{ $unread_count > 0 ? 'has-notification' : '' }}" href="{{ act('message', 'index') }}">
                            <span class="fa fa-envelope"></span> {{ $unread_count != 0 ? $unread_count : '' }} Private Message{{ $unread_count == 1 ? '' : 's' }}
                        </a>
                        <a class="dropdown-item" href="{{ act('user', 'view', Auth::user()->id) }}"><span class="fa fa-user"></span> My Profile</a>
                        <a class="dropdown-item" href="{{ act('panel', 'index') }}"><span class="fa fa-cogs"></span> Control Panel</a>
                        <a class="dropdown-item" href="{{ url('/auth/logout') . '?_token=' . csrf_token() }}"><span class="fa fa-sign-out"></span> Logout</a>
                    </div>
                </li>
            @endif
            <li class="nav-item">
                <form class="navbar-search-inline form-inline" action="{{ url('search/index') }}" method="get">
                    <div class="navbar-form navbar-search-form">
                        <div class="form-group">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text"><span class="fa fa-search"></span></span></div>
                                <input type="text" class="form-control" name="search" placeholder="Search">
                            </div>
                        </div>
                    </div>
                </form>
            </li>
        </ul>
    </nav>
</header>
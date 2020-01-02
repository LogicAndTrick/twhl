<header class="hidden-lg-up">
    <nav class="navbar navbar-expand-md bg-dark navbar-dark">
        <div class="container">

            <div class="d-flex justify-content-between" style="flex-grow: 1;">
            {{-- Logo --}}
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/logo_icon.png') }}" alt="">
                <span class="hidden-md-down">The Whole Half-Life</span>
                <span class="hidden-lg-up">TWHL</span>
            </a>

            {{-- Mobile nav toggle --}}
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile-collapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            </div>

            <div class="collapse navbar-collapse" id="navbar-mobile-collapse">

                {{-- Main nav --}}
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/forum') }}">Forums</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/wiki') }}">Wiki</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/vault') }}">Vault</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/competition') }}">Competitions</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ url('/') }}" data-toggle="dropdown">
                            More...
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ url('/journal') }}">Journals</a>
                            <a class="dropdown-item" href="{{ url('/news') }}">News</a>
                            <a class="dropdown-item" href="{{ url('/poll') }}">Polls</a>
                            <a class="dropdown-item" href="{{ url('/user') }}">Members</a>
                        </div>
                    </li>
                </ul>

                {{-- Search form --}}
                <form class="navbar-search-inline form-inline hidden-md-only hidden-lg-only" action="{{ url('search/index') }}" method="get">
                    <div class="navbar-form">
                        <div class="form-group">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text"><span class="fa fa-search"></span></span></div>
                                <input type="text" class="form-control" name="search" placeholder="Search">
                                <span class="input-group-append"><button type="submit" class="btn btn-light">Go</button></span>
                            </div>
                        </div>
                    </div>
                </form>
                <ul class="navbar-search-dropdown navbar-nav hidden-sm-down hidden-xl-up">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                            <span class="fa fa-search"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <form action="{{ url('search/index') }}" method="get">
                                <div class="navbar-form">
                                    <div class="form-group">
                                        <div class="input-group input-group-sm flex-nowrap">
                                            <div class="input-group-prepend"><span class="input-group-text"><span class="fa fa-search"></span></span></div>
                                            <input type="text" class="form-control" name="search" placeholder="Search">
                                            <span class="input-group-append"><button type="submit" class="btn btn-light">Go</button></span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </li>
                </ul>

                {{-- Account --}}
                <ul class="navbar-nav">
                    @if (Auth::guest())
                        <li class="navbar-login-dropdown nav-item dropdown">
                            <a class="nav-link dropdown-toggle"  href="{{ url('/auth/login') }}" data-toggle="dropdown">
                                Login
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                @form(auth/login)
                                    <div class="navbar-form login-form">
                                        {? $login_form_checked = true; ?}
                                        @text(email placeholder=Email/username) = Email or Username
                                        @password(password) = Password
                                        @checkbox(remember $login_form_checked) = Remember Me
                                        <div>
                                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                                            <a class="btn btn-link" href="{{ url('/password/email') }}">Forgot Your Password? </a>
                                        </div>
                                    </div>
                                @endform
                            </div>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="{{ url('/auth/register') }}">Register</a></li>
                    @else
                        {? $unread_count = Auth::user()->unreadPrivateMessageCount(); ?}
                        {? $notify_count = Auth::user()->unreadNotificationCount(); ?}
                        <li class="nav-item dropdown {{ $unread_count + $notify_count > 0 ? 'has-notification' : '' }}">
                            <a class="nav-link dropdown-toggle nav-avatar" href="{{ act('panel', 'index') }}" data-toggle="dropdown">
                                <img src="{{ Auth::user()->getAvatarUrl('small') }}" alt="{{ Auth::user()->name }}"/>
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
                </ul>
            </div>
        </div>
    </nav>
</header>
<header class="hidden-lg-up">
    <nav class="navbar navbar-expand-md bg-dark navbar-dark">
        <div class="container p-sm-0">

            <div class="d-flex justify-content-between" style="flex-grow: 1;">
            {{-- Logo --}}
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/logo_icon.png') }}" alt="">
                <span class="hidden-md-up">The Whole Half-Life</span>
                <span class="hidden-sm-down">TWHL</span>
            </a>

            {{-- Mobile nav toggle --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-mobile-collapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            </div>

            <div class="collapse navbar-collapse" id="navbar-mobile-collapse">

                {{-- Main nav --}}
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ act('forum', 'index') }}">Forums</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ act('wiki', 'index') }}">Wiki</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ act('vault', 'index') }}">Vault</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ act('competition', 'index') }}">Competitions</a></li>
                    <li class="nav-item"><a class="nav-link" href="https://discord.gg/jEw8EqD">Discord</a></li>
                    <li class="nav-item">
                        <button class="nav-link theme-toggle d-md-none">
                            <span class="fa fa-lightbulb-o"></span> Toggle theme
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{ url('/') }}" data-bs-toggle="dropdown">
                            More...
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ act('journal', 'index') }}">Journals</a>
                            <a class="dropdown-item" href="{{ act('news', 'index') }}">News</a>
                            <a class="dropdown-item" href="{{ act('poll', 'index') }}">Polls</a>
                            <a class="dropdown-item" href="{{ act('user', 'index') }}">Members</a>
                            <button class="dropdown-item theme-toggle d-none d-md-block d-lg-none">
                                <span class="fa fa-lightbulb-o"></span> Toggle theme
                            </button>
                        </div>
                    </li>
                </ul>

                {{-- Search form --}}
                <form class="navbar-search-inline form-inline hidden-md-only hidden-lg-only" action="{{ url('search/index') }}" method="get">
                    <div class="navbar-form">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-text"><span class="fa fa-search"></span></span>
                                <input type="text" class="form-control" name="search" placeholder="Search">
                                <button type="submit" class="btn btn-light">Go</button>
                            </div>
                        </div>
                    </div>
                </form>
                <ul class="navbar-search-dropdown navbar-nav hidden-sm-down hidden-xl-up">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <span class="fa fa-search"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <form action="{{ url('search/index') }}" method="get">
                                <div class="navbar-form">
                                    <div class="form-group">
                                        <div class="input-group input-group-sm flex-nowrap">
                                            <span class="input-group-text"><span class="fa fa-search"></span></span>
                                            <input type="text" class="form-control" name="search" placeholder="Search">
                                            <button type="submit" class="btn btn-light">Go</button>
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
                            <a class="nav-link dropdown-toggle"  href="{{ url('/auth/login') }}" data-bs-toggle="dropdown">
                                Login
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                @form(auth/login)
                                    <div class="navbar-form login-form">
                                        {? $login_form_checked = true; ?}
                                        @text(email placeholder=Email/username) = Email or Username
                                        @password(password) = Password
                                        @checkbox(remember $login_form_checked) = Remember Me
                                        <div>
                                            <button type="submit" class="btn btn-primary d-block w-100">Login</button>
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
                            <a class="nav-link dropdown-toggle nav-avatar" href="{{ act('panel', 'index') }}" data-bs-toggle="dropdown">
                                <img src="{{ Auth::user()->getAvatarUrl('small') }}" alt="{{ Auth::user()->name }}"/>
                                @if ($unread_count + $notify_count > 0)
                                    <span class="fa fa-exclamation-triangle"></span>
                                @endif
                                <span class="name">{{ Auth::user()->name }}</span>
                                <span class="caret"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
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
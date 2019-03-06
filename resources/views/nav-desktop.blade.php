<nav class="nav-desktop hidden-md-down header-{{ rand(1, 8) }}">
    <div class="wrapper">
        <div class="d-flex flex-row">
            <div class="mr-auto main-nav">

                <div class="d-flex flex-row align-items-center">

                    <a class="navbar-brand mr-auto" href="{{ url('/') }}">
                        <img src="{{ asset('images/logo_icon.png') }}" alt="">
                        The Whole Half-Life
                    </a>

                    <form class="navbar-search-inline form-inline" action="{{ url('search/index') }}" method="get">
                        <div class="navbar-form navbar-search-form">
                            <div class="form-group">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text"><span class="fa fa-search"></span></span></div>
                                    <input type="text" class="form-control" name="search" placeholder="Search">
                                    <span class="input-group-append"><button type="submit" class="btn btn-light">Go</button></span>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>

                <ul class="nav nav-pills">
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
                </ul>
            </div>
            {? $data = header_data() ?}
            <div class="greetings text-right align-self-center">
                @if ($data['competition'])
                    {? $comp = $data['competition'] ?}
                    @if ($comp->isVotingOpen())
                        <span class="hidden-lg-down">Vote for a winner in the <a href="{{ act('competition', 'vote', $comp->id) }}">{{ $comp->name }}</a> competition!</span>
                        <span class="hidden-xl-up">Vote now: <a href="{{ act('competition', 'vote', $comp->id) }}">{{ $comp->name }}</a></span>
                    @elseif ($comp->isOpen())
                        <span class="hidden-lg-down">Enter our newest competition, <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a>!</span>
                        <span class="hidden-xl-up">Open for entries: <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a></span>
                    @elseif ($comp->isJudging() || $comp->isVoting())
                        <span class="hidden-lg-down"><a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a> competition results coming soon...</span>
                        <span class="hidden-xl-up">Results coming soon: <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a></span>
                    @elseif ($comp->isClosed())
                        <span class="hidden-lg-down">Check out <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a> competition results!</span>
                        <span class="hidden-xl-up">Competition results: <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a></span>
                    @else
                        <span class="hidden-lg-down">Take a look at our latest competition, <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a>!</span>
                        <span class="hidden-xl-up">Latest competition: <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a></span>
                    @endif
                    <br />
                @endif
                @if ($data['motm'])
                    {? $motm = $data['motm'] ?}
                        <span class="hidden-lg-down"><a href="{{ act('vault', 'view', $motm->item_id) }}">{{ $motm->vault_item->name }}</a> is map of the month for {{ $motm->getDateString() }}!</span>
                        <span class="hidden-xl-up">{{ $motm->getShortDateString() }} MOTM: <a href="{{ act('vault', 'view', $motm->item_id) }}">{{ $motm->vault_item->name }}</a></span>
                    <br/>
                @endif
                @if ($data['user'])
                    {? $user = $data['user'] ?}
                    <span class="hidden-lg-down">Say hello to <a href="{{ act('user', 'view', $user->id) }}">{{ $user->name }}</a>, our newest member!</span>
                    <span class="hidden-xl-up">Welcome to TWHL, <a href="{{ act('user', 'view', $user->id) }}">{{ $user->name }}</a>!</span>
                @endif
            </div>
        </div>
    </div>
</nav>
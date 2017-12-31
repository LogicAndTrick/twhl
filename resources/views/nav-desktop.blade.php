<nav class="nav-desktop hidden-md-down header-{{ rand(1, 4) }}">
    <div class="wrapper">
        <div class="d-flex flex-row">
            <div class="mr-auto">

                <div class="d-flex flex-row align-items-center">

                    <a class="navbar-brand mr-auto" href="{{ url('/') }}">
                        <img src="{{ asset('images/logo_icon.png') }}" alt="">
                        The Whole Half-Life
                    </a>

                    <form class="navbar-search-inline form-inline" action="{{ url('search/index') }}" method="get">
                        <div class="navbar-form">
                            <div class="form-group">
                                <div class="input-group input-group-sm">
                                    <div class="input-group-addon"><span class="fa fa-search"></span></div>
                                    <input type="text" class="form-control" name="search" placeholder="Search">
                                    <span class="input-group-btn"><button type="submit" class="btn btn-default">Go</button></span>
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
                                    <div class="navbar-form">
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
                                @if ($unread_count > 0)
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
                                <a class="dropdown-item" href="{{ url('/auth/logout') }}"><span class="fa fa-sign-out"></span> Logout</a>
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
                        Vote for a winner in the <a href="{{ act('competition', 'vote', $comp->id) }}">{{ $comp->name }}</a> competition!
                    @elseif ($comp->isOpen())
                        Enter our newest competition, <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a>!
                    @elseif ($comp->isJudging() || $comp->isVoting())
                        <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a> competition results coming soon...
                    @elseif ($comp->isClosed())
                        Check out <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a> competition results!
                    @else
                        Take a look at our latest competition, <a href="{{ act('competition', 'brief', $comp->id) }}">{{ $comp->name }}</a>!
                    @endif
                    <br />
                @endif
                @if ($data['motm'])
                    {? $motm = $data['motm'] ?}
                    <a href="{{ act('vault', 'view', $motm->item_id) }}">{{ $motm->vault_item->name }}</a> is map of the month for {{ $motm->getDateString() }}!
                    <br/>
                @endif
                @if ($data['user'])
                    {? $user = $data['user'] ?}
                    Say hello to <a href="{{ act('user', 'view', $user->id) }}">{{ $user->name }}</a>, our newest member!
                @endif
            </div>
        </div>
    </div>
</nav>
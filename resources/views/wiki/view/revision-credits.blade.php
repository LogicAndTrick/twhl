@if ($revision && $revision->wiki_revision_credits->where('type', '!=', \App\Models\Wiki\WikiRevisionCredit::FULL)->count() > 0)
    <ul class="wiki-credits">
        <li class="header">Article Credits</li>
        @foreach ($revision->wiki_revision_credits->where('type', '!=', \App\Models\Wiki\WikiRevisionCredit::FULL) as $cred)
            <li>
                @if ($cred->type == \App\Models\Wiki\WikiRevisionCredit::CREDIT)
                    <span class="fa fa-pencil"></span>
                    @if ($cred->user_id)
                        @avatar($cred->user inline)
                    @elseif ($cred->url)
                        <a href="{{$cred->url}}">{{$cred->name}}</a>
                    @else
                        <strong>{{$cred->name}}</strong>
                    @endif
                    &ndash;
                    {{$cred->description}}
                @elseif ($cred->type == \App\Models\Wiki\WikiRevisionCredit::ARCHIVE)
                    <span class="fa fa-globe"></span>
                    This article contains archived content from
                    <strong>{{$cred->name}}</strong>@if($cred->description) &ndash; <em>{{$cred->description}}</em>@endif.
                    @if ($cred->url)
                        Original link <a href="{{$cred->url}}">here</a>.
                    @endif
                    @if ($cred->wayback_url)
                        Archive link <a href="{{$cred->wayback_url}}">here</a>.
                    @endif
                @endif
            </li>
        @endforeach
        @if ($revision->wiki_revision_credits->where('type', '=', \App\Models\Wiki\WikiRevisionCredit::ARCHIVE)->count() > 0)
            <li>
                <span class="fa fa-info-circle"></span>
                TWHL only publishes archived articles from defunct websites, or with permission.
                For more information on TWHL's archiving efforts, please visit the
                <a href="{{ url('wiki/page/TWHL_Archiving_Project') }}" title="TWHL Archiving Project">TWHL Archiving Project</a> page.
            </li>
        @endif
    </ul>
@endif
@if ($revision && $revision->wiki_revision_credits->where('type', '=', \App\Models\Wiki\WikiRevisionCredit::FULL)->count() > 0)
    <div class="wiki-archive-credits bbcode">
        @foreach ($revision->wiki_revision_credits->where('type', '=', \App\Models\Wiki\WikiRevisionCredit::FULL) as $cred)

            <div class="card card-info">
                <div class="card-body">
                    <div>
                        <span class="fa fa-globe"></span>
                        This article was originally published on <strong>{{$cred->name}}</strong>@if($cred->description) as <em>{{$cred->description}}</em>@endif.
                    </div>
                    @if ($cred->url)
                        <div class="ml-3">
                            <span class="fa fa-link"></span> The original URL of the article was <a href="{{$cred->url}}">{{$cred->url}}</a>.
                        </div>
                    @endif
                    @if ($cred->wayback_url)
                        <div class="ml-3">
                            <span class="fa fa-archive"></span> The archived page is available <a href="{{$cred->wayback_url}}">here</a>.
                        </div>
                    @endif
                    <div>
                        <span class="fa fa-info-circle"></span>
                        TWHL only publishes archived articles from defunct websites, or with permission.
                        For more information on TWHL's archiving efforts, please visit the
                        <a href="{{ url('wiki/page/TWHL_Archiving_Project') }}" title="TWHL Archiving Project">TWHL Archiving Project</a> page.
                    </div>
                </div>
            </div>

        @endforeach
    </div>
@endif
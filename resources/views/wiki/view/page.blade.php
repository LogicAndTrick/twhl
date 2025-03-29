<h1>
    <span class="fa fa-file-o"></span>
    {{ $revision->getNiceTitle() }}

    <small class="pull-right">
        @if (!$revision->wiki_object->canEdit())
            <span class="fa fa-lock" title="You do not have access to edit this page."></span>
        @elseif ($revision->wiki_object->isProtected())
            <span class="fa fa-lock faded" title="This page is protected."></span>
        @endif
        Last edited @date($revision->created_at)
    </small>
</h1>

<ol class="breadcrumb">
    <li><a href="{{ url('/wiki') }}">Wiki</a></li>
    <li class="active">View Page</li>
    <li class="ms-auto no-breadcrumb">
        @if (Auth::check())
            @if ($obj_subscription)
                <a href="{{ act('wiki', 'unsubscribe', $revision->object_id) }}" class="btn btn-xs btn-outline-inverse"><span class="fa fa-bell"></span> Unsubscribe</a>
            @else
                <a href="{{ act('wiki', 'subscribe', $revision->object_id) }}" class="btn btn-xs btn-outline-inverse"><span class="fa fa-bell"></span> Subscribe</a>
            @endif
        @endif
    </li>
</ol>
@if(Request::session()->get('wiki.redirected'))
    <div class="ms-2 mb-2" style="margin-top: -0.75rem">
        <span class="fa fa-share fa-flip-vertical"></span>
        Redirected from <a href="{{act('wiki', 'page', Request::session()->get('wiki.redirected')) . '?no_redirect=true'}}">{{Request::session()->get('wiki.redirected')}}</a>
    </div>
@endif

@include('wiki.view.revision-content', ['revision' => $revision])
@include('wiki.view.revision-categories', ['revision' => $revision])
@include('wiki.view.revision-credits', ['revision' => $revision])
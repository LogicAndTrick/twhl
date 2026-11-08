<h1 class="d-flex align-items-center">
    <span class="me-1">
        <span class="far fa-file"></span>
    </span>
    <span class="me-auto">
        {{ $revision->getNiceTitle() }}
    </span>

    <small>
        @if (!$revision->wiki_object->canEdit())
            <span class="fas fa-lock" title="You do not have access to edit this page."></span>
        @elseif ($revision->wiki_object->isProtected())
            <span class="fas fa-lock faded" title="This page is protected."></span>
        @endif
        Last edited @date($revision->created_at)
    </small>
</h1>

<ol class="breadcrumb">
    <li><a href="{{ act('wiki', 'index') }}">Wiki</a></li>
    <li class="active">View Page</li>
    <li class="ms-auto no-breadcrumb d-flex">
        <label class="form-check form-check-inline d-none d-xl-inline-block">
            <input type="checkbox" class="form-check-input" id="reading-mode"> Reading mode
        </label>
        @if (Auth::check())
            @if ($obj_subscription)
                @form(wiki/unsubscribe)
                    @hidden(id $revision->object_id)
                    <button type="submit" class="btn btn-xs btn-outline-inverse"><span class="fas fa-bell"></span> Unsubscribe</button>
                @endform
            @else
                @form(wiki/subscribe)
                    @hidden(id $revision->object_id)
                    <button type="submit" class="btn btn-xs btn-outline-inverse"><span class="fas fa-bell"></span> Subscribe</button>
                @endform
            @endif
        @endif
    </li>
</ol>
@if(Request::session()->get('wiki.redirected'))
    <div class="ms-2 mb-2" style="margin-top: -0.75rem">
        <span class="fas fa-share fa-flip-vertical"></span>
        Redirected from <a href="{{act('wiki', 'page', Request::session()->get('wiki.redirected')) . '?no_redirect=true'}}">{{Request::session()->get('wiki.redirected')}}</a>
    </div>
@endif

@include('wiki.view.revision-content', ['revision' => $revision])
@include('wiki.view.revision-categories', ['revision' => $revision])
@include('wiki.view.revision-credits', ['revision' => $revision])

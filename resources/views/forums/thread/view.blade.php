@extends('app')

@section('content')
    <hc>
        @if (permission('ForumAdmin'))
            @if ($thread->deleted_at)
                <a href="{{ act('thread', 'restore', $thread->id) }}" class="btn btn-xs btn-info"><span class="glyphicon glyphicon-repeat"></span></a>
            @else
                <a href="{{ act('thread', 'delete', $thread->id) }}" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-remove"></span></a>
                <a href="{{ act('thread', 'edit', $thread->id) }}" class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-pencil"></span></a>
            @endif
        @endif

        <h1>{{ $thread->title }}</h1>

        <ol class="breadcrumb">
            <li><a href="{{ act('forum', 'index') }}">Forums</a></li>
            <li><a href="{{ act('forum', 'view', $forum->slug) }}">{{ $forum->name }}</a></li>
            <li class="active">View Thread</li>
        </ol>

        {!! $posts->render() !!}
    </hc>

    @foreach ($posts as $post)
        <div class="row">
            <div class="col-md-10 bbcode">
                {!! $post->content_html !!}
            </div>
            <div class="col-md-2">
                {{ Date::TimeAgo($post->created_at) }}<br>
                @if ($post->isEditable($thread))
                    <a href="{{ act('post', 'edit', $post->id) }}" class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-pencil"></span></a>
                @endif
                @if (permission('ForumAdmin'))
                    <a href="{{ act('post', 'delete', $post->id) }}" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-remove"></span></a>
                @endif
                <br>
                {{ $post->user->name }}
            </div>
        </div>
        <hr/>
    @endforeach

    @if (!$thread->isPostable())
        <div class="well">
            {{ $thread->getUnpostableReason() }}
        </div>
    @else
        @if (Date::DiffDays(Date::Now(), $thread->last_post->updated_at) > 90)
            <div class="alert alert-warning">
                <p>Careful! This thread is over 90 days old, and bumping it will cause it to become postable again.</p>
            </div>
        @endif
        @if (!$thread->is_open)
            <div class="alert alert-warning">
                <p>This thread is closed, regular users are not able to post in it.</p>
            </div>
        @endif
        @form(post/create)
            <h3>Add Post</h3>
            <input type="hidden" name="thread_id" value="{{ $thread->id }}" />
            @textarea(text) = Post Content
            <div class="form-group">
                <h4>
                    Post preview
                    <button id="update-preview" type="button" class="btn btn-info btn-xs">Update Preview</button>
                </h4>
                <div id="preview-panel" class="well bbcode"></div>
            </div>
            @submit
        @endform
    @endif
@endsection

@section('scripts')
    <script type="text/javascript">
        $('#update-preview').click(function() {
            $('#preview-panel').html('Loading...');
            $.post('{{ url("api/format") }}?field=text', $('form').serializeArray(), function(data) {
                $('#preview-panel').html(data);
            });
        });
    </script>
@endsection
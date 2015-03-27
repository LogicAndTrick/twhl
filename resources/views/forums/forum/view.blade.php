@extends('app')

@section('content')
    @foreach ($threads as $thread)
        <div class="row">
            <a href="{{ act('thread', 'view', $thread->id, 'last') }}">{{ $thread->title }}</a>
        </div>
    @endforeach
@endsection
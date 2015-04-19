@extends('app')

@section('content')
    <h2>Screenshots: {{ $item->name }}</h2>
    <ul class="media-list screenshot-list">
        @foreach ($item->vault_screenshots as $shot)
        <li class="media">
            <div class="media-left">
                <img class="media-object" src="{{ asset('uploads/vault/'.$shot->image_thumb) }}" alt="Screenshot">
            </div>
            <div class="media-body">
                <span class="drag-handle glyphicon glyphicon-menu-hamburger"></span>
            </div>
        </li>
        @endforeach
    </ul>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript">
        $( ".screenshot-list" ).sortable();
    </script>
@endsection
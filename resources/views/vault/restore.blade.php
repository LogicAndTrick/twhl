@extends('app')

@section('content')
    @form(vault/restore)
        <h3>Restore Vault Item: {{ $item->name }}</h3>
        @hidden(id $item)
        <p>You are about to restore this deleted vault item. This will un-delete the content and make it visible to all users again. Are you sure?</p>
        @submit
    @endform
@endsection

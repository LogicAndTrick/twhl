@extends('app')

@section('content')
    @form(vault/delete)
        <h3>Delete Vault Item: {{ $item->name }}</h3>
        @hidden(id $item)
        <p>You are about to delete this vault item. If you proceed, the content will no longer be available on TWHL. Are you sure?</p>
        @submit
    @endform
@endsection

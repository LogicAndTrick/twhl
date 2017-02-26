@title('Delete vault item: '.$item->name)
@extends('app')

@section('content')
    <h1>Delete vault item: {{ $item->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
        <li><a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a></li>
        <li class="active">Delete Item</li>
    </ol>

    @form(vault/delete)
        @hidden(id $item)
        <p>You are about to delete this vault item. If you proceed, the content will no longer be available on TWHL. Are you sure?</p>
        @submit = Delete Vault Item
    @endform
@endsection

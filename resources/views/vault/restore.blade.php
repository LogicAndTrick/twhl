@title('Restore vault item: '.$item->name)
@extends('app')

@section('content')
    <h1>Restore vault item: {{ $item->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
        <li><a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a></li>
        <li class="active">Restore Item</li>
    </ol>

    @form(vault/restore)
        @hidden(id $item)
        <p>You are about to restore this deleted vault item. This will un-delete the content and make it visible to all users again. Are you sure?</p>
        @submit = Restore Vault Item
    @endform
@endsection

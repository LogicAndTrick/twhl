@title('Delete vault item review for: '.$item->name)
@extends('app')

@section('content')
    <hc>
        <h1>Delete Vault Item Review by @avatar($review->user inline)</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('vault', 'index') }}">Vault</a></li>
            <li><a href="{{ act('vault', 'view', $item->id) }}">{{ $item->name }}</a></li>
            <li class="active">Delete Review</li>
        </ol>
    </hc>

    @form(vault-review/delete)
        @hidden(id $review)
        <p>You are about to delete this review. If you proceed, the review will no longer be available on TWHL. Are you sure?</p>
        @submit = Delete Review
    @endform

@endsection
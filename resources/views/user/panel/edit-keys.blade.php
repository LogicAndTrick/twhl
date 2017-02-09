@title('Manage API keys: '.$user->name)
@extends('app')

@section('content')
    <hc>
        <h1>Manage API keys: {{ $user->name }}</h1>
        <ol class="breadcrumb">
            <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
            <li class="active">Manage API keys</li>
        </ol>
    </hc>

    <h2>Active API Keys</h2>
    <table class="table">
        <tr>
            <th>Key</th>
            <th>Generated</th>
            <th></th>
        </tr>
        @foreach ($user->api_keys as $key)
            <tr>
                <td>
                    <code>{{ $key->key }}</code><br/>
                    <em>Context: {{ $key->app }}</em>
                </td>
                <td>
                    @date($key->created_at)<br>
                    from {{ $key->ip }}
                </td>
                <td>
                    @form(panel/delete-key)
                        @hidden(id $key)
                        <button class="btn btn-danger btn-xs" type="submit">
                            <span class="fa fa-remove"></span>
                            Delete
                        </button>
                    @endform
                </td>
            </tr>
        @endforeach
    </table>

    <h2>Generate a new API key</h2>
    @form(panel/add-key)
        @hidden(id $user)
        @text(app) = Context (what the key will be used for)
        @submit = Create Key
    @endform
@endsection

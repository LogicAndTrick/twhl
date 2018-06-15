@title('Manage permissions: '.$user->name)
@extends('app')

@section('content')
    <h1>Manage permissions: {{ $user->name }}</h1>

    <ol class="breadcrumb">
        <li><a href="{{ act('panel', 'index', $user->id) }}">Control Panel</a></li>
        <li class="active">Manage permissions</li>
    </ol>

    <div class="alert alert-danger">
        <strong>Careful!</strong>
        This is an administration page. Don't change permissions of other admins. That's not nice.
        Also, don't change your own permissions. That's stupid, you already have all the permissions.
    </div>

    <h2>Permissions</h2>
    <table class="table">
        <tr>
            <th>Permission name</th>
            <th>Description</th>
            <th></th>
        </tr>
        @foreach ($permissions as $up)
            <tr>
                <td>{{ $up->permission->name }} {{ $up->permission->is_default ? '(Default)' : '' }}</td>
                <td>{{ $up->permission->description }}</td>
                <td>
                    @form(panel/delete-permission)
                        @hidden(id $up)
                        <button class="btn btn-danger btn-xs" type="submit">
                            <span class="fa fa-remove"></span>
                            Delete
                        </button>
                    @endform
                </td>
            </tr>
        @endforeach
    </table>

    <h2>Add a permission to this user</h2>
    @form(panel/add-permission)
        @hidden(id $user)
        @autocomplete(permission_id api/permissions clearable=true) = Permission to add
        @submit = Add permission
    @endform
@endsection

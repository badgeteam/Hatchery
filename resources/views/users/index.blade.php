@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>Users</strong>
                </div>

                <div class="panel-body">

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Eggs</th>
                                <th>Editor</th>
                                <th>2FA/U2F</th>
                                <th>Last active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td><a href="{{ route('users.show', $user) }}">{{ $user->name }}</a></td>
                                    <td>{{ $user->projects()->count() }}</td>
                                    <td>{{ $user->editor }}</td>
                                    <td>{{ $user->google2fa_enabled ? '2FA' : '' }} {{ $user->webauthnKeys->isEmpty() ? '' : 'U2F' }}</td>
                                    <td>{{ $user->projects()->count() > 0 ? $user->projects->last()->updated_at : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No public users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Profile
                        <div class="pull-right">
                            @can('update', $user)
                            <a class="btn btn-primary btn-xs" href="{{ route('users.edit', ['user' => $user->id])  }}">edit</a>
                            @endcan
                            @can('delete', $user)
                            {!! Form::open(['method' => 'delete', 'route' => ['users.destroy', 'user' => $user->id], 'class' => 'deleteform']) !!}
                            <button class="btn btn-danger btn-xs" name="delete-resource" type="submit" value="delete">delete</button>
                            {!! Form::close() !!}
                            @endcan
                        </div>
                    </div>
                    <div class="panel-body">

                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td>Name:</td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td>Editor:</td>
                                    <td>{{ $user->editor }}</td>
                                </tr>
                                <tr>
                                    <td>2FA/U2F:</td>
                                    <td>{{ $user->google2fa_enabled ? '2FA' : '' }} {{ $user->webauthnKeys->isEmpty() ? '' : 'U2F' }}</td>
                                </tr>
                                <tr>
                                    <td>Last active:</td>
                                    <td>{{ $user->projects()->count() > 0 ? $user->projects->last()->updated_at : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        @if($user->projects()->count() > 0)
                        <h3>Projects:</h3>
                        @include('users.partials.projects')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="confirm-delete" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    Are you sure you want to delete {{ $user->name }}?
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-danger" id="delete">Delete</button>
                    <button type="button" data-dismiss="modal" class="btn">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
			// Delete resource
			$('button[name="delete-resource"]').on('click', function (e) {
				e.preventDefault()
				var $form = $(this).closest('form')
				$('#confirm-delete').modal({ backdrop: 'static', keyboard: false }).one('click', '#delete', function (e) {
					$form.trigger('submit')
				})
			})
    </script>
@endsection
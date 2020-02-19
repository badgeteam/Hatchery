@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Profile
                        @can('delete', $user)
                        <div class="pull-right">
                            {!! Form::open(['method' => 'delete', 'route' => ['users.destroy', 'user' => $user->id]]) !!}
                            <button class="btn btn-danger btn-xs" name="delete-resource" type="submit" value="delete">delete</button>
                            {!! Form::close() !!}
                        </div>
                        @endcan
                    </div>
                    <div class="panel-body">
                        {!! Form::open(['method' => 'put', 'route' => ['users.update', 'user' => $user->id], 'class' => "form-horizontal"]) !!}

                            {{ csrf_field() }}

                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">Name</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name" value="{{ $user->name }}" required autofocus>

                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" value="{{ $user->email }}" required>

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('editor') ? ' has-error' : '' }}">
                                <label for="password" class="col-md-4 control-label">Prefered editor</label>

                                <div class="col-md-6">
                                    <select id="editor" class="form-control" name="editor">
                                        <option value="default" {{ $user->editor == 'default' ? 'selected="selected"' : '' }}>notepad.exe</option>
                                        <option value="vim" {{ $user->editor == 'vim' ? 'selected="selected"' : '' }}>vim</option>
                                        <option value="emacs" {{ $user->editor == 'emacs' ? 'selected="selected"' : '' }}>emacs</option>
                                        <option value="sublime" {{ $user->editor == 'sublime' ? 'selected="selected"' : '' }}>Sublime</option>
                                    </select>

                                    @if ($errors->has('editor'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('editor') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-7 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Save
                                    </button>

                                    {!! Form::close() !!}

                                    <a href="{{ route('webauthn.register') }}" class="btn btn-info">
                                        Add WebAuthn
                                    </a>

                                    <a href="{{ route('2fa') }}" class="btn btn-info">
                                        {{ $user->google2fa_enabled ? 'Manage' : 'Add' }} Authenticator
                                    </a>

                                    @if($user->webauthnKeys()->count() > 0)
                                    <hr>
                                        @foreach($user->webauthnKeys as $key)
                                        {!! Form::open(['method' => 'delete', 'route' => ['webauthn.destroy', 'id' => $key->id]]) !!}
                                            <button class="btn btn-danger btn-xs" name="delete-token" type="submit" value="delete">Delete WebaAuthn token added {{ $key->created_at->format('Y-m-d') }}</button>
                                        {!! Form::close() !!}
                                        @endforeach
                                    @endif
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="col-md-12">
				    <table class="table table-striped">
					<thead>
					    <tr>
						<th>Name</th>
						<th>Revision</th>
						<th>Size of egg</th>
						<th>Size of content</th>
						<th>Category</th>
						<th>Last release</th>
					    </tr>
					</thead>
					<tbody>
					@forelse($user->projects()->paginate() as $project)
					<tr>
					    <td>
						@can('update', $project)
							<a href="{{ route('projects.edit', ['project' => $project->slug]) }}">{{ $project->name }}</a></td>
						@else
							<a href="{{ route('projects.show', ['project' => $project->slug]) }}">{{ $project->name }}</a></td>
						@endcan
					    <td>{{ $project->versions()->published()->count() > 0 ? $project->versions()->published()->get()->last()->revision : 'unreleased' }}</td>
					    <td>{{ $project->size_of_zip }}</td>
					    <td>{{ $project->size_of_content }}</td>
					    <td>{{ $project->category }}</td>
					    <td>{{ $project->versions()->published()->count() > 0 ? $project->versions()->published()->get()->last()->updated_at : '-' }}</td>
					</tr>
				    	@empty
					<tr>
					    <td colspan="6">No Eggs published yet</td>
					</tr>
				    	@endforelse
				</tbody>
			</table>
                    {{ $user->projects()->paginate()->render() }}

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="confirm-delete" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    Are you sure you want to delete yourself?
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-danger" id="delete">Delete</button>
                    <button type="button" data-dismiss="modal" class="btn">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <div id="confirm-delete-token" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    Are you sure you want to delete this token?
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
        window.onload = function() {
            var uploader = new window.Dropzone("#uploader",{
                maxFilesize: 1,
                acceptedFiles: ".{{ implode(',.', \App\Models\File::$extensions)  }}"
            });
            var d = document.getElementById("uploader");
            d.className += " dropzone";
        }

        // Delete resource
        $('button[name="delete-resource"]').on('click', function (e) {
            e.preventDefault()
            var $form = $(this).closest('form')
            $('#confirm-delete').modal({ backdrop: 'static', keyboard: false }).one('click', '#delete', function (e) {
                $form.trigger('submit')
            })
        })

        // Delete resource
        $('button[name="delete-token"]').on('click', function (e) {
          e.preventDefault()
          var $form = $(this).closest('form')
          $('#confirm-delete-token').modal({ backdrop: 'static', keyboard: false }).one('click', '#delete', function (e) {
            $.ajax({
              url: $form.attr('action'),
              type: 'DELETE',
              data: {
                '_token': window.Laravel.csrfToken
              },
              success: function(result) {
                location.reload()
              }
            });
          })
        })
    </script>
@endsection

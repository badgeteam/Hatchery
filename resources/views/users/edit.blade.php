@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Register</div>
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
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Save
                                    </button>
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

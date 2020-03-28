@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-10 col-md-offset-1">

                <div class="panel panel-default">

                    <div class="panel-heading">
                        <strong>{{ $project->name }}</strong>
                        <div class="pull-right">
                            <a href="{{ route('projects.show', ['project' => $project]) }}" class="btn btn-default btn-xs">show</a>
                            @can('update', $project)
                                <a href="{{ route('projects.edit', ['project' => $project]) }}" class="btn btn-info btn-xs">edit</a>
                            @endcan
                            @can('delete', $project)
                                {!! Form::open(['method' => 'delete', 'route' => ['projects.destroy', 'project' => $project->slug], 'class' => 'deleteform']) !!}
                                <button class="btn btn-danger btn-xs" name="delete-resource" type="submit" value="delete">delete</button>
                                {!! Form::close() !!}
                            @endcan
                        </div>
                    </div>

                    <div class="panel-body">
                        <div class="row">
                            {!! Form::open(['method' => 'post', 'route' => ['projects.move', 'project' => $project->slug]]) !!}

                            <div class="col-md-12 clearfix">
                                <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                    <label for="name" class="col-md-4 control-label">Rename project to the following</label>
                                    <div class="col-md-6">
                                        <input id="name" type="text" class="form-control" name="name" value="{{ $project->name }}" required autofocus>
                                        @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 clearfix">
                                <button type="submit" class="btn btn-primary">
                                    Rename
                                </button>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

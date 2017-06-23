@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <ol class="breadcrumb">
                <li><a href="{{ route('projects.index') }}">Eggs</a></li>
                <li class="active">{{ $project->name }}</li>
            </ol>

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>{{ $project->name }}</strong>

                    <div class="pull-right">
                        {!! Form::open(['method' => 'delete', 'route' => ['projects.destroy', 'project' => $project->id]]) !!}
                        <button class="btn btn-danger btn-xs" name="delete-resource" type="submit" value="delete">delete</button>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="panel-body">
                    <div class="row">
                        {!! Form::open(['method' => 'put', 'route' => ['projects.update', 'project' => $project->id]]) !!}

                        <div class="col-md-8 clearfix">

                                <div class="form-group @if($errors->has('description')) has-error @endif">
                                    {{ Form::label('description', 'Description', ['class' => 'control-label']) }}
                                    {{ Form::textarea('description', $project->description, ['class' => 'form-control', 'id' => 'description']) }}
                                </div>
                        </div>
                        <div class="col-md-4 clearfix">
                            @include('projects.partials.dependencies')
                        </div>
                        <div class="col-md-12 clearfix">

                                TODO: Image / screenshot?

                                <div class="pull-right">
                                    {{ Form::label('publish', 'Publish', ['class' => 'control-label']) }}
                                    {{ Form::checkbox('publish', 1, 1, ['id' => 'publish']) }}
                                    <button type="submit" class="btn btn-default">Save</button>
                                </div>

                        </div>
                        {!! Form::close() !!}

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            @include('projects.partials.files')
                        </div>
                        <div class="col-md-6">
                            @include('projects.partials.revisions')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

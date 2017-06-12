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
                    <strong>Add an Egg</strong>
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-12 clearfix">
                            {!! Form::open(['method' => 'put', 'route' => ['projects.update', 'project' => $project->id]]) !!}

                                <div class="form-group @if($errors->has('description')) has-error @endif">
                                    {{ Form::label('description', 'Intro body', ['class' => 'control-label']) }}
                                    {{ Form::textarea('description', $project->description, ['class' => 'form-control', 'id' => 'description']) }}
                                </div>

                                <div class="pull-right">
                                    <button type="submit" class="btn btn-default">Save</button>
                                </div>

                            {!! Form::close() !!}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

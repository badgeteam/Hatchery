@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>{{ $project->name }}</strong>
                    @can('delete', $project)
                    <div class="pull-right">
                        {!! Form::open(['method' => 'delete', 'route' => ['projects.destroy', 'project' => $project->slug]]) !!}
                        <button class="btn btn-danger btn-xs" name="delete-resource" type="submit" value="delete">delete</button>
                        {!! Form::close() !!}
                    </div>
                    @endcan
                </div>

                <div class="panel-body">
                    <div class="row">
                        {!! Form::open(['method' => 'put', 'route' => ['projects.update', 'project' => $project->slug]]) !!}

                        <div class="col-md-8 clearfix">
                                <div class="form-group @if($errors->has('description')) has-error @endif">
                                    {{ Form::label('description', 'Description', ['class' => 'control-label']) }}
                                    {{ Form::textarea('description', $project->description, ['class' => 'form-control', 'id' => 'description']) }}
                                </div>
                        </div>
                        <div class="col-md-4 clearfix">
                            <div class="form-group @if($errors->has('category_id')) has-error @endif">
                                {{ Form::label('category_id', 'Category', ['class' => 'control-label']) }}
                                {{ Form::select('category_id', \App\Models\Category::where('hidden', false)->pluck('name', 'id'), $project->category_id, ['class' => 'form-control', 'id' => 'category_id']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::label('status', 'Status', ['class' => 'control-label']) }}
                                {{ Form::select('status', ['working' => 'Working', 'in_progress' => 'In Progress', 'broken' => 'Broken'], $project->status, ['class' => 'form-control', 'id' => 'status']) }}
                            </div>

                            <div class="form-group @if($errors->has('badge_ids')) has-error @endif">
                                {{ Form::label('badge_ids', 'Compatibility', ['class' => 'control-label']) }}
                                {{ Form::select('badge_ids[]', \App\Models\Badge::pluck('name', 'id'), $project->badges()->pluck('badges.id'), ['multiple' => 'multiple', 'class' => 'form-control', 'id' => 'badge_ids']) }}
                            </div>

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

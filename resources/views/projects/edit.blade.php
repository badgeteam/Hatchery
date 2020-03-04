@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    @if($project->git)
                        <img src="{{ asset('img/git.svg') }}" alt="Git revision: {{ $project->git_commit_id}}" class="collab-icon" />
                    @endif
                    @if(!$project->collaborators->isEmpty())
                        <img src="{{ asset('img/collab.svg') }}" alt="{{ $project->collaborators()->count() . ' ' . \Illuminate\Support\Str::plural('collaborator', $project->collaborators()->count()) }}" class="collab-icon" />
                    @endif
                    <strong>{{ $project->name }}</strong>
                    <div class="pull-right">
                        <a href="{{ route('projects.show', ['project' => $project]) }}" class="btn btn-default btn-xs">show</a>
                        @can('rename', $project)
                        <a href="{{ route('projects.rename', ['project' => $project]) }}" class="btn btn-info btn-xs">rename</a>
                        @endcan
                        @can('pull', $project)
                        <a href="{{ route('projects.pull', ['project' => $project]) }}" class="btn btn-success btn-xs">update</a>
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
                        {!! Form::open(['method' => 'put', 'route' => ['projects.update', 'project' => $project->slug]]) !!}

                        <div class="col-md-8 clearfix">
                            <div class="form-group">
                                {!! $project->descriptionHtml !!}
                            </div>
                            @if($project->versions->last()->files()->where('name', 'README.md')->exists())
                            <a class="btn btn-success btn-xs" href="{{ route('files.edit', $project->versions->last()->files()->where('name', 'README.md')->first()) }}">Edit README.md</a>
                            @else
                            <a class="btn btn-success btn-xs" href="{{ route('files.create', ['version' => $project->versions->last()->id, 'name' => 'README.md']) }}">Create README.md</a>
                            @endif
                        </div>
                        <div class="col-md-4 clearfix">
                            <div class="form-group @if($errors->has('category_id')) has-error @endif">
                                {{ Form::label('category_id', 'Category', ['class' => 'control-label']) }}
                                {{ Form::select('category_id', \App\Models\Category::where('hidden', false)->pluck('name', 'id'), $project->category_id, ['class' => 'form-control', 'id' => 'category_id']) }}
                            </div>

                            @include('projects.partials.compatibility')
                            @include('projects.partials.dependencies')
                            @include('projects.partials.collaborators')

                        </div>
                        <div class="col-md-12 clearfix">

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

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
                        <button class="btn btn-danger btn-xs" name="delete-resource" type="submit" data-bs-toggle="modal" data-bs-target="#confirm-delete" value="delete">delete</button>
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
                            <div class="form-group @if($errors->has('project_type')) has-error @endif">
                                {{ Form::label('project_type', 'Type', ['class' => 'control-label']) }}
                                {{ Form::select('project_type', \App\Models\Badge::$types, $project->project_type, ['class' => 'form-control', 'id' => 'badge_ids']) }}
                            </div>
                            <div class="form-group @if($errors->has('license')) has-error @endif">
                                {{ Form::label('license', 'License', ['class' => 'control-label']) }}
                                {{ Form::select('license', \App\Models\License::where('isDeprecatedLicenseId', 0)->where('isOsiApproved', 1)->pluck('name', 'licenseId'), $project->license, ['class' => 'form-control', 'id' => 'license']) }}
                            </div>
                            <div class="form-group @if($errors->has('min_firmware') || $errors->has('max_firmware')) has-error @endif">
                                {{ Form::label('min_firmware', 'Minimal firmware version', ['class' => 'control-label']) }}
                                {{ Form::text('min_firmware', $project->min_firmware, ['class' => 'form-control', 'id' => 'min_firmware']) }}
                                {{ Form::label('max_firmware', 'Maximum firmware version', ['class' => 'control-label']) }}
                                {{ Form::text('max_firmware', $project->max_firmware, ['class' => 'form-control', 'id' => 'max_firmware']) }}
                            </div>
                            @include('projects.partials.compatibility')
                            @include('projects.partials.dependencies')
                            @include('projects.partials.collaborators')
                            <div class="form-group @if($errors->has('allow_team_fixes')) has-error @endif">
                                {{ Form::label('allow_team_fixes', 'Allow badge.team to apply fixes to code', ['class' => 'control-label']) }}
                                {{ Form::checkbox('allow_team_fixes', $project->allow_team_fixes, ['class' => 'form-control', 'id' => 'allow_team_fixes']) }}
                            </div>

                        </div>
                        <div class="col-md-12 clearfix">

                                <div class="pull-right">
                                    {{ Form::label('publish', 'Publish', ['class' => 'control-label']) }}
                                    {{ Form::checkbox('publish', 1, null, ['id' => 'publish']) }}
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

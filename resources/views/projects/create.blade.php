@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>Add an Egg</strong>
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-12 clearfix">
                            {!! Form::open(['method' => 'post', 'route' => [$type === 'import' ? 'projects.import.git' : 'projects.store']]) !!}
                                <div class="form-group @if($errors->has('category_id')) has-error @endif">
                                    {{ Form::label('category_id', 'Category', ['class' => 'control-label']) }}
                                    {{ Form::select('category_id', \App\Models\Category::where('hidden', false)->pluck('name', 'id'), 0, ['class' => 'form-control', 'id' => 'category_id']) }}
                                </div>

                                @if(\App\Models\Badge::count() > 1)
                                <div class="form-group @if($errors->has('badge_ids')) has-error @endif">
                                    {{ Form::label('badge_ids', 'Compatibility', ['class' => 'control-label']) }}
                                    {{ Form::select('badge_ids[]', \App\Models\Badge::pluck('name', 'id')->reverse(), 0, ['multiple' => 'multiple', 'class' => 'form-control', 'id' => 'badge_ids']) }}
                                </div>
                                @else
                                {{ Form::hidden('badge_ids[]', \App\Models\Badge::first()->id, ['id' => 'badge_ids']) }}
                                @endif

                                <div class="form-group @if($errors->has('project_type')) has-error @endif">
                                    {{ Form::label('project_type', 'Type', ['class' => 'control-label']) }}
                                    {{ Form::select('project_type', \App\Models\Badge::$types, 'python', ['class' => 'form-control', 'id' => 'badge_ids']) }}
                                </div>

                                <div class="form-group @if($errors->has('name')) has-error @endif">
                                    {{ Form::label('name', 'Name', ['class' => 'control-label']) }} (unique)
                                    {{ Form::text('name', null, ['class' => 'form-control', 'id' => 'name']) }}
                                </div>

                                @if ($type === 'import')
                                <div class="form-group @if($errors->has('git')) has-error @endif">
                                    {{ Form::label('git', 'Git repository', ['class' => 'control-label']) }} (url)
                                    {{ Form::text('git', null, ['class' => 'form-control', 'id' => 'git']) }}
                                </div>
                                @else

                                <div class="form-group @if($errors->has('description')) has-error @endif">
                                    {{ Form::label('description', 'Description', ['class' => 'control-label']) }} (markdown)
                                    {{ Form::textarea('description', null, ['class' => 'form-control', 'id' => 'content']) }}
                                    {{ Form::hidden('extension', 'md', ['id' => 'extension']) }}
                                </div>
                                @endif

                                <div class="form-group @if($errors->has('license')) has-error @endif">
                                    {{ Form::label('license', 'License', ['class' => 'control-label']) }}
                                    {{ Form::select('license', \App\Models\License::where('isDeprecatedLicenseId', 0)->where('isOsiApproved', 1)->pluck('name', 'licenseId'), 'MIT', ['class' => 'form-control', 'id' => 'license']) }}
                                </div>

                                <div class="form-group @if($errors->has('allow_team_fixes')) has-error @endif">
                                    {{ Form::label('allow_team_fixes', 'Allow badge.team to apply fixes to code', ['class' => 'control-label']) }}
                                    {{ Form::checkbox('allow_team_fixes', true, ['class' => 'form-control', 'id' => 'allow_team_fixes']) }}
                                </div>

                                <input name="status" type="hidden" value="3" />

                                <div class="pull-right">
                                    <button type="submit" class="btn btn-default">{{ $type === 'import' ? 'Import' : 'Save' }}</button>
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
@section('script')
    <script>
			window.keymap = "{{ Auth::user()->editor }}";
    </script>
@endsection

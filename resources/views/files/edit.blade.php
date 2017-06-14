@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <ol class="breadcrumb">
                <li><a href="{{ route('projects.index') }}">Eggs</a></li>
                <li><a href="{{ route('projects.edit', ['project' => $file->version->project->id]) }}">{{ $file->version->project->name }}</a></li>
                <li class="active">{{ $file->name }}</li>
            </ol>

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>{{ $file->name }}</strong>
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-12 clearfix">
                            <pre>{!! $file->content !!}</pre>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

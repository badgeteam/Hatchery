@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>{{ $project->name }}</strong> rev. {{ $project->revision }} (by {{ $project->user->name }})
                    @can('update', $project)
                    <div class="pull-right">
                        <a class="btn btn-primary btn-xs" href="{{ route('projects.edit', ['project' => $project->slug])  }}">edit</a>
                    </div>
                    @endcan
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-8 clearfix">
                            @foreach(explode("\n", $project->description) as $line)
                                {{$line}}<br/>
                            @endforeach
                        </div>
                        <div class="col-md-4 clearfix">
                            @include('projects.partials.show-dependencies')
                        </div>
                        <div class="col-md-12 clearfix">
                            @include('projects.partials.show-files')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

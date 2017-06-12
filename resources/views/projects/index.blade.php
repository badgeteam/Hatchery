@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <ol class="breadcrumb">
                <li class="active">Eggs</li>
            </ol>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>Eggs</strong>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Revision</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                                <tr>
                                    <td><a href="{{ route('projects.edit', ['project' => $project->id]) }}">{{ $project->name }}</a></td>
                                    <td>{{ !$project->versions->isEmpty() ? $project->versions->last()->revision : 'unpublished' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No Eggs published yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="pull-right">
                        <a href="{{ route('projects.create') }}" class="btn btn-default">Add</a>
                    </div>

                    {{ $projects->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

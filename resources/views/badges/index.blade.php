@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>Badges</strong>
                    <div class="pull-right">
                    @can('create', \App\Models\Badge::class)
                        <a class="btn btn-success btn-xs" href="{{ route('badges.create')  }}">create</a>
                    @endcan
                    </div>
                </div>

                <div class="panel-body">

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Projects</th>
                                <th>Processes</th>
                                <th>Created at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($badges as $badge)
                                <tr>
                                    <td><a href="{{ route('badges.show', $badge) }}">{{ $badge->name }}</a></td>
                                    <td>{{ $badge->projects()->count() }}</td>
                                    <td>{!! $badge->commands ? '<span class="u2f">Commands</span>' : ''  !!} {!! $badge->constraints ? '<span class="u2f">Constraints</span>' : '' !!}</td>
                                    <td>{{ $badge->created_at }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No basdges found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $badges->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

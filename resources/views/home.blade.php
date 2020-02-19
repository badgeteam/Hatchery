@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>
                <div class="panel-body">
                    <div>
                        Contributors: {{$users}}
                        Eggs: {{$projects}}
                    </div>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Badge</th>
                            <th>Eggs</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach(App\Models\Badge::all() as $badge)
                            <tr>
                                <td>{{ $badge->name }}</td>
                                <td>{{ $badge->projects->count() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

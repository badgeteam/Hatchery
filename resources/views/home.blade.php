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
                    <div>
                        @foreach(App\Models\Badge::all() as $badge)
                            {{ $badge->name }}: {{ $badge->projects->count() }}
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

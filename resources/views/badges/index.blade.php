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
                    @include('partials.badges')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('errors::minimal')

@section('title', __('Not Found'))
@section('code', '404')
@section('message', __('Not Found'))

@section('more')
    <div class="flex-center position-ref full-height">
        <ul>
            <li><a href="{{ route('home') }}">Hatchery</a></li>
            <li><a href="{{ route('login') }}">Login</a></li>
            <li><a href="{{ route('register') }}">Register</a></li>
            <li><a href="{{ route('badges.index') }}">Badges</a>
                <ul>
                @foreach(\App\Models\Badge::all() as $badge)
                    <li><a href="{{ route('badges.show', $badge) }}">{{ $badge->name }}</a></li>
                @endforeach
                </ul>
            </li>
            <li><a href="{{ route('projects.index') }}">Projects</a>
                <ul>
                @foreach(\App\Models\Project::all() as $project)
                    <li><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></li>
                @endforeach
                </ul>
            </li>
            <li><a href="{{ route('users.index') }}">Users</a>
                <ul>
                    @foreach(\App\Models\User::wherePublic(true)->get() as $user)
                        <li><a href="{{ route('users.show', $user) }}">{{ $user->name }}</a></li>
                    @endforeach
                </ul>
            </li>
        </ul>
    </div>
@endsection
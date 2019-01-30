@extends('layouts.app_splash')

@section('content')
<div class="container">
    <div class="row">
            <div class="panel panel-compact">
                <div class="panel-body">

		    @if (Route::has('login'))
			<div class="pull-right links">
			    @if (Auth::check())
				<a href="{{ url('/projects') }}">Eggs</a>
			    @else
				<a href="{{ url('/login') }}">Login</a>
				<a href="{{ url('/register') }}">Register</a>
			    @endif
			</div>
		    @endif

		    <div class="content text-center">
			<div class="title m-b-md">
			    <h1 class="hatcher"><span class="hidden-xs">Badge.team</span> {{ config('app.name', 'Laravel') }}</h1>
			</div>
			<div>
			    Contributors: {{$users}}
			    Eggs: {{$projects}}
			</div>
			<div class="spacer col-md-12 hidden-xs"></div>
			<div class="links">
			    <a href="https://wiki.badge.team/MicroPython">Wiki Coding Help</a>
			    <a href="https://github.com/badgeteam/">GitHub</a>
			    <a href="https://twitter.com/SHA2017Badge">Twitter</a>
			</div>
			<div class="spacer col-md-12 hidden-xs"></div>
			<table class="table table-condensed">
			    <thead>
			    <tr>
					<th>Name</th>
					<th>Revision</th>
					<th>Size of zip</th>
					<th>Size of content</th>
					<th>Category</th>
					<th>Last release</th>
			    </tr>
			    </thead>
			    <tbody>
			    @forelse($published as $project)
				<tr>
				    <td><a href="{{ route('projects.show', ['project' => $project->slug]) }}">{{ $project->name }}</a></td>
				    <td>{{ $project->revision }}</td>
				    <td>{{ $project->size_of_zip }}</td>
				    <td>{{ $project->size_of_content }}</td>
					<td>{{ $project->category }}</td>

					<td>{{ $project->versions()->published()->get()->last()->updated_at->diffForHumans() }}</td>
				</tr>
			    @empty
				<tr><td>No published eggs</td></tr>
			    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection

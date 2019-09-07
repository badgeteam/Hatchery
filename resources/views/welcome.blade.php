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
				<div class="spacer col-md-12 hidden-xs"></div>
				<div class="links">
					<a href="https://docs.badge.team/">Documentation</a>
					<a href="https://wiki.badge.team/MicroPython">Wiki Coding Help</a>
					<a href="https://github.com/badgeteam/">GitHub</a>
					<a href="https://twitter.com/SHA2017Badge">Twitter</a>
				</div>
				<div>
					Contributors: {{$users}}
					Eggs: {{$projects}}
				</div>
				<div>
					@foreach(App\Models\Badge::all() as $selectBadge)
						{{ $selectBadge->name }}: {{ $selectBadge->projects->count() }}
					@endforeach
				</div>

				<div class="spacer col-md-12 hidden-xs"></div>
				{{ Form::select('badge_id', \App\Models\Badge::pluck('name', 'slug')->reverse()->prepend('Choose a badge model', ''), $badge, ['id' => 'badge']) }}
				{{ Form::select('category_id', \App\Models\Category::where('hidden', false)->pluck('name', 'slug')->reverse()->prepend('Choose a category', ''), $category, ['id' => 'category']) }}
				{{ Form::open(['method' => 'post', 'route' => ['projects.search', 'badge' => $badge, 'category' => $category], 'class' => 'searchform'])  }}
					{{ Form::text('search', null, ['placeholder' => 'Search']) }}
				{{ Form::close() }}
				<table class="table table-condensed">
						<thead>
					<tr>
						<th>Name</th>
						<th>Revision</th>
						<th>Size of zip</th>
						<th>Size of content</th>
						<th>Category</th>
						<th><img src="{{ asset('img/rulez.gif') }}" alt="up" /></th>
						<th><img src="{{ asset('img/isok.gif') }}" alt="pig" /></th>
						<th><img src="{{ asset('img/sucks.gif') }}" alt="down" /></th>
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
						<td>{{ $project->votes->where('type', 'up')->count() }}</td>
						<td>{{ $project->votes->where('type', 'pig')->count() }}</td>
						<td>{{ $project->votes->where('type', 'down')->count() }}</td>
						<td>{{ $project->versions()->published()->get()->last()->updated_at->diffForHumans() }}</td>
					</tr>
					@empty
					<tr><td>No published eggs</td></tr>
					@endforelse
						</tbody>
					</table>
					@if ($badge && $category)
						{{ $published->appends(['badge' => $badge, 'category' => $category])->links() }}
					@elseif ($badge)
						{{ $published->appends(['badge' => $badge])->links() }}
					@elseif ($category)
						{{ $published->appends(['category' => $category])->links() }}
					@else
						{{ $published->links() }}
					@endif
				</div>
			</div>
        </div>
    </div>
</div>
@endsection

@section('script')
	<script>
		$(document).ready(function () {
			$('#badge').change(function () {
				if ($(this).val()) {
					window.location.href = '{{ route('splash') }}/badge/' + $(this).val() {!! $category ? " + '?category=$category'" : "" !!};
				} else {
					window.location.href = '{{ route('splash') . ($category ? "?category=$category" : "") }}';
				}
			})
			$('#category').change(function () {
				if ($(this).val()) {
					window.location.href = '{{ url()->current() }}?category=' + $(this).val();
				} else {
					window.location.href = '{{ url()->current() }}';
				}
			})
		})
	</script>
@endsection
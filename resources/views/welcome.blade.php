@extends('layouts.app_splash')

@section('content')
<div class="container">
    <div class="row">
		<div class="panel panel-compact">
			<div class="panel-body">
		    @if (Route::has('login'))
			<div class="pull-right links">
			    @if (Auth::check())
				<a href="{{ route('projects.index') }}">Eggs</a>
				<a href="{{ route('users.index') }}">Users</a>
				<a href="{{ route('badges.index') }}">Badges</a>
			    @else
				<a href="{{ url('/login') }}">Login</a>
				<a href="{{ url('/register') }}">Register</a>
			    @endif
			</div>
		    @endif
		    <div class="content text-center">
				<div class="title m-b-md">
					<h1 class="hatcher"><span class="hidden-xs small">{{ request()->getHost() }}</span> {{ config('app.name', 'Hatchery') }}</h1>
				</div>
				<div class="spacer col-md-12 hidden-xs"></div>
				<div class="links">
					<a class="btn btn-success" href="https://docs.badge.team/">Documentation</a>
					<a class="btn btn-danger" href="https://github.com/badgeteam/">GitHub</a>
					<a class="btn btn-info" href="https://twitter.com/BadgeteamNL">Twitter</a>
					<a class="btn btn-primary" href="https://t.me/joinchat/AMG-ZhQQ9cE1KAAbQozy5Q">Telegram</a>
				</div>
				<div>
					Contributors: {{$users}}
					Eggs: {{$projects}}
				</div>
				<div class="spacer col-md-12 hidden-xs"></div>
					{{ Form::label('badge', 'Badge', ['class' => 'control-label']) }}
					{{ Form::select('badge_id', \App\Models\Badge::pluck('name', 'slug')->reverse()->prepend('Choose a badge model', ''), $badge, ['id' => 'badge']) }}
					<br class="hidden-sm-up">
					{{ Form::label('category', 'Category', ['class' => 'control-label']) }}
					{{ Form::select('category_id', \App\Models\Category::where('hidden', false)->pluck('name', 'slug')->reverse()->prepend('Choose a category', ''), $category, ['id' => 'category']) }}
					{{ Form::open(['method' => 'post', 'route' => ['projects.search', 'badge' => $badge, 'category' => $category], 'class' => 'searchform'])  }}
						{{ Form::label('search', 'Search', ['class' => 'control-label']) }}
						{{ Form::text('search', null, ['placeholder' => 'Search']) }}
					{{ Form::close() }}
					<hr>
					<table class="table table-condensed">
						<thead>
							<tr>
								<th>
									<a href="{{ Request::fullUrlWithQuery(['order' => 'name', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
										Name
										@if($order === 'name')
											{{$direction === 'desc' ? '↓' : '↑'}}
										@endif
									</a>
								</th>
								<th>Rev</th>
								<th>Egg</th>
								<th class="hidden-xs">Content</th>
								<th class="hidden-xs">Category</th>
								<th>Collab</th>
								<th class="hidden-xs"><img src="{{ asset('img/rulez.gif') }}" alt="up" /></th>
								<th class="hidden-xs"><img src="{{ asset('img/isok.gif') }}" alt="pig" /></th>
								<th class="hidden-xs"><img src="{{ asset('img/sucks.gif') }}" alt="down" /></th>
								<th class="hidden-xs"><img src="{{ asset('img/alert.gif') }}" alt="alert" /></th>
								<th>
									<a href="{{ Request::fullUrlWithQuery(['order' => 'published_at', 'direction' => $direction === 'asc' ? 'desc' : 'asc']) }}">
										Last release
										@if($order === 'published_at')
											{{$direction === 'desc' ? '↓' : '↑'}}
										@endif
									</a>
								</th>
							</tr>
						</thead>
						<tbody>
						@forelse($published as $project)
							<tr>
								<td><a href="{{ route('projects.show', ['project' => $project->slug]) }}">{{ $project->name }}</a></td>
								<td>{{ $project->revision }}</td>
								<td>{{ $project->size_of_zip_formatted }}</td>
								<td class="hidden-xs">{{ $project->size_of_content_formatted }}</td>
								<td class="hidden-xs">{{ $project->category }}</td>
								<td>
								@if($project->git)
									<img src="{{ asset('img/git.svg') }}" alt="Git revision: {{ $project->git_commit_id}}" class="collab-icon" />
								@endif
								@if(!$project->collaborators->isEmpty())
									<img src="{{ asset('img/collab.svg') }}" alt="{{ $project->collaborators()->count() . ' ' . \Illuminate\Support\Str::plural('collaborator', $project->collaborators()->count()) }}" class="collab-icon" />
								@endif
								</td>
								<td class="hidden-xs">{{ $project->votes->where('type', 'up')->count() }}</td>
								<td class="hidden-xs">{{ $project->votes->where('type', 'pig')->count() }}</td>
								<td class="hidden-xs">{{ $project->votes->where('type', 'down')->count() }}</td>
								<td class="hidden-xs">{{ $project->warnings->count() }}</td>
								<td>{{ $project->published_at->diffForHumans() }}</td>
							</tr>
						@empty
							<tr><td>No published eggs</td></tr>
						@endforelse
						</tbody>
					</table>
					@if ($appends)
						{{ $published->appends($appends)->links() }}
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

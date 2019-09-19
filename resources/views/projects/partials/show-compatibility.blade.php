Compatibility
<ul>
    @forelse($project->states as $state)
        <li>{{ $state->badge->name }}: {{ $state->status }}</li>
    @empty
        <li>No badges found</li>
    @endforelse
</ul>

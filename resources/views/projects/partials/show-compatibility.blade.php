Compatibility
<ul>
    @forelse($project->badges as $badge)
        <li>{{ $badge->name }}</li>
    @empty
        <li>No badges found</li>
    @endforelse
</ul>

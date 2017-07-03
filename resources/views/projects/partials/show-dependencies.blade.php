Dependencies
<ul>
    @forelse($project->dependencies as $dependency)
        <li><a href="{{ route('projects.show', ['project' => $dependency->slug]) }}">{{ $dependency->name }}</a></li>
    @empty
        <li>No dependencies found</li>
    @endforelse
</ul>
Dependants
<ul>
@forelse($project->dependants as $dependant)
    <li><a href="{{ route('projects.show', ['project' => $dependant->slug]) }}">{{ $dependant->name }}</a></li>
@empty
    <li>No dependants found</li>
@endforelse
</ul>

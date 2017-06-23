@forelse($project->dependencies as $dependency)
    Dependencies
    <ul>
        <li><a href="{{ route('projects.edit', ['project' => $dependency->id]) }}">{{ $dependency->name }}</a></li>
    </ul>
    TODO: Add button!
@empty
    <p>No dependencies set</p>
@endforelse

@forelse($project->dependants as $dependant)
    Dependants
    <ul>
        <li><a href="{{ route('projects.edit', ['project' => $dependant->id]) }}">{{ $dependant->name }}</a></li>
    </ul>
@empty
    <p>No dependants found</p>
@endforelse

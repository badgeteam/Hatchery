{{ Form::label('dependencies', 'Dependencies', ['class' => 'control-label']) }}
<select multiple="multiple" name="dependencies[]" id="dependencies" class="form-control">
    @foreach(App\Models\Project::whereHas('versions', function ($query) {
                $query->published();
            })->get()->reverse() as $dep)
        @if($dep->id !== $project->id && !$project->dependants->contains($dep->id))
        <option value="{{$dep->id}}" @if($project->dependencies->contains($dep->id))selected="selected"@endif>{{$dep->name}}</option>
        @endif
    @endforeach
</select>

Dependants
<ul>
@forelse($project->dependants as $dependant)
    <li>
        @can('update', $dependant)
        <a href="{{ route('projects.edit', ['project' => $dependant->slug]) }}">{{ $dependant->name }}</a>
        @else
        <a href="{{ route('projects.show', ['project' => $dependant->slug]) }}">{{ $dependant->name }}</a>
        @endcan
    </li>
@empty
    <li>No dependants found</li>
@endforelse
</ul>

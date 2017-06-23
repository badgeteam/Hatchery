{{ Form::label('dependencies', 'Dependencies', ['class' => 'control-label']) }}
<select multiple="multiple" name="dependencies[]" id="dependencies" class="form-control">
    @foreach(App\Models\Project::whereHas('versions', function ($query) {
                $query->published();
            })->get() as $dep)
        @if($dep->id !== $project->id && !$project->dependants->contains($dep->id))
        <option value="{{$dep->id}}" @if($project->dependencies->contains($dep->id))selected="selected"@endif>{{$dep->name}}</option>
        @endif
    @endforeach
</select>


@forelse($project->dependants as $dependant)
    Dependants
    <ul>
        <li><a href="{{ route('projects.edit', ['project' => $dependant->id]) }}">{{ $dependant->name }}</a></li>
    </ul>
@empty
    <p>No dependants found</p>
@endforelse

{{ Form::label('collaborators', 'Collaborators', ['class' => 'control-label']) }}
<select multiple="multiple" name="collaborators[]" id="collaborators" class="form-control">
    @foreach(App\Models\User::wherePublic(true)->get() as $collaborator)
        @if($collaborator->id !== $project->user->id)
            <option value="{{$collaborator->id}}" @if($project->collaborators->contains($collaborator->id))selected="selected"@endif>{{$collaborator->name}}</option>
        @endif
    @endforeach
</select>
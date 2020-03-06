@if($project->user->public)
    <strong>Author: <a href="{{ route('users.show', $project->user) }}">{{ $project->user->name }}</a></strong>
    <br><br>
@endif
@if($project->collaborators()->count() > 0)
    <strong>Collaborators</strong>
    <ul>
        @foreach($project->collaborators as $user)
            <li><a href="{{ route('users.show', $user) }}">{{ $user->name }}</a></li>
        @endforeach
    </ul>
@endif
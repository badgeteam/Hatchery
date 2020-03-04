<div class="form-group">
    <div class="col-md-12">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Name</th>
                <th>Rev</th>
                <th>Egg</th>
                <th>Content</th>
                <th>Cat</th>
                <th>Colab</th>
                <th>Last release</th>
            </tr>
            </thead>
            <tbody>
            @forelse($projects as $project)
                <tr>
                    <td>
                    @can('update', $project)
                        <a href="{{ route('projects.edit', ['project' => $project->slug]) }}">{{ $project->name }}</a>
                    @else
                        <a href="{{ route('projects.show', ['project' => $project->slug]) }}">{{ $project->name }}</a>
                    @endcan
                    </td>
                    <td>{{ $project->versions()->published()->exists() ? $project->versions()->published()->get()->last()->revision : 'unreleased' }}</td>
                    <td>{{ $project->size_of_zip_formatted }}</td>
                    <td>{{ $project->size_of_content_formatted }}</td>
                    <td>{{ $project->category }}</td>
                    <td>
                    @if($project->git)
                        <img src="{{ asset('img/git.png') }}" alt="Git revision: {{ $project->git_commit_id}}" />
                    @endif
                    </td>
                    <td>{{ $project->versions()->published()->exists() ? $project->versions()->published()->get()->last()->updated_at : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No Eggs published yet</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        {{ $projects }}
    </div>
</div>
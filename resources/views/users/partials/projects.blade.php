<div class="form-group">
    <div class="col-md-12">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Name</th>
                <th>Revision</th>
                <th>Size of egg</th>
                <th>Size of content</th>
                <th>Category</th>
                <th>Last release</th>
            </tr>
            </thead>
            <tbody>
            @forelse($projects as $project)
                <tr>
                    <td>
                        @can('update', $project)
                            <a href="{{ route('projects.edit', ['project' => $project->slug]) }}">{{ $project->name }}</a></td>
                    @else
                        <a href="{{ route('projects.show', ['project' => $project->slug]) }}">{{ $project->name }}</a></td>
                    @endcan
                    <td>{{ $project->versions()->published()->exists() ? $project->versions()->published()->get()->last()->revision : 'unreleased' }}</td>
                    <td>{{ $project->size_of_zip }}</td>
                    <td>{{ $project->size_of_content }}</td>
                    <td>{{ $project->category }}</td>
                    <td>{{ $project->versions()->published()->exists() ? $project->versions()->published()->get()->last()->updated_at : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No Eggs published yet</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        {{ $projects }}
    </div>
</div>
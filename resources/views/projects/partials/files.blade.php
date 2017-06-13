<table class="table table-striped">
    <thead>
    <tr>
        <th>File</th>
        <th>Last edited</th>
    </tr>
    </thead>
    <tbody>
    @forelse($project->versions->last()->files as $file)
        <tr>
            <td>
                @if($file->editable)<a href="#">@endif
                    {{ $file->name }}
                @if($file->editable)</a>@endif
            </td>
            <td>{{ $file->updated_at }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6">No files yet</td>
        </tr>
    @endforelse
    </tbody>
</table>

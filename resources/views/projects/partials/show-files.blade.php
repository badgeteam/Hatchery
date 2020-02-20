<table class="table table-striped">
    <thead>
    <tr>
        <th>File</th>
        <th>Last edited</th>
        <th>Size in bytes</th>
    </tr>
    </thead>
    <tbody>
    @forelse($project->versions->last()->files()->paginate() as $file)
        <tr>
            <td>
                @if($file->editable)
                <a href="{{ route('files.show', ['file' => $file->id]) }}">{{ $file->name }}</a>
                @else
                <a href="{{ route('files.download', ['file' => $file->id]) }}">{{ $file->name }}</a>
                @endif
            </td>
            <td>{{ $file->updated_at }}</td>
            <td>{{ $file->size_of_content }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="6">No files yet</td>
        </tr>
    @endforelse
    </tbody>
</table>
{{ $project->versions->last()->files()->paginate()->render() }}
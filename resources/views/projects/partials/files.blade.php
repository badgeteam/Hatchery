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
                @if($file->editable)
                <a href="{{ route('files.edit', ['file' => $file->id]) }}">{{ $file->name }}</a>
                @else
                {{ $file->name }}
                @endif
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
<div>Upload Python, Text or PNG image files.</div>
{!! Form::open([ 'route' => [ 'files.upload', 'version' => $project->versions->last()->id ], 'files' => true, 'enctype' => 'multipart/form-data', 'id' => 'uploader' ]) !!}
    <div class="fallback">
        <input name="file" type="file" />
        <input type="submit" />
    </div>
{!! Form::close() !!}

@section('script')
<script type="text/javascript">
    window.onload = function() {
        var uploader = new window.Dropzone("#uploader",{
            maxFilesize: 1,
            acceptedFiles: ".{{ implode(',.', \App\Models\File::$extensions)  }}"
        });
        var d = document.getElementById("uploader");
        d.className += " dropzone";
    }
</script>
@endsection
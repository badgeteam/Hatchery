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
<div>Upload Python, text or image files.</div>
{!! Form::open([ 'route' => [ 'files.store', 'version' => $project->versions->last()->id ], 'files' => true, 'enctype' => 'multipart/form-data', 'id' => 'uploader' ]) !!}
<div>
    <h3>Upload files by dropping them here or clicking on the box</h3>
    <div class="fallback">
        <input name="file" type="file" />
        <input type="submit" />
    </div>
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
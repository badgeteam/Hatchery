@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>{{ $file->name }}</strong>
                    <div class="pull-right">
                        @if($file->editable)
                        @can('update', $file)
                        <a class="btn btn-primary btn-xs" href="{{ route('files.edit', $file)  }}">edit</a>
                        @endcan
                        @endif
                        <a class="btn btn-info btn-xs" href="{{ route('files.download', $file)  }}">raw</a>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-12 clearfix">
                            @if($file->editable)
                            <div class="form-group">
                                {{ Form::label('content-readonly', 'Content', ['class' => 'control-label']) }}
                                {{ Form::textarea('file_content', $file->content, ['class' => 'form-control', 'id' => 'content-readonly']) }}
                                {{ Form::hidden('extension', $file->extension, ['id' => 'extension']) }}
                            </div>
                            @elseif($file->viewable)
                                @if ($file->viewable === 'image')
                                    <img src="{{ route('files.download', $file) }}" alt="{{ $file->name }}" />
                                @elseif ($file->viewable === 'audio')
                                    <audio controls>
                                        <source src="{{ route('files.download', $file) }}" type="{{ $file->mime }}">
                                    </audio>
                                @else
                                    This type needs a player!!
                                @endif
                            @else
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>This file is unfortunately not viewable.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>File size:</td>
                                            <td>{{ \App\Support\Helpers::formatBytes($file->size_of_content) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Mime:</td>
                                            <td>{{ $file->mime }}</td>
                                        </tr>
                                        <tr>
                                            <td>Download:</td>
                                            <td> <a class="btn btn-success" href="{{ route('files.download', $file)  }}">{{ $file->name }}</a></td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>

                    </div>
                    @if($file->name === 'icon.py')
                    <div class="row" id="pixels">
                        <div class="col-md-4">
                            <table>
                                @for($r=0; $r < 8; $r++)
                                <tr id="row{{ $r }}">
                                    @for($p=0; $p < 8; $p++)
                                    <td id="row{{$r}}pixel{{$p}}"></td>
                                    @endfor
                                </tr>
                                @endfor
                            </table>
                        </div>
                        <div class="col-md-8">
                            <div id="frames">
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    @auth
    <script>
			window.keymap = "{{ Auth::user()->editor }}";
    </script>
    @endauth
@endsection

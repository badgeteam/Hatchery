@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>{{ $file->name }}</strong>
                    @can('update', $file)
                        <div class="pull-right">
                            <a class="btn btn-primary btn-xs" href="{{ route('files.edit', ['file' => $file->id])  }}">edit</a>
                        </div>
                    @endcan
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-12 clearfix">

                            <div class="form-group">
                                {{ Form::label('file_content', 'Content', ['class' => 'control-label']) }}
                                {{ Form::textarea('file_content', $file->content, ['class' => 'form-control', 'id' => 'content-readonly']) }}
                                {{ Form::hidden('extension', $file->extension, ['id' => 'extension']) }}
                            </div>
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

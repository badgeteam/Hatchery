@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>{{ $file->name }}</strong>
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-12 clearfix">
                            {!! Form::open(['method' => 'put', 'route' => ['files.update', 'file' => $file->id], 'id' => 'content_form']) !!}

                            <div class="form-group @if($errors->has('file_content')) has-error @endif">
                                {{ Form::label('file_content', 'Content', ['class' => 'control-label']) }}
                                {{ Form::textarea('file_content', $file->content, ['class' => 'form-control', 'id' => 'content']) }}
                            </div>

                            <div class="pull-right">
                                <button type="submit" class="btn btn-default">Save</button>
                            </div>

                            {!! Form::close() !!}
                        </div>

                    </div>
                    @if($file->name === 'icon.py')
                    <div class="row" id="pixels">
                        <div class="col-md-4">
                            <table>
                                @for($r=0; $r < 8; $r++)
                                <tr id="row{{ $r }}">
                                    @for($p=0; $p < 8; $p++)
                                    <td id="row{{$r}}pixel{{$p}}" class="clickable"></td>
                                    @endfor
                                </tr>
                                @endfor
                            </table>
                        </div>
                        <div class="col-md-8">
                            <span class="colour-container">
                                <a href="#" id="colour"></a>
                            </span>
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
<script>
    window.keymap = "{{ Auth::user()->editor }}";
</script>
@endsection

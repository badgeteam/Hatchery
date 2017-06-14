@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <ol class="breadcrumb">
                <li><a href="{{ route('projects.index') }}">Eggs</a></li>
                <li><a href="{{ route('projects.edit', ['project' => $file->version->project->id]) }}">{{ $file->version->project->name }}</a></li>
                <li class="active">{{ $file->name }}</li>
            </ol>

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>{{ $file->name }}</strong>
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-12 clearfix">
                            {!! Form::open(['method' => 'put', 'route' => ['files.update', 'file' => $file->id]]) !!}

                            <div class="form-group @if($errors->has('content')) has-error @endif">
                                {{ Form::label('content', 'Content', ['class' => 'control-label']) }}
                                {{ Form::textarea('content', $file->content, ['class' => 'form-control', 'id' => 'content']) }}
                            </div>

                            <div class="pull-right">
                                <button type="submit" class="btn btn-default">Save</button>
                            </div>

                            {!! Form::close() !!}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript">
        window.onload = function() {
            var editor = window.CodeMirror.fromTextArea(document.getElementById('content'), {
                lineNumbers: true,
                mode: "python"
            });
        }
    </script>
@endsection
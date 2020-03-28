@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>Add a file</strong>
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-12 clearfix">
                            {!! Form::open(['method' => 'post', 'route' => 'files.store', 'id' => 'content_form']) !!}

                            {{ Form::hidden('version_id', $version->id) }}
                            @if ($name)
                            {{ Form::hidden('extension', ltrim((string) strstr($name, '.'), '.'), ['id' => 'extension']) }}
                            @endif

                            <div class="form-group @if($errors->has('name')) has-error @endif">
                                {{ Form::label('name', 'File name', ['class' => 'control-label']) }}
                                {{ Form::text('name', $name, ['class' => 'form-control', 'id' => 'name']) }}
                            </div>

                            <div class="form-group @if($errors->has('file_content')) has-error @endif">
                                {{ Form::label('file_content', 'Content', ['class' => 'control-label']) }}
                                {{ Form::textarea('file_content', null, ['class' => 'form-control', 'id' => 'content']) }}
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
    <script>
        window.keymap = "{{ Auth::user()->editor }}";
    </script>
@endsection
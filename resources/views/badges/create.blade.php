@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>Add a badge</strong>
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-12 clearfix">
                            {!! Form::open(['method' => 'post', 'route' => 'badges.store', 'id' => 'content_form']) !!}

                            <div class="form-group @if($errors->has('name')) has-error @endif">
                                {{ Form::label('name', 'Badge name', ['class' => 'control-label']) }}
                                {{ Form::text('name', null, ['class' => 'form-control', 'id' => 'name']) }}
                            </div>

                            <h3>Optional:</h3>

                            <div class="form-group @if($errors->has('constraints')) has-error @endif">
                                {{ Form::label('constraints', 'Constraints', ['class' => 'control-label']) }}
                                {{ Form::textarea('constraints', null, ['class' => 'form-control', 'id' => 'constraints']) }}
                            </div>

                            <div class="form-group @if($errors->has('commands')) has-error @endif">
                                {{ Form::label('commands', 'Commands', ['class' => 'control-label']) }}
                                {{ Form::textarea('commands', null, ['class' => 'form-control', 'id' => 'commands']) }}
                                {{ Form::hidden('extension', 'sh', ['id' => 'extension']) }}
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
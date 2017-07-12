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
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    <strong>Add an Egg</strong>
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-12 clearfix">
                            {!! Form::open(['method' => 'post', 'route' => ['projects.store']]) !!}
                                <div class="form-group @if($errors->has('category_id')) has-error @endif">
                                    {{ Form::label('category_id', 'Category', ['class' => 'control-label']) }}
                                    {{ Form::select('category_id', \App\Models\Category::where('hidden', false)->pluck('name', 'id'), 0, ['class' => 'form-control', 'id' => 'category_id']) }}
                                </div>

                                <div class="form-group @if($errors->has('name')) has-error @endif">
                                    {{ Form::label('name', 'Name', ['class' => 'control-label']) }}
                                    {{ Form::text('name', null, ['class' => 'form-control', 'id' => 'name']) }}
                                </div>

                                <div class="form-group @if($errors->has('description')) has-error @endif">
                                    {{ Form::label('description', 'Description', ['class' => 'control-label']) }}
                                    {{ Form::textarea('description', null, ['class' => 'form-control', 'id' => 'description']) }}
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

@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <strong class="spacer">{{ $badge->name }}</strong>
                        <div class="pull-right">
                            @can('update', $badge)
                                <a class="btn btn-primary btn-xs" href="{{ route('badges.edit', ['badge' => $badge->id])  }}">edit</a>
                            @endcan
                            @can('delete', $badge)
                                {!! Form::open(['method' => 'delete', 'route' => ['badges.destroy', 'badge' => $badge->id], 'class' => 'deleteform']) !!}
                                <button class="btn btn-danger btn-xs" name="delete-resource" type="submit" value="delete">delete</button>
                                {!! Form::close() !!}
                            @endcan
                        </div>
                    </div>
                    <div class="panel-body">

                        <table class="table table-striped">
                            <tbody>
                            <tr>
                                <td>Badge added:</td>
                                <td>{{ $badge->created_at }}</td>
                            </tr>
                            </tbody>
                        </table>
                        @if($projects->total() > 0)
                            <h3>Projects:</h3>
                            @include('partials.projects')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="confirm-delete" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    Are you sure you want to delete {{ $badge->name }}?
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-danger" id="delete">Delete</button>
                    <button type="button" data-dismiss="modal" class="btn btn-default">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script type="text/javascript">
			// Delete resource
			$('button[name="delete-resource"]').on('click', function (e) {
				e.preventDefault()
				var $form = $(this).closest('form')
				$('#confirm-delete').modal({ backdrop: 'static', keyboard: false }).one('click', '#delete', function (e) {
					$form.trigger('submit')
				})
			})
    </script>
@endsection
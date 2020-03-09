<table class="table table-striped">
    <thead>
        <tr>
            <th>Vote</th>
            <th>Comment</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
    @forelse($project->votes as $vote)
        <tr>
            <td>
            @if($vote->type === 'up')
                <img src="{{ asset('img/rulez.gif') }}" alt="up" />
            @elseif($vote->type === 'pig')
                <img src="{{ asset('img/isok.gif') }}" alt="pig" />
            @elseif($vote->type === 'down')
                <img src="{{ asset('img/sucks.gif') }}" alt="down" />
            @endif
            </td>
            <td>{{ $vote->comment }}</td>
            <td>{{ $vote->updated_at->diffForHumans() }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="3">No votes yet :(</td>
        </tr>
    @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">
                @if ($project->userVoted() === false)
                <button name="vote" class="btn btn-default">Vote</button>
                @endif
                @auth
                <button name="notify" class="btn btn-warning">Notify team</button>
                @endauth
            </td>
        </tr>
    </tfoot>
</table>
@if($project->warnings()->count() > 0)
<table class="table table-striped">
    <thead>
        <tr>
            <th>Warning!</th>
        </tr>
    </thead>
    <tbody>
        @foreach($project->warnings as $warning)
        <tr>
            <td>
                {{ $warning->description }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
<div id="vote-dialog" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <h3>How do you rate this app?</h3>
                {!! Form::open(['method' => 'post', 'route' => ['votes.store'], 'id' => 'vote-form']) !!}
                {{ Form::hidden('project_id', $project->id) }}
                <label>
                    {{ Form::radio('type', 'up' , false) }}
                    <img src="{{ asset('img/rulez.gif') }}" alt="up" />
                </label>
                <label>
                    {{ Form::radio('type', 'pig' , true) }}
                    <img src="{{ asset('img/isok.gif') }}" alt="pig" />
                </label>
                <label>
                    {{ Form::radio('type', 'down' , false) }}
                    <img src="{{ asset('img/sucks.gif') }}" alt="down" />
                </label>
                <div class="form-group @if($errors->has('comment')) has-error @endif">
                    {{ Form::label('comment', 'Comment', ['class' => 'control-label']) }}
                    {{ Form::textarea('comment', null, ['class' => 'form-control', 'id' => 'comment']) }}
                </div>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-danger" id="vote">Vote</button>
                <button type="button" data-dismiss="modal" class="btn btn-default">Cancel</button>
            </div>
        </div>
    </div>
</div>
<div id="notify-dialog" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <h3>So, you have issue?</h3>
                {!! Form::open(['method' => 'post', 'route' => ['projects.notify', $project], 'id' => 'notify-form']) !!}
                <div class="form-group @if($errors->has('description')) has-error @endif">
                    {{ Form::label('description', 'Description', ['class' => 'control-label']) }}
                    {{ Form::textarea('description', null, ['class' => 'form-control', 'id' => 'description']) }}
                </div>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-danger" id="notify">Notify</button>
                <button type="button" data-dismiss="modal" class="btn btn-default">Cancel</button>
            </div>
        </div>
    </div>
</div>
@section('script')
    <script type="text/javascript">
      $('button[name="vote"]').on('click', function (e) {
        e.preventDefault()
        var $form = $('#vote-form')
        $('#vote-dialog').modal({ backdrop: 'static', keyboard: false }).one('click', '#vote', function (e) {
          $form.trigger('submit')
        })
      })
      $('button[name="notify"]').on('click', function (e) {
        e.preventDefault()
        var $form = $('#notify-form')
        $('#notify-dialog').modal({ backdrop: 'static', keyboard: false }).one('click', '#notify', function (e) {
          $form.trigger('submit')
        })
      })
    </script>
    <style>
        [type=radio] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        [type=radio] + img {
            cursor: pointer;
            margin-right: 20px;
        }
        [type=radio]:checked + img {
            filter: drop-shadow(0 0 .75rem crimson) drop-shadow(0 0 .5rem red) drop-shadow(0 0 .25rem maroon);
        }
    </style>
@endsection


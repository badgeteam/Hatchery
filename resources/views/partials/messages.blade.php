@if(!$errors->isEmpty())
    @if(isset($file))
    <div class="combined-alerts">
    @endif
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                @if(!empty($error))
                <p>{{ $error }}</p>
                @endif
            @endforeach
        </div>
    @if(isset($file))
        <div class="alert alert-info">
            <p>Don't worry, the changes have been saved anyway ¯\_(ツ)_/¯</p>
        </div>
    </div>
    @endif
@endif
@if(!$successes->isEmpty())
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        @foreach($successes->all() as $success)
            @if(!empty($success))
            <p>{{ $success }}</p>
            @endif
        @endforeach
    </div>
@endif
@if(!$info->isEmpty())
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        @foreach($info->all() as $infoItem)
            @if(!empty($infoItem))
            <p>{{ $infoItem }}</p>
            @endif
        @endforeach
    </div>
@endif
@if(!$warnings->isEmpty())
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        @foreach($warnings->all() as $warning)
            @if(!empty($warning))
            <p>{{ $warning }}</p>
            @endif
        @endforeach
    </div>
@endif
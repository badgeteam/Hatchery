@if(!$errors->isEmpty())
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
@if(!$successes->isEmpty())
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        @foreach($successes->all() as $success)
            <p>{{ $success }}</p>
        @endforeach
    </div>
@endif
@if(!$info->isEmpty())
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        @foreach($info->all() as $infoItem)
            <p>{{ $infoItem }}</p>
        @endforeach
    </div>
@endif
@if(!$warnings->isEmpty())
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        @foreach($warnings->all() as $warning)
            <p>{{ $warning }}</p>
        @endforeach
    </div>
@endif
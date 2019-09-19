{{ Form::label('compatibility', 'Compatibility', ['class' => 'control-label']) }}




<div class="form-group @if($errors->has('compatibility')) has-error @endif">
@foreach(\App\Models\Badge::all()->reverse() as $badge)
    <div>
    @php
        /** @var \App\Models\Project $project */
        /** @var \App\Models\Badge $badge */
        /** @var \Illuminate\Database\Eloquent\Relations\HasMany $state */
        $state = $project->states()->where('badge_id', $badge->id);
    @endphp
    {{ Form::checkbox('badge_ids[]', $badge->id, $state->count() > 0) }}
    {{ $badge->name }}
    {{ Form::select("badge_status[$badge->id]", \App\Models\BadgeProject::$states, $state->count() > 0 ? $state->first()->status : 'unknown', ['class' => 'form-control compatibility']) }}
    </div>
@endforeach
</div>

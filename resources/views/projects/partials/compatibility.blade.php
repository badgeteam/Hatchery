{{ Form::label('compatibility', 'Compatibility', ['class' => 'control-label']) }}

<div class="form-group compact @if($errors->has('compatibility')) has-error @endif">
@foreach(\App\Models\Badge::all()->reverse() as $badge)
    <div class="compatibility-block">
    @php
        /** @var \App\Models\Project $project */
        /** @var \App\Models\Badge $badge */
        /** @var \Illuminate\Database\Eloquent\Relations\HasMany $state */
        $state = $project->states()->where('badge_id', $badge->id);
    @endphp
        <label>
        {{ Form::checkbox('badge_ids[]', $badge->id, $state->count() > 0, ['id' => 'badge_checkbox_'.$badge->id]) }}
        {{ $badge->name }}
        </label>
        {{ Form::select("badge_status[$badge->id]", \App\Models\BadgeProject::$states, $state->count() > 0 ? $state->first()->status : 'unknown', ['class' => 'form-control compatibility']) }}
    </div>
@endforeach
</div>

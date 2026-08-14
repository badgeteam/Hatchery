{{-- SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors --}}
{{-- SPDX-License-Identifier: MIT --}}
@php
    /** @var \App\Models\Project|null $project */
    $currentLicense = isset($project) ? $project->license : 'MIT';
@endphp
<div class="form-group @if($errors->has('license')) has-error @endif">
    {{ Form::label('license', 'License', ['class' => 'control-label']) }}
    {{ Form::text('license', $currentLicense, [
        'class'       => 'form-control',
        'id'          => 'license',
        'list'        => 'spdx-licenses',
        'maxlength'   => 255,
        'placeholder' => 'MIT, GPL-3.0-or-later, Proprietary, ...',
    ]) }}
    <span class="help-block">
        Anything you like. The suggestions are
        <a href="https://spdx.org/licenses/" target="_blank" rel="noopener">SPDX identifiers</a>;
        pick one of those and the site can link to the licence text. Leave it empty for
        &ldquo;all rights reserved&rdquo;.
    </span>
    <datalist id="spdx-licenses">
        @foreach(\App\Models\License::where('isDeprecatedLicenseId', 0)->orderBy('licenseId')->get() as $spdx)
            <option value="{{ $spdx->licenseId }}">{{ $spdx->name }}</option>
        @endforeach
    </datalist>
    @if ($errors->has('license'))
        <span class="help-block"><strong>{{ $errors->first('license') }}</strong></span>
    @endif
</div>

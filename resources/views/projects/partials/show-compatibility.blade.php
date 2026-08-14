{{-- SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors --}}
{{-- SPDX-License-Identifier: MIT --}}
<strong>Compatibility</strong>
<ul>
    @forelse($project->states as $state)
        <li>{{ $state->badge->name }}: {{ $state->status }}</li>
    @empty
        <li>No badges found</li>
    @endforelse
</ul>

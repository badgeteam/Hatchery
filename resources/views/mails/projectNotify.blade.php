{{-- SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors --}}
{{-- SPDX-License-Identifier: MIT --}}
Hey,

Deze app suckt: {{ route('projects.show', ['project' => $project]) }}
Kijk er effies naar ofzo . .

---
{{ $description }}
---

MvG,
    Berend Botje

Gemeld door: {{ Auth::user()->email }} {{ Auth::user()->name }}
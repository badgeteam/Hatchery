{{-- SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors --}}
{{-- SPDX-License-Identifier: MIT --}}
@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Forbidden'))

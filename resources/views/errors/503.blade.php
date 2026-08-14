{{-- SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors --}}
{{-- SPDX-License-Identifier: MIT --}}
@extends('errors::minimal')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('message', __($exception->getMessage() ?: 'Service Unavailable'))

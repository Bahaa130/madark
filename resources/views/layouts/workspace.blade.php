@extends('layouts.app')

@php
    use App\Support\Nav;

    $user = auth()->user();
    $role = $user?->role?->value ?? 'STUDENT';
    $nav = Nav::forRole($role);
    $bottomNav = Nav::bottomFor($role);
@endphp

@section('content')
    <x-layout.app-shell :nav="$nav" :bottom-nav="$bottomNav">
        @yield('workspace')
    </x-layout.app-shell>
@endsection
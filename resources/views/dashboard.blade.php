@extends('layouts.app')

@section('title', 'Dashboard - NexusHRIS')
@section('page_title', 'Dashboard Utama')

@section('content')
    @role('super_admin')
        @include('dashboard.partials.super-admin')
    @endrole

    @role('hr_admin')
        @include('dashboard.partials.hr-admin')
    @endrole

    @role('manager')
        @include('dashboard.partials.manager')
    @endrole

    @role('employee')
        @include('dashboard.partials.employee')
    @endrole
@endsection

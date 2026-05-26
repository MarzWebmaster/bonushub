@extends('layouts.app')
@section('title', 'Dashboard - BonusHub')
@section('content')
<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">Redirecting...</h1>
    </div>
    <p class="text-surface-500">Loading your dashboard...</p>
</div>
<script>window.location.href = '{{ route('home') }}';</script>
@endsection
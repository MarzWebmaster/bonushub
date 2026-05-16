@extends('layouts.app')
{{-- This login view uses no role-specific sidebar --}}
@section('title', 'Login - BonusHub')
@section('page-title', 'Login')

@section('content')
    @include('auth.login')
@endsection

@extends('layouts.app')
@section('title','Liability Report - Merchant')
@section('content')
<div class="page-container">
  <div class="page-header">
    <div>
      <h1 class="page-title">Points Liability Report</h1>
      <p class="page-subtitle">Track your points issued vs redeemed</p>
    </div>
  </div>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="stat-card border-l-bonus-500"><p class="text-sm text-surface-500">Total Points Issued</p><p class="text-2xl font-bold">{{ number_format($report['"total_issued"'] ?? 0) }}</p></div>
    <div class="stat-card border-l-red-500"><p class="text-sm text-surface-500">Total Points Redeemed</p><p class="text-2xl font-bold">{{ number_format($report['"total_redeemed"'] ?? 0) }}</p></div>
    <div class="stat-card border-l-amber-500"><p class="text-sm text-surface-500">Outstanding Liability</p><p class="text-2xl font-bold text-amber-600">{{ number_format($report['"outstanding"'] ?? 0) }}</p></div>
    <div class="stat-card border-l-emerald-500"><p class="text-sm text-surface-500">Redemption Rate</p><p class="text-2xl font-bold text-emerald-600">{{ ($report['"redemption_rate"'] ?? 0) }}%</p></div>
  </div>
  <p class="text-xs text-surface-400 mt-4">Active customers: {{ number_format($report['"active_customers"'] ?? 0) }} | Generated: {{ $report['"generated_at"'] ?? now() }}</p>
</div>
@endsection
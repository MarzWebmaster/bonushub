@extends('layouts.app')
@section('title', 'Dashboard - Staff')
@section('content')
<div class="page-container" style="padding-top:0">
    <div class="page-header">
        <div>
            <h1 class="page-title">Staff Dashboard</h1>
            <p class="page-subtitle">Serve your customers</p>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card p-6"><h2 class="font-bold text-surface-800 mb-4">Customer Lookup</h2>
            <form action="{{ route('staff.customer.lookup') }}" method="POST" class="space-y-4">
                @csrf
                <div><label class="form-label">Phone Number</label><input type="text" name="phone" class="form-input" placeholder="e.g. 0123456789" required></div>
                <button type="submit" class="btn-primary w-full">Search Customer</button>
            </form>
        </div>
        <div class="card p-6"><h2 class="font-bold text-surface-800 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="#" onclick="event.preventDefault();document.getElementById('add-points-section').classList.toggle('hidden');this.closest('.card').nextElementSibling?.scrollIntoView({behavior:'smooth'})" class="btn-outline w-full justify-start">➕ Add Points</a>
                <a href="#" onclick="event.preventDefault();document.getElementById('redeem-section').classList.toggle('hidden')" class="btn-outline w-full justify-start">🔄 Redeem</a>
                <a href="#" onclick="event.preventDefault();document.getElementById('void-section').classList.toggle('hidden')" class="btn-outline w-full justify-start">↩️ Void Transaction</a>
            </div>
        </div>
    </div>
    <div id="add-points-section" class="hidden mt-6">
        <div class="card p-6"><h2 class="font-bold text-surface-800 mb-4">Add Points</h2>
            <form action="{{ route('staff.add.points') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Customer Phone</label><input type="text" name="phone" class="form-input" required></div>
                    <div><label class="form-label">Points</label><input type="number" name="points" class="form-input" min="1" required></div>
                </div>
                <div><label class="form-label">Notes</label><textarea name="notes" class="form-textarea" rows="2" placeholder="Reason for adding points..."></textarea></div>
                <button type="submit" class="btn-primary">Add Points</button>
            </form>
        </div>
    </div>
    <div id="redeem-section" class="hidden mt-6">
        <div class="card p-6"><h2 class="font-bold text-surface-800 mb-4">Redeem Points</h2>
            <form action="{{ route('staff.redeem') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Customer Phone</label><input type="text" name="phone" class="form-input" required></div>
                    <div><label class="form-label">Points to Redeem</label><input type="number" name="points" class="form-input" min="1" required></div>
                </div>
                <button type="submit" class="btn-primary">Redeem</button>
            </form>
        </div>
    </div>
    <div id="void-section" class="hidden mt-6">
        <div class="card p-6"><h2 class="font-bold text-surface-800 mb-4">Void Transaction</h2>
            <form action="{{ route('staff.void') }}" method="POST" class="space-y-4">
                @csrf
                <div><label class="form-label">Transaction ID</label><input type="text" name="transaction_id" class="form-input" required></div>
                <div><label class="form-label">Reason</label><textarea name="reason" class="form-textarea" rows="2" required></textarea></div>
                <button type="submit" class="btn-danger">Void Transaction</button>
            </form>
        </div>
    </div>
</div>
@endsection
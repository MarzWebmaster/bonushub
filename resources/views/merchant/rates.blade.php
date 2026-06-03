@extends('layouts.app')@section('title','Loyalty Rates - Merchant')@section('content')
<div class="page-container"><div class="page-header"><div><h1 class="page-title">Loyalty Rates</h1><p class="page-subtitle">Configure earning and redemption rates</p></div></div>
<div class="card p-6" id="rates-form">Loading...</div></div>
<script>
fetch('/merchant/api/loyalty-rates').then(r=>r.json()).then(d=>{
    const rate = d.rate || {};
    document.getElementById('rates-form').innerHTML='<form action="{{ route('merchant.loyalty.rates.update') }}" method="POST" class="space-y-4">@csrf<div class="grid grid-cols-1 sm:grid-cols-2 gap-4"><div><label class="form-label">Earn Rate (pts per RM)</label><input name="earn_rate" class="form-input" value="'+(rate.earn_rate||'')+'"></div><div><label class="form-label">Redeem Rate (pts per RM)</label><input name="redeem_rate" class="form-input" value="'+(rate.redeem_rate||'')+'"></div><div><label class="form-label">Min Redeem Points</label><input name="min_redeem" type="number" class="form-input" value="'+(rate.min_redeem||'')+'"></div><div><label class="form-label">Max Redeem Points</label><input name="max_redeem" type="number" class="form-input" value="'+(rate.max_redeem||'')+'"></div></div><button type="submit" class="btn-primary">Update Rates</button></form>';
});
</script>
@endsection

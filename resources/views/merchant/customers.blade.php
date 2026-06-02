@extends('layouts.app')@section('title','Customers - Merchant')@section('content')
<div class="page-container"><div class="page-header"><div><h1 class="page-title">My Customers</h1><p class="page-subtitle">View and manage your customers</p></div></div>
<div class="card overflow-hidden"><table class="data-table"><thead><tr><th>Customer</th><th>Phone</th><th>Points</th><th>Tier</th><th>Joined</th></tr></thead>
<tbody id="cust-table"><tr><td colspan="5" class="text-center text-surface-400 py-8">Loading...</td></tr></tbody></table></div></div>
<script>
fetch('/merchant/api/customers').then(r=>r.json()).then(d=>{
    const list = d.customers?.data || d.customers || [];
    let h = '';
    if(list.length){
        list.forEach(c=>{
            const cu = c.customer || c;
            h += '<tr><td class="font-medium">'+(cu.name||'N/A')+'</td>'
                +'<td class="text-surface-500">'+(cu.phone||'-')+'</td>'
                +'<td class="font-bold text-bonus-600">'+c.points+'</td>'
                +'<td><span class="badge-tier '+(c.tier_per_merchant||'basic').toLowerCase()+'">'+(c.tier_per_merchant||'Basic')+'</span></td>'
                +'<td class="text-surface-400 text-xs">'+c.tied_at+'</td></tr>';
        });
    } else {
        h = '<tr><td colspan="5" class="text-center text-surface-400 py-8">No customers yet</td></tr>';
    }
    document.getElementById('cust-table').innerHTML = h;
});
</script>
@endsection

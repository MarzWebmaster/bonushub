@extends('layouts.app')
@section('title','Pending Approvals - Merchant')
@section('content')
<div class="page-container">
  <div class="page-header">
    <div>
      <h1 class="page-title">Pending Approvals</h1>
      <p class="page-subtitle">Review staff point entries</p>
    </div>
  </div>

  <div class="card overflow-hidden">
    <table class="data-table">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Points</th>
          <th>Staff</th>
          <th>Notes</th>
          <th>Date</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody id="pending-table">
        <tr><td colspan="6" class="text-center text-surface-400 py-8">Loading...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<script>
fetch('/merchant/api/points/pending').then(r=>r.json()).then(d=>{
  const tbody = document.getElementById('pending-table');
  const items = d.transactions?.data || d.transactions || [];
  if(items.length){
    tbody.innerHTML = items.map(t => `
      <tr>
        <td class="font-medium">${t.customer?.name ?? t.customer_name ?? 'N/A'}</td>
        <td class="font-bold text-amber-600">${t.points}</td>
        <td>${t.staff?.name ?? t.staff_name ?? '-'}</td>
        <td class="text-surface-500 text-sm">${t.notes || '-'}</td>
        <td class="text-surface-400 text-xs">${t.created_at}</td>
        <td>
          <div class="flex gap-1 justify-center">
            <button onclick="approveTx(${t.id})" class="btn-sm btn-success">Approve</button>
            <button onclick="rejectTx(${t.id})" class="btn-sm btn-danger">Reject</button>
          </div>
        </td>
      </tr>
    `).join('');
  } else {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-surface-400 py-8">No pending approvals</td></tr>';
  }
});

function approveTx(id){
  fetch('/merchant/points/approve/'+id, {
    method: 'POST',
    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
  }).then(()=> location.reload());
}

function rejectTx(id){
  if(!confirm('Reject this transaction?')) return;
  fetch('/merchant/points/reject/'+id, {
    method: 'POST',
    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
  }).then(()=> location.reload());
}
</script>
@endsection

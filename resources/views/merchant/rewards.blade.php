@extends('layouts.app')
@section('title','Rewards - Merchant')
@section('content')
<div class="page-container">
  <div class="page-header">
    <div>
      <h1 class="page-title">Reward Products</h1>
      <p class="page-subtitle">Manage redeemable products</p>
    </div>
    <button onclick="document.getElementById('reward-modal').classList.remove('hidden')" class="btn-primary">+ Add Reward</button>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="rewards-grid">
    <div class="text-center text-surface-400 py-8 col-span-3">Loading...</div>
  </div>
</div>

<!-- Add Reward Modal -->
<div id="reward-modal" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
  <div class="modal-content">
    <div class="modal-header">
      <h2 class="text-lg font-bold">Add Reward</h2>
      <button onclick="this.closest('.modal-overlay').classList.add('hidden')">&times;</button>
    </div>
    <form action="{{ route('merchant.rewards.store') }}" method="POST" class="modal-body">
      @csrf
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="form-label">Product Name</label>
          <input name="name" class="form-input" required>
        </div>
        <div>
          <label class="form-label">Points Required</label>
          <input name="points_required" type="number" class="form-input" min="1" required>
        </div>
        <div>
          <label class="form-label">Stock Quantity</label>
          <input name="stock_quantity" type="number" class="form-input" min="0">
        </div>
        <div>
          <label class="form-label">Claim Type</label>
          <select name="claim_type" class="form-select">
            <option value="self_collect">Self Collect</option>
            <option value="delivery">Delivery</option>
            <option value="download">Download</option>
            <option value="access_code">Access Code</option>
          </select>
        </div>
        <div class="col-span-2">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-textarea" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="this.closest('.modal-overlay').classList.add('hidden')" class="btn-secondary">Cancel</button>
        <button type="submit" class="btn-primary">Save Reward</button>
      </div>
    </form>
  </div>
</div>

<script>
fetch('/merchant/api/rewards').then(r=>r.json()).then(d=>{
  const grid = document.getElementById('rewards-grid');
  const items = d.products?.data || d.products || [];
  if(items.length){
    grid.innerHTML = items.map(r => `
      <div class="card p-4">
        <h3 class="font-bold">${r.name}</h3>
        <p class="text-2xl font-bold text-bonus-600 mt-2">${r.points_required} pts</p>
        <p class="text-sm text-surface-500 mt-1">Stock: ${r.stock_left ?? r.stock_quantity ?? '-'}</p>
        <p class="text-xs text-surface-400">${r.claim_type ?? ''}</p>
        <button onclick="deleteReward(${r.id})" class="btn-sm btn-danger mt-3">Delete</button>
      </div>
    `).join('');
  } else {
    grid.innerHTML = '<div class="col-span-3 text-center text-surface-400 py-8">No reward products yet.</div>';
  }
});

function deleteReward(id){
  if(!confirm('Delete this reward?')) return;
  fetch('/merchant/rewards/'+id, {
    method: 'DELETE',
    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
  }).then(()=> location.reload());
}
</script>
@endsection

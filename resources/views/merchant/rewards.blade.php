@extends('layouts.app')
@section('title','Rewards - Merchant')
@section('content')
<div class="page-container">
  <div class="page-header">
    <div>
      <h1 class="page-title">Reward Products</h1>
      <p class="page-subtitle">Manage redeemable products</p>
    </div>
    <button onclick="openAddModal()" class="btn-primary">+ Add Reward</button>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="rewards-grid">
    @forelse($rewards as $r)
    <div class="card p-5 flex flex-col gap-3">
      <div class="flex items-start justify-between">
        <h3 class="font-semibold text-surface-800">{{ $r->name }}</h3>
        <div class="flex gap-1">
          <button onclick="editReward({{ $r->id }})" class="text-xs px-2 py-1 rounded bg-surface-100 hover:bg-surface-200 text-surface-600">Edit</button>
          <button onclick="deleteReward({{ $r->id }})" class="text-xs px-2 py-1 rounded bg-red-50 hover:bg-red-100 text-red-600">Del</button>
        </div>
      </div>
      <div class="flex items-baseline gap-2">
        <span class="text-2xl font-bold text-bonus-600">{{ number_format($r->points_required) }}</span>
        <span class="text-sm text-surface-500">pts</span>
      </div>
      <div class="flex gap-4 text-xs text-surface-500">
        <span>Stock: {{ $r->stock_quantity }}</span>
        <span>Type: {{ ucfirst(str_replace('_',' ',$r->claim_type)) }}</span>
      </div>
    </div>
    @empty
    <div class="text-center text-surface-400 py-8 sm:col-span-3">No reward products yet. Click <strong>+ Add Reward</strong> to create one.</div>
    @endforelse
  </div>
</div>

<div id="reward-modal" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
  <div class="modal-content">
    <div class="modal-header">
      <h2 class="text-lg font-bold" id="reward-modal-title">Add Reward</h2>
      <button onclick="document.getElementById('reward-modal').classList.add('hidden')" class="text-surface-400 hover:text-surface-600 text-xl leading-none">&times;</button>
    </div>
    <form id="reward-form" action="{{ route('merchant.rewards.store') }}" method="POST" class="modal-body">
      @csrf
      <div id="method-spoof"></div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="form-label">Product Name</label><input name="name" id="rw-name" class="form-input" required></div>
        <div><label class="form-label">Points Required</label><input name="points_required" id="rw-points" type="number" class="form-input" min="1" required></div>
        <div><label class="form-label">Stock Quantity</label><input name="stock_quantity" id="rw-stock" type="number" class="form-input" min="0"></div>
        <div><label class="form-label">Claim Type</label>
          <select name="claim_type" id="rw-claim" class="form-select">
            <option value="self_collect">Self Collect</option>
            <option value="delivery">Delivery</option>
            <option value="download">Download</option>
            <option value="access_code">Access Code</option>
          </select>
        </div>
      </div>
      <div class="mt-4"><label class="form-label">Description</label><textarea name="description" id="rw-desc" class="form-input" rows="3"></textarea></div>
      <div class="flex gap-3 mt-5">
        <button type="submit" class="btn-primary">Save Reward</button>
        <button type="button" onclick="document.getElementById('reward-modal').classList.add('hidden')" class="px-4 py-2 text-sm text-surface-600 hover:text-surface-800">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
    document.getElementById('reward-modal-title').textContent = 'Add Reward';
    document.getElementById('reward-form').action = '{{ route("merchant.rewards.store") }}';
    document.getElementById('method-spoof').innerHTML = '';
    document.getElementById('rw-name').value = '';
    document.getElementById('rw-points').value = '';
    document.getElementById('rw-stock').value = '';
    document.getElementById('rw-claim').value = 'self_collect';
    document.getElementById('rw-desc').value = '';
    document.getElementById('reward-modal').classList.remove('hidden');
}
function refreshRewards() {
    fetch('/merchant/api/rewards').then(r => r.json()).then(d => {
        if (!d.success) return;
        let h = '';
        d.rewards.forEach(r => {
            h += '<div class="card p-5 flex flex-col gap-3"><div class="flex items-start justify-between"><h3 class="font-semibold text-surface-800">' + r.name + '</h3><div class="flex gap-1"><button onclick="editReward(' + r.id + ')" class="text-xs px-2 py-1 rounded bg-surface-100 hover:bg-surface-200 text-surface-600">Edit</button><button onclick="deleteReward(' + r.id + ')" class="text-xs px-2 py-1 rounded bg-red-50 hover:bg-red-100 text-red-600">Del</button></div></div><div class="flex items-baseline gap-2"><span class="text-2xl font-bold text-bonus-600">' + Number(r.points_required).toLocaleString() + '</span><span class="text-sm text-surface-500">pts</span></div><div class="flex gap-4 text-xs text-surface-500"><span>Stock: ' + r.stock_quantity + '</span><span>Type: ' + r.claim_type.replace(/_/g,' ') + '</span></div></div>';
        });
        if (!h) h = '<div class="text-center text-surface-400 py-8 sm:col-span-3">No reward products yet. Click <strong>+ Add Reward</strong> to create one.</div>';
        document.getElementById('rewards-grid').innerHTML = h;
    });
}
function editReward(id) {
    fetch('/merchant/api/rewards').then(r => r.json()).then(d => {
        const r = d.rewards.find(x => x.id == id);
        if (!r) return;
        document.getElementById('reward-modal-title').textContent = 'Edit Reward';
        document.getElementById('reward-form').action = '/merchant/api/rewards/' + id;
        document.getElementById('method-spoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('rw-name').value = r.name;
        document.getElementById('rw-points').value = r.points_required;
        document.getElementById('rw-stock').value = r.stock_quantity;
        document.getElementById('rw-claim').value = r.claim_type;
        document.getElementById('rw-desc').value = r.description || '';
        document.getElementById('reward-modal').classList.remove('hidden');
    });
}
function deleteReward(id) {
    if (!confirm('Delete this reward?')) return;
    fetch('/merchant/rewards/' + id, { method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/x-www-form-urlencoded'}, body: '_method=DELETE' })
        .then(() => refreshRewards());
}
document.getElementById('reward-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch(this.action, { method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}, body: fd })
        .then(r => r.json()).then(d => {
            if (d.success) { refreshRewards(); document.getElementById('reward-modal').classList.add('hidden'); }
            else alert(d.message || 'Failed');
        });
});
</script>
@endsection
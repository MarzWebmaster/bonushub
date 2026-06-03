@extends('layouts.app')
@section('title', 'Loyalty Rates - Merchant')
@section('content')
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Loyalty Rates</h1>
            <p class="page-subtitle">Configure earning and redemption rates</p>
        </div>
    </div>

    <div class="card p-4 sm:p-6" id="rates-form">Loading...</div>
</div>

<script>
function loadRatesForm() {
    fetch('/merchant/api/loyalty-rates')
        .then(r => r.json())
        .then(d => {
            const rate = d.rate || {};
            document.getElementById('rates-form').innerHTML = '' +
                '<div id="rates-success" class="hidden mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">Saved!</div>' +
                '<form id="rates-frm" class="space-y-4">' +
                '@csrf' +
                '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' +
                '<div><label class="form-label">Earn Rate (pts per RM)</label>' +
                '<input name="earn_rate" type="number" step="0.01" min="0.01" class="form-input" value="' + (rate.earn_rate || '1.00') + '" required></div>' +
                '<div><label class="form-label">Redeem Rate (pts per RM)</label>' +
                '<input name="redeem_rate" type="number" step="0.01" min="1" class="form-input" value="' + (rate.redeem_rate || '1.00') + '" required></div>' +
                '<div><label class="form-label">Min Redeem Points</label>' +
                '<input name="min_redeem" type="number" min="0" class="form-input" value="' + (rate.min_redeem || '') + '"></div>' +
                '<div><label class="form-label">Max Redeem Points</label>' +
                '<input name="max_redeem" type="number" min="0" class="form-input" value="' + (rate.max_redeem || '') + '"></div>' +
                '</div>' +
                '<button type="button" id="btn-save-rates" class="btn-primary inline-flex items-center gap-2">' +
                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Update Rates</button>' +
                '</form>';

            document.getElementById('btn-save-rates').addEventListener('click', function() {
                const btn = this;
                const orig = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Saving...';

                const fd = new FormData(document.getElementById('rates-frm'));
                const data = {};
                fd.forEach((v, k) => data[k] = v);

                fetch('/merchant/api/loyalty-rates', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('rates-success').classList.remove('hidden');
                        setTimeout(function() {
                            document.getElementById('rates-success').classList.add('hidden');
                        }, 3000);
                        loadRatesForm();
                    } else {
                        alert('Error: ' + (res.message || 'Failed'));
                    }
                })
                .catch(function(err) { alert('Network error: ' + err.message); })
                .finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = orig;
                });
            });
        });
}

loadRatesForm();
</script>
@endsection
@extends('layouts.app')
@section('title', 'Create Giveaway')
@section('content')
<div class="page-container" style="padding-top:0" x-data="createGiveaway()">
    <div class="page-header">
        <a href="{{ route('merchant.giveaways.index') }}" class="btn btn-secondary">← Back</a>
        <div>
            <h1 class="page-title">🎉 Create Giveaway</h1>
            <p class="page-subtitle">Set up a viral giveaway campaign</p>
        </div>
    </div>

    <div class="card" style="max-width:700px">
        <div class="card-header">
            <h2 class="card-title">Campaign Details</h2>
        </div>
        <div class="card-body">
            <form @submit.prevent="submit()">
                <!-- Title -->
                <div class="form-group">
                    <label class="form-label">Campaign Title *</label>
                    <input type="text" class="form-input" x-model="form.title"
                           placeholder="e.g. Merdeka Giveaway 2026" required>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-input" x-model="form.description" rows="3"
                              placeholder="Describe the giveaway and how to participate..."></textarea>
                </div>

                <!-- Prize -->
                <div class="form-row">
                    <div class="form-group" style="flex:2">
                        <label class="form-label">Prize Description *</label>
                        <input type="text" class="form-input" x-model="form.prize_description"
                               placeholder="e.g. RM100 Voucher + Free Product" required>
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Prize Value (pts)</label>
                        <input type="number" class="form-input" x-model="form.prize_value"
                               placeholder="0" min="0">
                    </div>
                </div>

                <!-- Winners & Selection -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Number of Winners *</label>
                        <input type="number" class="form-input" x-model="form.winner_count"
                               placeholder="1" min="1" max="100" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Selection Method *</label>
                        <select class="form-input" x-model="form.selection_method">
                            <option value="random">🎲 Random Draw</option>
                            <option value="top_referrers">🏆 Top Referrers</option>
                            <option value="manual">✋ Manual Selection</option>
                        </select>
                    </div>
                </div>

                <!-- Entry Method -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">How Customers Enter *</label>
                        <select class="form-input" x-model="form.entry_method">
                            <option value="referral">🔗 Referral Links</option>
                            <option value="task">📸 Complete Tasks</option>
                            <option value="purchase">🛒 Make Purchase</option>
                            <option value="manual">✋ Manual Entry</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Entries</label>
                        <input type="number" class="form-input" x-model="form.max_entries"
                               placeholder="Unlimited" min="10">
                        <small style="color:var(--text-muted)">Leave empty for unlimited</small>
                    </div>
                </div>

                <!-- Dates -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="datetime-local" class="form-input" x-model="form.starts_at">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date *</label>
                        <input type="datetime-local" class="form-input" x-model="form.ends_at" required>
                    </div>
                </div>

                <!-- Submit -->
                <div class="form-actions">
                    <a href="{{ route('merchant.giveaways.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg" :disabled="loading">
                        <span x-show="!loading">🚀 Create Campaign</span>
                        <span x-show="loading">Creating...</span>
                    </button>
                </div>
            </form>

            <!-- Status messages -->
            <div x-show="error" x-cloak class="alert alert-error" style="margin-top:16px" x-text="error"></div>
            <div x-show="success" x-cloak class="alert alert-success" style="margin-top:16px" x-text="success"></div>
        </div>
    </div>
</div>

<script>
function createGiveaway() {
    return {
        form: {
            title: '',
            description: '',
            prize_description: '',
            prize_value: 0,
            winner_count: 1,
            selection_method: 'random',
            entry_method: 'referral',
            entries_per_referral: 1,
            max_entries: null,
            starts_at: '',
            ends_at: '',
        },
        loading: false,
        error: '',
        success: '',

        async submit() {
            this.loading = true;
            this.error = '';
            this.success = '';

            try {
                const res = await fetch('/merchant/giveaways', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();

                if (data.success) {
                    this.success = data.message;
                    setTimeout(() => {
                        window.location.href = '/merchant/giveaways/' + data.campaign.id;
                    }, 1000);
                } else {
                    this.error = data.message || 'Failed to create campaign.';
                    if (data.errors) {
                        this.error = Object.values(data.errors).flat().join(' ');
                    }
                }
            } catch (e) {
                this.error = 'Network error. Please try again.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endsection
@extends('layouts.app')
@section('title', 'Packages - BonusHub')
@section('content')
<style>
/* Package Management: scoped styles. Same palette as the app (bonus = indigo, gold, surface = slate), light + dark. */
.pk-summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-bottom:20px; }
@media (max-width:560px){ .pk-summary { grid-template-columns:1fr; } }
.pk-sum { display:flex; align-items:center; gap:12px; padding:14px 16px; }
.pk-sum-ico { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; background:#eef2ff; flex-shrink:0; }
.dark .pk-sum-ico { background:rgba(99,102,241,.18); }
.pk-sum b { display:block; font-size:22px; line-height:1.1; font-weight:700; color:#0f172a; }
.dark .pk-sum b { color:#f1f5f9; }
.pk-sum span { font-size:12px; color:#64748b; }

.pk-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(270px,1fr)); gap:20px; align-items:stretch; }
.pk-card { position:relative; display:flex; flex-direction:column; overflow:hidden; }
.pk-card:hover { box-shadow:0 10px 28px -12px rgba(79,70,229,.35); transform:translateY(-2px); }
.pk-accent { height:5px; background:linear-gradient(90deg,#6366f1,#a855f7); }
.pk-card.is-free .pk-accent { background:linear-gradient(90deg,#cbd5e1,#94a3b8); }
.pk-card.is-premium .pk-accent { background:linear-gradient(90deg,#fbbf24,#f59e0b); }
.pk-ribbon { position:absolute; top:16px; right:14px; font-size:10px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; padding:3px 9px; border-radius:999px; background:#eef2ff; color:#4338ca; }
.pk-card.is-premium .pk-ribbon { background:#fef3c7; color:#b45309; }
.dark .pk-ribbon { background:rgba(99,102,241,.22); color:#c7d2fe; }
.pk-head { padding:20px 20px 14px; }
.pk-name { font-size:15px; font-weight:700; color:#312e81; letter-spacing:.01em; }
.pk-card.is-free .pk-name { color:#475569; }
.pk-card.is-premium .pk-name { color:#92400e; }
.dark .pk-name, .dark .pk-card.is-free .pk-name, .dark .pk-card.is-premium .pk-name { color:#e2e8f0; }
.pk-price { display:flex; align-items:baseline; gap:4px; margin-top:6px; }
.pk-price b { font-size:34px; line-height:1; font-weight:800; color:#0f172a; letter-spacing:-.02em; }
.dark .pk-price b { color:#fff; }
.pk-price span { font-size:13px; color:#64748b; }
.pk-subs { margin-top:8px; font-size:12px; color:#64748b; display:flex; align-items:center; gap:6px; }
.pk-body { padding:0 20px 18px; display:flex; flex-direction:column; gap:14px; flex:1; }
.pk-sec { font-size:10.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; margin-bottom:6px; }
.pk-limits { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.pk-tile { background:#f8fafc; border:1px solid #eef2f6; border-radius:10px; padding:9px 12px; }
.dark .pk-tile { background:rgba(15,23,42,.5); border-color:#334155; }
.pk-tile b { display:block; font-size:17px; font-weight:700; color:#0f172a; }
.dark .pk-tile b { color:#f1f5f9; }
.pk-tile span { font-size:11px; color:#64748b; }
.pk-tool { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:10px; border:1px solid #e2e8f0; margin-bottom:8px; }
.pk-tool.on { background:linear-gradient(135deg,#eef2ff,#f5f3ff); border-color:#c7d2fe; }
.dark .pk-tool { border-color:#334155; }
.dark .pk-tool.on { background:rgba(99,102,241,.14); border-color:rgba(129,140,248,.45); }
.pk-tool.off { opacity:.62; }
.pk-tool-ico { font-size:18px; width:26px; text-align:center; }
.pk-tool-name { font-size:13px; font-weight:600; color:#1e293b; line-height:1.2; }
.dark .pk-tool-name { color:#e2e8f0; }
.pk-tool-sub { font-size:11px; color:#64748b; }
.pk-pill { margin-left:auto; font-size:11px; font-weight:600; padding:3px 9px; border-radius:999px; white-space:nowrap; }
.pk-pill.on { background:#dcfce7; color:#15803d; }
.pk-pill.off { background:#f1f5f9; color:#64748b; }
.dark .pk-pill.on { background:rgba(16,185,129,.2); color:#6ee7b7; }
.dark .pk-pill.off { background:#334155; color:#94a3b8; }
.pk-feats { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:6px; }
.pk-feats li { display:flex; gap:8px; align-items:flex-start; font-size:12.5px; color:#475569; }
.dark .pk-feats li { color:#cbd5e1; }
.pk-feats svg { width:14px; height:14px; margin-top:2px; color:#6366f1; flex-shrink:0; }
.pk-more { font-size:11.5px; color:#94a3b8; padding-left:22px; }
.pk-foot { display:flex; gap:8px; padding:14px 20px; border-top:1px solid #f1f5f9; background:#fcfdff; margin-top:auto; }
.dark .pk-foot { border-color:#334155; background:rgba(15,23,42,.25); }
.pk-foot .btn-sm { height:34px; display:inline-flex; align-items:center; justify-content:center; gap:6px; padding-top:0; padding-bottom:0; }
.pk-foot svg { width:14px; height:14px; flex-shrink:0; }
.pk-del { flex-shrink:0; width:38px; padding-left:0; padding-right:0; }
.pk-del[disabled] { opacity:.4; cursor:not-allowed; }
.pk-add { border:2px dashed #c7d2fe; background:transparent; min-height:240px; align-items:center; justify-content:center; color:#6366f1; cursor:pointer; text-align:center; gap:6px; transition:all .15s; }
.pk-add:hover { background:#eef2ff; border-color:#818cf8; }
.dark .pk-add { border-color:rgba(129,140,248,.4); }
.dark .pk-add:hover { background:rgba(99,102,241,.12); }

/* modal */
.pk-modal { max-width:980px; padding:0; }
.pk-modal-grid { display:grid; grid-template-columns:minmax(0,1fr); }
@media (min-width:900px){ .pk-modal-grid { grid-template-columns:minmax(0,1.45fr) minmax(0,1fr); } }
.pk-form { padding:20px 24px; display:flex; flex-direction:column; gap:18px; }
.pk-side { padding:20px 24px; background:#f8fafc; border-left:1px solid #eef2f6; display:none; }
@media (min-width:900px){ .pk-side { display:block; } }
.dark .pk-side { background:rgba(15,23,42,.45); border-color:#334155; }
.pk-box { border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; }
.dark .pk-box { border-color:#334155; }
.pk-box-title { font-size:12px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#64748b; margin-bottom:10px; }
.pk-row2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.pk-affix { position:relative; }
.pk-affix > i { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-style:normal; font-size:13px; color:#94a3b8; }
.pk-affix > input { padding-left:42px; }
.pk-affix > em { position:absolute; right:12px; top:50%; transform:translateY(-50%); font-style:normal; font-size:12px; color:#94a3b8; }
.pk-toggle { display:flex; align-items:flex-start; gap:12px; border:1px solid #e2e8f0; border-radius:12px; padding:12px 14px; transition:all .15s; }
.pk-toggle.on { border-color:#a5b4fc; background:linear-gradient(135deg,#eef2ff,#faf5ff); }
.dark .pk-toggle { border-color:#334155; }
.dark .pk-toggle.on { border-color:rgba(129,140,248,.5); background:rgba(99,102,241,.12); }
.pk-switch { position:relative; width:38px; height:22px; flex-shrink:0; margin-top:2px; }
.pk-switch input { position:absolute; inset:0; opacity:0; cursor:pointer; z-index:2; margin:0; }
.pk-switch span { position:absolute; inset:0; background:#cbd5e1; border-radius:999px; transition:background .15s; }
.pk-switch span::after { content:''; position:absolute; top:3px; left:3px; width:16px; height:16px; border-radius:50%; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.25); transition:transform .15s; }
.pk-switch input:checked + span { background:#6366f1; }
.pk-switch input:checked + span::after { transform:translateX(16px); }
.pk-unl { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:#64748b; cursor:pointer; user-select:none; }
.pk-chips { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
.pk-chip { font-size:11.5px; padding:3px 10px; border-radius:999px; background:#eef2ff; color:#4338ca; }
.dark .pk-chip { background:rgba(99,102,241,.2); color:#c7d2fe; }
.pk-err { background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; font-size:13px; border-radius:10px; padding:9px 12px; }
.dark .pk-err { background:rgba(239,68,68,.12); border-color:rgba(248,113,113,.4); color:#fca5a5; }
.pk-hint { font-size:11.5px; color:#94a3b8; margin-top:4px; }
.pk-x { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:22px; line-height:1; background:transparent; border:0; cursor:pointer; }
.pk-x:hover { background:#f1f5f9; color:#475569; }
.dark .pk-x:hover { background:#334155; }
.pk-spin { width:14px; height:14px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:pkspin .7s linear infinite; display:inline-block; }
@keyframes pkspin { to { transform:rotate(360deg); } }
</style>

<div class="page-container" style="padding-top:0" x-data="packageManager()" @keydown.escape.window="close(); confirmDel = null" @pkg-edit.window="openEdit($event.detail)">
    <div class="page-header">
        <div>
            <h1 class="page-title">Package Management</h1>
            <p class="page-subtitle">Plans, limits and the tools each plan includes</p>
        </div>
        <button type="button" class="btn-primary" @click="openCreate()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Package
        </button>
    </div>

    {{-- Summary --}}
    <div class="pk-summary">
        <div class="card pk-sum"><div class="pk-sum-ico">📦</div><div><b data-k="plans">{{ $summary['plans'] }}</b><span>Plans</span></div></div>
        <div class="card pk-sum"><div class="pk-sum-ico">🏪</div><div><b data-k="merchants">{{ $summary['merchants'] }}</b><span>Merchants on a plan</span></div></div>
        <div class="card pk-sum"><div class="pk-sum-ico">💎</div><div><b data-k="paid">{{ $summary['paid'] }}</b><span>On a paid plan</span></div></div>
    </div>

    <div class="pk-grid">
        @foreach($packages as $p)
        @php($tier = (float) $p->price == 0 ? 'is-free' : ((float) $p->price === (float) $maxPrice && $summary['plans'] > 1 ? 'is-premium' : ''))
        @php($feats = $p->featureList())
        @php($gv = (int) $p->giveaway_limit)
        @php($vt = (int) $p->task_limit)
        <div class="card pk-card {{ $tier }}" data-package="{{ $p->id }}">
            <div class="pk-accent"></div>
            @if($topId === $p->id)<span class="pk-ribbon" data-k="popular">Most used</span>@elseif($tier === 'is-premium')<span class="pk-ribbon">Top plan</span>@endif
            <div class="pk-head">
                <div class="pk-name">{{ $p->name }}</div>
                <div class="pk-price"><b>{{ (float) $p->price == 0 ? 'Free' : 'RM' . number_format($p->price, 0) }}</b>@if((float) $p->price != 0)<span>/ month</span>@endif</div>
                <div class="pk-subs" data-k="subs">👥 {{ $p->merchants_count }} {{ \Illuminate\Support\Str::plural('merchant', $p->merchants_count) }}</div>
            </div>
            <div class="pk-body">
                <div>
                    <div class="pk-sec">Limits</div>
                    <div class="pk-limits">
                        <div class="pk-tile"><b>{{ $p->branch_limit < 0 ? '∞' : $p->branch_limit }}</b><span>{{ \Illuminate\Support\Str::plural('Branch', $p->branch_limit < 0 ? 2 : $p->branch_limit) }}</span></div>
                        <div class="pk-tile"><b>{{ $p->staff_limit < 0 ? '∞' : $p->staff_limit }}</b><span>Staff</span></div>
                    </div>
                </div>
                <div>
                    <div class="pk-sec">Included tools</div>
                    <div class="pk-tool {{ $gv === 0 ? 'off' : 'on' }}" data-feature="giveaway">
                        <span class="pk-tool-ico">🎉</span>
                        <div><div class="pk-tool-name">Giveaway</div><div class="pk-tool-sub">Referral contests with winners</div></div>
                        <span class="pk-pill {{ $gv === 0 ? 'off' : 'on' }}">{{ $gv === 0 ? 'Not included' : ($gv < 0 ? 'Unlimited' : $gv . ' active') }}</span>
                    </div>
                    <div class="pk-tool {{ $vt === 0 ? 'off' : 'on' }}" data-feature="viral">
                        <span class="pk-tool-ico">🚀</span>
                        <div><div class="pk-tool-name">Viral Tools</div><div class="pk-tool-sub">Tasks, referral campaign &amp; review</div></div>
                        <span class="pk-pill {{ $vt === 0 ? 'off' : 'on' }}">{{ $vt === 0 ? 'Not included' : ($vt < 0 ? 'Unlimited' : $vt . ' tasks') }}</span>
                    </div>
                </div>
                @if(count($feats))
                <div>
                    <div class="pk-sec">Also includes</div>
                    <ul class="pk-feats">
                        @foreach(array_slice($feats, 0, 5) as $feat)
                        <li><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg><span>{{ $feat }}</span></li>
                        @endforeach
                    </ul>
                    @if(count($feats) > 5)<div class="pk-more">+ {{ count($feats) - 5 }} more</div>@endif
                </div>
                @endif
            </div>
            <div class="pk-foot">
                <button type="button" class="btn-secondary btn-sm" style="flex:1" @click="$dispatch('pkg-edit', @js([
                    'id' => $p->id, 'name' => $p->name, 'price' => (float) $p->price, 'branch_limit' => (int) $p->branch_limit, 'staff_limit' => (int) $p->staff_limit,
                    'giveaway_limit' => $gv, 'task_limit' => $vt, 'features' => $feats, 'merchants' => (int) $p->merchants_count,
                ]))">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.768-6.768a2.5 2.5 0 113.536 3.536L12.536 16.536 8 18l1-5z"/></svg>
                    Edit
                </button>
                <button type="button" class="btn-sm btn-danger pk-del" @if($p->merchants_count > 0) disabled title="{{ $p->merchants_count }} merchant(s) use this package" @else title="Delete package" @click="confirmDel = {{ $p->id }}; delName = @js($p->name)" @endif>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                </button>
            </div>
        </div>
        @endforeach

        <button type="button" class="card pk-card pk-add" @click="openCreate()">
            <span style="font-size:30px;line-height:1">＋</span>
            <span style="font-weight:600;font-size:14px">New package</span>
            <span style="font-size:12px;color:#94a3b8">Set the price, limits and tools</span>
        </button>
    </div>

    <div class="mt-6">{{ $packages->links() }}</div>

    {{-- ========== Add / Edit modal ========== --}}
    <div class="modal-overlay" x-show="open" x-cloak x-transition.opacity @click.self="close()">
        <div class="modal-content pk-modal" role="dialog" aria-modal="true">
            <div class="modal-header">
                <div>
                    <h2 class="text-lg font-bold" x-text="f.id ? 'Edit package' : 'Add package'"></h2>
                    <p class="text-xs text-surface-500" x-text="f.id && f.merchants ? f.merchants + ' merchant(s) are on this package: changes apply to them immediately' : 'Choose what this plan costs and includes'"></p>
                </div>
                <button type="button" class="pk-x" @click="close()" aria-label="Close">&times;</button>
            </div>

            <div class="pk-modal-grid">
                <form class="pk-form" @submit.prevent="save()">
                    <div class="pk-box">
                        <div class="pk-box-title">Plan</div>
                        <div class="pk-row2">
                            <div>
                                <label class="form-label">Name</label>
                                <input class="form-input" x-model="f.name" placeholder="e.g. Starter" maxlength="60" required>
                            </div>
                            <div>
                                <label class="form-label">Price per month</label>
                                <div class="pk-affix"><i>RM</i><input class="form-input" type="number" step="0.01" min="0" x-model="f.price" placeholder="0.00" required><em>/ mo</em></div>
                            </div>
                        </div>
                    </div>

                    <div class="pk-box">
                        <div class="pk-box-title">Limits</div>
                        <div class="pk-row2">
                            <div>
                                <label class="form-label">Branches</label>
                                <input class="form-input" type="number" min="1" x-model="f.branch" :disabled="f.branchUnl" :style="f.branchUnl ? 'opacity:.5' : ''">
                                <label class="pk-unl mt-2"><input type="checkbox" x-model="f.branchUnl"> Unlimited</label>
                            </div>
                            <div>
                                <label class="form-label">Staff accounts</label>
                                <input class="form-input" type="number" min="1" x-model="f.staff" :disabled="f.staffUnl" :style="f.staffUnl ? 'opacity:.5' : ''">
                                <label class="pk-unl mt-2"><input type="checkbox" x-model="f.staffUnl"> Unlimited</label>
                            </div>
                        </div>
                    </div>

                    <div class="pk-box">
                        <div class="pk-box-title">Included tools</div>
                        <div style="display:flex;flex-direction:column;gap:10px">
                            <div class="pk-toggle" :class="f.gOn ? 'on' : ''">
                                <label class="pk-switch"><input type="checkbox" id="f-giveaway-on" x-model="f.gOn"><span></span></label>
                                <div style="flex:1;min-width:0">
                                    <div class="text-sm font-semibold">🎉 Giveaway</div>
                                    <div class="text-xs text-surface-500">Referral contests: score, winners, claim and replacement</div>
                                    <div x-show="f.gOn" x-cloak style="display:flex;align-items:center;gap:12px;margin-top:10px;flex-wrap:wrap">
                                        <input class="form-input" id="f-giveaway" type="number" min="1" style="width:130px" x-model="f.gLimit" :disabled="f.gUnl" :style="f.gUnl ? 'width:130px;opacity:.5' : 'width:130px'" placeholder="e.g. 5">
                                        <span class="text-xs text-surface-500">active at a time</span>
                                        <label class="pk-unl"><input type="checkbox" x-model="f.gUnl"> Unlimited</label>
                                    </div>
                                </div>
                            </div>
                            <div class="pk-toggle" :class="f.tOn ? 'on' : ''">
                                <label class="pk-switch"><input type="checkbox" id="f-task-on" x-model="f.tOn"><span></span></label>
                                <div style="flex:1;min-width:0">
                                    <div class="text-sm font-semibold">🚀 Viral Tools</div>
                                    <div class="text-xs text-surface-500">Viral tasks, task analytics, referral campaign and referral review</div>
                                    <div x-show="f.tOn" x-cloak style="display:flex;align-items:center;gap:12px;margin-top:10px;flex-wrap:wrap">
                                        <input class="form-input" id="f-task" type="number" min="1" style="width:130px" x-model="f.tLimit" :disabled="f.tUnl" :style="f.tUnl ? 'width:130px;opacity:.5' : 'width:130px'" placeholder="e.g. 10">
                                        <span class="text-xs text-surface-500">active viral tasks</span>
                                        <label class="pk-unl"><input type="checkbox" x-model="f.tUnl"> Unlimited</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="pk-hint">Switched off = not part of the plan: merchants on it do not see that block on their dashboard.</p>
                    </div>

                    <div class="pk-box">
                        <div class="pk-box-title">Also includes (shown on the plan card)</div>
                        <textarea class="form-textarea" rows="4" x-model="f.features" placeholder="One per line, e.g.&#10;Advanced reports&#10;API access&#10;Priority support"></textarea>
                        <div class="pk-chips" x-show="chips.length" x-cloak><template x-for="(c, i) in chips" :key="i"><span class="pk-chip" x-text="c"></span></template></div>
                    </div>

                    <div class="pk-err" x-show="error" x-cloak x-text="error"></div>

                    <div style="display:flex;justify-content:flex-end;gap:10px">
                        <button type="button" class="btn-secondary" @click="close()">Cancel</button>
                        <button type="submit" class="btn-primary" :disabled="saving">
                            <span class="pk-spin" x-show="saving" x-cloak></span>
                            <span x-text="saving ? 'Saving…' : (f.id ? 'Save changes' : 'Create package')"></span>
                        </button>
                    </div>
                </form>

                {{-- live preview --}}
                <div class="pk-side">
                    <div class="pk-box-title">Preview</div>
                    <div class="card pk-card" :class="f.price !== '' && parseFloat(f.price) === 0 ? 'is-free' : ''">
                        <div class="pk-accent"></div>
                        <div class="pk-head">
                            <div class="pk-name" x-text="f.name || 'Package name'"></div>
                            <div class="pk-price"><b x-text="parseFloat(f.price) === 0 ? 'Free' : 'RM' + (parseFloat(f.price) || 0).toFixed(0)"></b><span x-show="parseFloat(f.price) > 0">/ month</span></div>
                        </div>
                        <div class="pk-body">
                            <div class="pk-limits">
                                <div class="pk-tile"><b x-text="f.branchUnl ? '∞' : (f.branch || '–')"></b><span>Branches</span></div>
                                <div class="pk-tile"><b x-text="f.staffUnl ? '∞' : (f.staff || '–')"></b><span>Staff</span></div>
                            </div>
                            <div>
                                <div class="pk-tool" :class="f.gOn ? 'on' : 'off'"><span class="pk-tool-ico">🎉</span><div><div class="pk-tool-name">Giveaway</div></div><span class="pk-pill" :class="f.gOn ? 'on' : 'off'" x-text="f.gOn ? (f.gUnl ? 'Unlimited' : ((f.gLimit || '?') + ' active')) : 'Not included'"></span></div>
                                <div class="pk-tool" :class="f.tOn ? 'on' : 'off'"><span class="pk-tool-ico">🚀</span><div><div class="pk-tool-name">Viral Tools</div></div><span class="pk-pill" :class="f.tOn ? 'on' : 'off'" x-text="f.tOn ? (f.tUnl ? 'Unlimited' : ((f.tLimit || '?') + ' tasks')) : 'Not included'"></span></div>
                            </div>
                            <ul class="pk-feats" x-show="chips.length" x-cloak>
                                <template x-for="(c, i) in chips.slice(0, 5)" :key="i"><li><svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg><span x-text="c"></span></li></template>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== Delete confirmation ========== --}}
    <div class="modal-overlay" x-show="confirmDel !== null" x-cloak x-transition.opacity @click.self="confirmDel = null">
        <div class="modal-content" style="max-width:420px">
            <div class="modal-body" style="padding:24px;text-align:center">
                <div style="font-size:34px">🗑️</div>
                <h3 class="text-lg font-bold mt-2">Delete this package?</h3>
                <p class="text-sm text-surface-500 mt-1"><b x-text="delName"></b> will be removed. This cannot be undone.</p>
                <div class="pk-err mt-3" x-show="delError" x-cloak x-text="delError"></div>
                <div style="display:flex;justify-content:center;gap:10px;margin-top:18px">
                    <button type="button" class="btn-secondary" @click="confirmDel = null">Cancel</button>
                    <button type="button" class="btn-danger" @click="remove()">Delete package</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function packageManager() {
    const blank = () => ({ id: null, merchants: 0, name: '', price: '', branch: 1, branchUnl: false, staff: 2, staffUnl: false,
                           gOn: false, gLimit: '', gUnl: false, tOn: false, tLimit: '', tUnl: false, features: '' });
    return {
        open: false, saving: false, error: '', confirmDel: null, delName: '', delError: '',
        f: blank(),
        get chips() { return this.f.features.split('\n').map(s => s.trim()).filter(Boolean); },
        openCreate() { this.f = blank(); this.error = ''; this.open = true; },
        openEdit(p) {
            this.f = {
                id: p.id, merchants: p.merchants || 0, name: p.name, price: p.price,
                branch: p.branch_limit < 0 ? 1 : p.branch_limit, branchUnl: p.branch_limit < 0,
                staff: p.staff_limit < 0 ? 2 : p.staff_limit, staffUnl: p.staff_limit < 0,
                gOn: p.giveaway_limit !== 0, gUnl: p.giveaway_limit < 0, gLimit: p.giveaway_limit > 0 ? p.giveaway_limit : '',
                tOn: p.task_limit !== 0, tUnl: p.task_limit < 0, tLimit: p.task_limit > 0 ? p.task_limit : '',
                features: (p.features || []).join('\n'),
            };
            this.error = ''; this.open = true;
        },
        close() { this.open = false; },
        // -1 = unlimited, 0 = not included; returns null (and sets this.error) when the number is missing or invalid
        limit(on, unl, val, label) {
            if (!on) return 0;
            if (unl) return -1;
            const n = parseInt(val, 10);
            if (isNaN(n) || n < 1) { this.error = label + ': enter a number of 1 or more, or tick Unlimited.'; return null; }
            return n;
        },
        payload() {
            this.error = '';
            const f = this.f;
            if (!f.name.trim()) { this.error = 'Give the package a name.'; return null; }
            const price = parseFloat(f.price);
            if (isNaN(price) || price < 0) { this.error = 'Enter the monthly price (0 for a free plan).'; return null; }
            const branch = f.branchUnl ? -1 : parseInt(f.branch, 10);
            const staff = f.staffUnl ? -1 : parseInt(f.staff, 10);
            if ((!f.branchUnl && (isNaN(branch) || branch < 1)) || (!f.staffUnl && (isNaN(staff) || staff < 1))) { this.error = 'Branches and staff need a number of 1 or more, or Unlimited.'; return null; }
            const g = this.limit(f.gOn, f.gUnl, f.gLimit, 'Giveaway'); if (g === null) return null;
            const t = this.limit(f.tOn, f.tUnl, f.tLimit, 'Viral Tools'); if (t === null) return null;
            return { name: f.name.trim(), price: price, branch_limit: branch, staff_limit: staff, giveaway_limit: g, task_limit: t, features: this.chips };
        },
        async send(url, method, body) {
            const res = await fetch(url, {
                method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
                body: body ? JSON.stringify(body) : undefined,
            });
            let data = {};
            try { data = await res.json(); } catch (e) {}
            return { ok: res.ok && data.success !== false, data };
        },
        message(d, fallback) {
            if (d && d.errors) return Object.values(d.errors).flat().join(' ');
            return (d && d.message) || fallback;
        },
        async save() {
            const body = this.payload();
            if (!body) return;
            this.saving = true;
            try {
                const r = await this.send(this.f.id ? '/superadmin/api/packages/' + this.f.id : '/superadmin/api/packages', this.f.id ? 'PUT' : 'POST', body);
                if (r.ok) { location.reload(); return; }
                this.error = this.message(r.data, 'Could not save the package.');
            } catch (e) { this.error = 'Network error. Please try again.'; }
            this.saving = false;
        },
        async remove() {
            this.delError = '';
            try {
                const r = await this.send('/superadmin/api/packages/' + this.confirmDel, 'DELETE');
                if (r.ok) { location.reload(); return; }
                this.delError = this.message(r.data, 'Could not delete the package.');
            } catch (e) { this.delError = 'Network error. Please try again.'; }
        },
    };
}
</script>
@endsection

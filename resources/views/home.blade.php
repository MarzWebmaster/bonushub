@extends('layouts.guest')

@section('title', 'Sistem Loyalty, Giveaway & Viral Loop Marketing Percuma')
@section('meta_description', 'BonusHub platform loyalty dengan Viral Loop System & Giveaway. Daftar PERCUMA sebagai Merchant atau Pengguna. Tingkatkan jualan, kumpul mata, menang hadiah — semua dalam satu sistem.')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════════════
     LIVE FEED NOTIFICATIONS (Social Proof)
     ═══════════════════════════════════════════════════════════════════════ --}}
<div id="live-feed-container" class="live-feed-wrapper"></div>

<script>
    const liveMessages = [
        { name: 'Ali', action: 'just won a Giveaway! 🎉', type: 'giveaway' },
        { name: 'Sarah', action: 'earned 250 points at Kedai Kopi ☕', type: 'points' },
        { name: 'Restoran Murni', action: 'gained 12 new members today 📈', type: 'merchant' },
        { name: 'Aiman', action: 'referred 5 friends this week! 🔥', type: 'referral' },
        { name: 'Maya', action: 'redeemed RM50 reward 🎁', type: 'reward' },
        { name: 'Kedai Buku Ilmu', action: 'just launched a Giveaway campaign 🚀', type: 'campaign' },
    ];
    let liveIndex = 0;

    function showLiveFeed() {
        const container = document.getElementById('live-feed-container');
        if (!container) return;
        const msg = liveMessages[liveIndex % liveMessages.length];
        liveIndex++;

        const colors = {
            giveaway: 'from-purple-600/90 to-purple-800/90 border-purple-500/30',
            points: 'from-emerald-600/90 to-emerald-800/90 border-emerald-500/30',
            merchant: 'from-bonus-600/90 to-bonus-800/90 border-bonus-500/30',
            referral: 'from-amber-600/90 to-amber-800/90 border-amber-500/30',
            reward: 'from-rose-600/90 to-rose-800/90 border-rose-500/30',
            campaign: 'from-sky-600/90 to-sky-800/90 border-sky-500/30',
        };
        const colorClass = colors[msg.type] || colors.giveaway;

        const toast = document.createElement('div');
        toast.className = `live-feed-toast bg-gradient-to-r ${colorClass} border text-white text-sm backdrop-blur-xl shadow-2xl`;
        toast.style.animation = 'liveIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards';
        toast.innerHTML = `
            <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse shrink-0"></span>
            <span><strong>${msg.name}</strong> ${msg.action}</span>
        `;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'liveOut 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }

    setInterval(showLiveFeed, 8000);
    setTimeout(showLiveFeed, 2000);
</script>

{{-- ═══════════════════════════════════════════════════════════════════════
     1. HERO SECTION
     ═══════════════════════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden bg-gradient-to-br from-gray-950 via-bonus-950 to-gray-950 min-h-[90vh] flex items-center">
    <div class="absolute inset-0 bg-grid-white opacity-[0.03]"></div>
    <div class="absolute inset-0">
        <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-bonus-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] bg-purple-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute top-1/3 left-1/4 w-[300px] h-[300px] bg-emerald-500/5 rounded-full blur-[100px]"></div>
    </div>

    {{-- Floating Badges --}}
    <div class="floating-badge top-20 left-[8%] hidden lg:block" style="animation: float 6s ease-in-out infinite">🚀</div>
    <div class="floating-badge top-40 right-[12%] hidden lg:block" style="animation: float 8s ease-in-out 1s infinite">🎁</div>
    <div class="floating-badge bottom-32 left-[15%] hidden lg:block" style="animation: float 7s ease-in-out 2s infinite">🔥</div>
    <div class="floating-badge bottom-48 right-[8%] hidden lg:block" style="animation: float 9s ease-in-out 0.5s infinite">💎</div>
    <div class="floating-badge top-1/2 right-[5%] hidden xl:block" style="animation: breathing 3s ease-in-out infinite">⭐</div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 lg:py-36 relative z-10">
        <div class="max-w-4xl mx-auto text-center">

            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-bonus-400/10 border border-bonus-400/20 rounded-full text-bonus-200 text-xs font-semibold uppercase tracking-wider mb-6 animate-fade-in">
                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                Kini Dilancarkan — Daftar PERCUMA
            </div>

            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold text-white leading-[1.1] tracking-tight mb-6 animate-fade-in">
                Platform Loyalty, Giveaway &amp;<br>
                <span class="bg-gradient-to-r from-bonus-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent">Viral Loop #1 Malaysia</span>
            </h1>

            <p class="text-lg sm:text-xl md:text-2xl text-surface-300 max-w-3xl mx-auto leading-relaxed mb-4 animate-fade-in">
                Satu platform untuk <strong class="text-white">Merchant</strong> dan <strong class="text-white">Pengguna</strong>.
            </p>

            <p class="text-base sm:text-lg text-surface-400 max-w-2xl mx-auto leading-relaxed mb-10 animate-fade-in">
                Daftar <strong class="text-emerald-400">100% PERCUMA</strong> — dapatkan akses kepada <strong class="text-white">Sistem Loyalty Points</strong>, <strong class="text-white">Cabutan Giveaway</strong>, dan <strong class="text-white">Viral Loop Marketing</strong> yang berfungsi secara automatik untuk anda.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4 animate-slide-up">
                <a href="{{ url('/login') }}"
                   class="group px-8 py-4 bg-gradient-to-r from-bonus-500 to-purple-600 text-white text-base font-bold rounded-2xl hover:from-bonus-600 hover:to-purple-700 transition-all shadow-2xl shadow-bonus-500/30 hover:shadow-bonus-500/50 flex items-center justify-center gap-2 glow-bonus">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Daftar Percuma Sebagai Merchant
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ url('/login') }}"
                   class="group px-8 py-4 bg-white/5 backdrop-blur-sm border border-white/10 text-white text-base font-bold rounded-2xl hover:bg-white/10 hover:border-white/20 transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Sertai Sebagai Pengguna — Mula Menang!
                </a>
            </div>

            <div class="mt-10 flex flex-wrap justify-center gap-6 text-sm text-surface-400 animate-fade-in">
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Percuma Seumur Hidup</span>
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Tiada Kadar Tersembunyi</span>
                <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Ganjaran & Hadiah Menarik</span>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════════
     STATS BAR (Social Proof - Animated Counters)
     ═══════════════════════════════════════════════════════════════════════ --}}
<section class="relative overflow-hidden bg-gray-950 border-y border-white/5">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 max-w-5xl mx-auto">
            <div class="text-center counter-item">
                <p class="text-3xl sm:text-4xl font-bold text-white counter-number" data-target="12847">0</p>
                <p class="text-xs sm:text-sm text-surface-400 mt-1">Pengguna Aktif</p>
            </div>
            <div class="text-center counter-item">
                <p class="text-3xl sm:text-4xl font-bold text-emerald-400 counter-number" data-target="3291">0</p>
                <p class="text-xs sm:text-sm text-surface-400 mt-1">Giveaway Dimenangi</p>
            </div>
            <div class="text-center counter-item">
                <p class="text-3xl sm:text-4xl font-bold text-purple-400 counter-number" data-target="486">0</p>
                <p class="text-xs sm:text-sm text-surface-400 mt-1">Rakan Merchant</p>
            </div>
            <div class="text-center counter-item">
                <p class="text-3xl sm:text-4xl font-bold text-bonus-400 counter-number" data-target="123456">RM 0</p>
                <p class="text-xs sm:text-sm text-surface-400 mt-1">Ganjaran Ditebus</p>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════════
     2. FEATURES OVERVIEW — BENTO GRID
     ═══════════════════════════════════════════════════════════════════════ --}}
<section id="ciri" class="py-16 md:py-24 relative overflow-hidden bg-gradient-to-b from-gray-950 via-bonus-950/50 to-gray-950">
    <div class="absolute inset-0 bg-grid-white opacity-[0.02]"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-bonus-500/5 rounded-full blur-[150px]"></div>

    {{-- Floating Badges --}}
    <div class="floating-badge top-20 left-[5%] hidden lg:block" style="animation: float 7s ease-in-out 0.3s infinite">✨</div>
    <div class="floating-badge bottom-20 right-[8%] hidden lg:block" style="animation: float 8s ease-in-out 1.5s infinite">💫</div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="text-emerald-400 font-semibold text-sm uppercase tracking-wider">Tiga Teras Utama</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3 mb-4">Semua Yang Anda Perlukan Dalam Satu Platform</h2>
            <p class="text-lg text-surface-400">Gabungan sistem loyalty, cabutan bertuah, dan viral loop yang direka untuk memaksimumkan pertumbuhan perniagaan dan ganjaran pengguna.</p>
        </div>

        {{-- Bento Grid Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 max-w-6xl mx-auto">

            {{-- Loyalty Points — Featured (col-span-2) --}}
            <div class="lg:col-span-2 group card-gradient rounded-2xl p-8 lg:p-10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-bonus-500/15 to-transparent rounded-full blur-2xl"></div>
                <div class="flex items-start gap-6">
                    <div class="w-16 h-16 min-w-[64px] rounded-2xl bg-gradient-to-br from-bonus-500 to-purple-600 flex items-center justify-center shrink-0 shadow-lg shadow-bonus-500/30 group-hover:scale-110 transition-transform duration-500" style="animation: glowPulse 3s ease-in-out infinite">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xl font-bold text-white mb-3">Sistem Loyalty Points</h3>
                        <p class="text-surface-300 leading-relaxed max-w-xl text-base">Pengguna mengumpul mata setiap kali membuat pembelian di kedai-kedai yang berdaftar. Mata ini boleh ditebus untuk pelbagai ganjaran menarik — semuanya diurus secara automatik oleh platform.</p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-surface-400">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Auto-kredit points</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Real-time balance</span>
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Multi-merchant</span>
                </div>
            </div>

            {{-- Giveaway — Compact (col-span-1) --}}
            <div class="lg:col-span-1 group card-gradient rounded-2xl p-8 relative overflow-hidden flex flex-col">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-gold-500/15 to-transparent rounded-full blur-2xl"></div>
                <div class="w-14 h-14 min-w-[56px] rounded-2xl bg-gradient-to-br from-gold-500 to-amber-600 flex items-center justify-center mb-5 shadow-lg shadow-gold-500/30 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Cabutan Giveaway</h3>
                <p class="text-surface-300 leading-relaxed text-base flex-1">Setiap pembelian atau perkongsian layak menyertai cabutan bertuah! Merchant boleh mencipta kempen giveaway untuk menarik pelanggan baru, manakala pengguna berpeluang memenangi hadiah-hadiah hebat.</p>
                <div class="mt-4 inline-flex items-center gap-1.5 text-sm text-gold-400">
                    <span>🔥</span> Hadiah bernilai tinggi menanti
                </div>
            </div>

            {{-- Viral Loop System — Full Width (col-span-3) --}}
            <div class="lg:col-span-3 group card-gradient rounded-2xl p-8 lg:p-10 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-60 h-60 bg-gradient-to-br from-emerald-500/10 to-transparent rounded-full blur-3xl"></div>
                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-6 lg:gap-10">
                    <div class="w-16 h-16 min-w-[64px] rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shrink-0 shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform duration-500" style="animation: glowPulse 3s ease-in-out infinite">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-xl font-bold text-white mb-3">Viral Loop System</h3>
                        <p class="text-surface-300 leading-relaxed max-w-3xl text-base">Sistem rujukan berantai paling pintar. Apabila pelanggan merujuk rakan, kedua-duanya mendapat mata bonus. Rakan yang dirujuk pula akan merujuk rakan yang lain — mencipta kesan viral yang meledakkan pertumbuhan perniagaan anda secara automatik.</p>
                    </div>
                    <div class="hidden lg:flex flex-col items-center gap-2 px-6 py-4 dark-card rounded-xl border border-emerald-500/20 shrink-0 bg-gray-900/80 shadow-lg">
                        <span class="text-3xl">📈</span>
                        <span class="text-sm text-emerald-400 font-semibold">Viral Loop</span>
                        <span class="text-2xl font-bold text-white">3.2x</span>
                        <span class="text-xs text-surface-400">purata rujukan</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════════
     3. BENEFITS TO CUSTOMER
     ═══════════════════════════════════════════════════════════════════════ --}}
<section class="py-16 md:py-24 relative overflow-hidden bg-gradient-to-br from-gray-950 via-emerald-950/20 to-gray-950">
    <div class="absolute inset-0 bg-grid-white opacity-[0.02]"></div>
    <div class="absolute top-20 left-0 w-96 h-96 bg-emerald-500/5 rounded-full blur-[120px]"></div>

    <div class="floating-badge top-32 right-[10%] hidden lg:block" style="animation: float 8s ease-in-out 1s infinite">🎯</div>
    <div class="floating-badge bottom-20 left-[8%] hidden lg:block" style="animation: float 7s ease-in-out 2.5s infinite">🏆</div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center max-w-6xl mx-auto">

            <div>
                <span class="text-emerald-400 font-semibold text-sm uppercase tracking-wider">Untuk Pengguna</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3 mb-6">Dapatkan Ganjaran Setiap Kali Anda Membeli!</h2>
                <p class="text-lg text-surface-400 mb-8">Sebagai pengguna, anda boleh menikmati pelbagai manfaat tanpa perlu mengeluarkan sebarang kos.</p>

                <div class="space-y-6">
                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 min-w-[48px] rounded-xl bg-emerald-500/15 flex items-center justify-center shrink-0 border border-emerald-500/20">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white">Pendaftaran 100% PERCUMA Seumur Hidup</h3>
                            <p class="text-base text-surface-400 mt-1">Tiada yuran pendaftaran, tiada caj bulanan. Daftar sekali, nikmati selamanya.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 min-w-[48px] rounded-xl bg-emerald-500/15 flex items-center justify-center shrink-0 border border-emerald-500/20">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white">Bebas Memilih Mana-Mana Kedai</h3>
                            <p class="text-base text-surface-400 mt-1">Anda bebas menyertai dan mengumpul mata di semua Merchant yang berdaftar dalam sistem BonusHub.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 min-w-[48px] rounded-xl bg-emerald-500/15 flex items-center justify-center shrink-0 border border-emerald-500/20">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white">Peluang Menang Hadiah Besar</h3>
                            <p class="text-base text-surface-400 mt-1">Setiap pembelian atau perkongsian memberi anda tiket ke cabutan giveaway. Lebih banyak anda berinteraksi, lebih tinggi peluang anda menang!</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 min-w-[48px] rounded-xl bg-emerald-500/15 flex items-center justify-center shrink-0 border border-emerald-500/20">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white">Ganjaran Mudah Ditebus</h3>
                            <p class="text-base text-surface-400 mt-1">Tukarkan mata ganjaran anda dengan mudah melalui platform. Proses tebusan cepat, telus, dan tanpa kerumitan.</p>
                        </div>
                    </div>
                </div>

                <a href="{{ url('/login') }}" class="mt-8 inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-emerald-700 transition-all shadow-lg shadow-emerald-500/25 glow-emerald">
                    Mula Dapatkan Ganjaran
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>

            <div class="relative">
                <div class="absolute -inset-4 bg-gradient-to-br from-emerald-500/10 to-purple-600/10 rounded-3xl blur-xl"></div>
                <div class="relative card-dark rounded-3xl p-8 lg:p-10 overflow-hidden" style="transform: perspective(1000px) rotateY(-2deg);">
                    <div class="absolute -top-20 -right-20 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl"></div>
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/10 text-emerald-400 text-xs font-semibold rounded-full border border-emerald-500/20 mb-4">Pelbagai Ganjaran Menanti</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="card-dark rounded-2xl p-5 text-center hover:bg-gray-700/80 transition-colors">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gold-400 to-amber-500 flex items-center justify-center mx-auto mb-3 shadow-lg" style="animation: glowPulse 3s ease-in-out infinite">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-2xl font-bold text-white">Mata Points</p>
                                <p class="text-xs text-surface-400">Kumpul & tebus</p>
                            </div>
                            <div class="card-dark rounded-2xl p-5 text-center hover:bg-gray-700/80 transition-colors">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-bonus-400 to-purple-500 flex items-center justify-center mx-auto mb-3 shadow-lg" style="animation: glowPulse 3s ease-in-out 0.5s infinite">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                </div>
                                <p class="text-2xl font-bold text-white">Giveaway</p>
                                <p class="text-sm text-surface-400">Cabutan hadiah</p>
                            </div>
                            <div class="card-dark rounded-2xl p-5 text-center hover:bg-gray-700/80 transition-colors">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center mx-auto mb-3 shadow-lg" style="animation: glowPulse 3s ease-in-out 1s infinite">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <p class="text-2xl font-bold text-white">Bonus Rujukan</p>
                                <p class="text-sm text-surface-400">Bawa rakan & menang</p>
                            </div>
                            <div class="card-dark rounded-2xl p-5 text-center hover:bg-gray-700/80 transition-colors">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-400 to-pink-500 flex items-center justify-center mx-auto mb-3 shadow-lg" style="animation: glowPulse 3s ease-in-out 1.5s infinite">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                                </div>
                                <p class="text-2xl font-bold text-white">Reward</p>
                                <p class="text-sm text-surface-400">Tebus sekarang</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════════
     4. BENEFITS TO MERCHANT
     ═══════════════════════════════════════════════════════════════════════ --}}
<section class="py-16 md:py-24 relative overflow-hidden bg-gradient-to-br from-gray-950 via-purple-950/20 to-gray-950">
    <div class="absolute inset-0 bg-grid-white opacity-[0.02]"></div>
    <div class="absolute top-20 right-0 w-96 h-96 bg-purple-500/5 rounded-full blur-[120px]"></div>

    <div class="floating-badge top-32 left-[8%] hidden lg:block" style="animation: float 9s ease-in-out 0.5s infinite">💼</div>
    <div class="floating-badge bottom-20 right-[12%] hidden lg:block" style="animation: float 7s ease-in-out 2s infinite">📊</div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center max-w-6xl mx-auto">

            <div class="relative order-2 lg:order-1">
                <div class="absolute -inset-4 bg-gradient-to-br from-bonus-500/10 to-purple-600/10 rounded-3xl blur-xl"></div>
                <div class="relative card-dark rounded-3xl p-8 lg:p-10 overflow-hidden" style="transform: perspective(1000px) rotateY(2deg);">
                    <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-purple-500/15 rounded-full blur-3xl"></div>
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-bonus-500/15 text-bonus-400 text-sm font-semibold rounded-full border border-bonus-500/20 mb-4">Tool Lengkap Untuk Merchant</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="card-dark rounded-2xl p-5 text-center hover:bg-gray-700/80 transition-colors">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-bonus-400 to-purple-500 flex items-center justify-center mx-auto mb-3 shadow-lg" style="animation: glowPulse 3s ease-in-out infinite">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <p class="text-2xl font-bold text-white">Analitik</p>
                                <p class="text-sm text-surface-400">Dashboard real-time</p>
                            </div>
                            <div class="card-dark rounded-2xl p-5 text-center hover:bg-gray-700/80 transition-colors">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center mx-auto mb-3 shadow-lg" style="animation: glowPulse 3s ease-in-out 0.5s infinite">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <p class="text-2xl font-bold text-white">Unlimited</p>
                                <p class="text-sm text-surface-400">Pelanggan</p>
                            </div>
                            <div class="card-dark rounded-2xl p-5 text-center hover:bg-gray-700/80 transition-colors">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gold-400 to-amber-500 flex items-center justify-center mx-auto mb-3 shadow-lg" style="animation: glowPulse 3s ease-in-out 1s infinite">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <p class="text-2xl font-bold text-white">Viral Loop</p>
                                <p class="text-sm text-surface-400">Auto referral</p>
                            </div>
                            <div class="card-dark rounded-2xl p-5 text-center hover:bg-gray-700/80 transition-colors">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-400 to-pink-500 flex items-center justify-center mx-auto mb-3 shadow-lg" style="animation: glowPulse 3s ease-in-out 1.5s infinite">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                </div>
                                <p class="text-2xl font-bold text-white">Giveaway</p>
                                <p class="text-sm text-surface-400">Kempen jualan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <span class="text-purple-400 font-semibold text-sm uppercase tracking-wider">Untuk Merchant</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3 mb-6">Kembangkan Perniagaan Anda Secara Viral & Automatik!</h2>
                <p class="text-lg text-surface-400 mb-8">Tool lengkap untuk membina kesetiaan pelanggan dan meledakkan jualan tanpa perlu keluar modal besar.</p>

                <div class="space-y-6">
                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 min-w-[48px] rounded-xl bg-purple-500/15 flex items-center justify-center shrink-0 border border-purple-500/20">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white">Pendaftaran PERCUMA Selamanya</h3>
                            <p class="text-base text-surface-400 mt-1">Akaun asas percuma tanpa had masa. Nikmati semua ciri teras tanpa dikenakan sebarang yuran.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 min-w-[48px] rounded-xl bg-purple-500/15 flex items-center justify-center shrink-0 border border-purple-500/20">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white">Tiada Had Pelanggan (Unlimited)</h3>
                            <p class="text-base text-surface-400 mt-1">Daftarkan seramai mungkin pelanggan tanpa had. Semakin ramai, semakin kuat ekosistem loyalty anda.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 min-w-[48px] rounded-xl bg-purple-500/15 flex items-center justify-center shrink-0 border border-purple-500/20">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white">Viral Loop System Automatik</h3>
                            <p class="text-base text-surface-400 mt-1">Pelanggan sedia ada menjadi ejen pemasaran anda. Sistem referral pintar memberi insentif kepada mereka untuk membawa pelanggan baru secara berantai.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 min-w-[48px] rounded-xl bg-purple-500/15 flex items-center justify-center shrink-0 border border-purple-500/20">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white">Dashboard Analitik Lengkap</h3>
                            <p class="text-base text-surface-400 mt-1">Pantau data pelanggan, retention rate, jumlah mata diedar, dan pulangan pelaburan (ROI) program loyalty anda dalam masa nyata.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-5">
                        <div class="w-12 h-12 min-w-[48px] rounded-xl bg-purple-500/15 flex items-center justify-center shrink-0 border border-purple-500/20">
                            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-white">Kempen Giveaway Mudah</h3>
                            <p class="text-base text-surface-400 mt-1">Cipta dan lancarkan kempen cabutan bertuah dalam beberapa klik. Giveaway adalah cara paling berkesan untuk melonjakkan jualan dan menarik pelanggan baru.</p>
                        </div>
                    </div>
                </div>

                <a href="{{ url('/login') }}" class="mt-8 inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white font-semibold rounded-xl hover:from-purple-600 hover:to-purple-700 transition-all shadow-lg shadow-purple-500/25 glow-purple">
                    Daftar Kedai Anda Sekarang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════════
     5. HOW IT WORKS
     ═══════════════════════════════════════════════════════════════════════ --}}
<section id="cara-kerja" class="py-16 md:py-24 relative overflow-hidden bg-gradient-to-br from-gray-950 via-bonus-950/30 to-gray-950">
    <div class="absolute inset-0 bg-grid-white opacity-[0.02]"></div>
    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-bonus-500/5 rounded-full blur-[120px]"></div>

    <div class="floating-badge top-16 right-[12%] hidden lg:block" style="animation: float 8s ease-in-out 1.2s infinite">📋</div>
    <div class="floating-badge bottom-24 left-[6%] hidden lg:block" style="animation: float 7s ease-in-out 0.8s infinite">✅</div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="text-bonus-400 font-semibold text-sm uppercase tracking-wider">Cara Ia Berfungsi</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3 mb-4">Mulakan Hanya Dalam 3 Langkah Mudah</h2>
            <p class="text-lg text-surface-400">Proses yang ringkas dan pantas untuk kedua-dua Merchant dan Pengguna.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 max-w-6xl mx-auto">

            {{-- Merchant Steps --}}
            <div class="card-dark rounded-2xl p-8 lg:p-10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-40 h-40 bg-bonus-500/10 rounded-full blur-2xl"></div>
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 min-w-[48px] rounded-xl bg-gradient-to-br from-bonus-500 to-purple-600 flex items-center justify-center shadow-lg shadow-bonus-500/30" style="animation: glowPulse 3s ease-in-out infinite">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white">Untuk Merchant</h3>
                </div>

                <div class="space-y-8">
                    <div class="flex gap-6">
                        <div class="flex flex-col items-center">
                            <div class="step-number bg-gradient-to-br from-bonus-500 to-purple-600 text-white shadow-lg shadow-bonus-500/30">1</div>
                            <div class="flex-1 w-px bg-gradient-to-b from-bonus-500/30 to-transparent mt-2"></div>
                        </div>
                        <div class="pb-2">
                            <h4 class="text-base font-semibold text-white mb-1">Daftar Akaun Merchant</h4>
                            <p class="text-base text-surface-400">Isi borang pendaftaran percuma. Tiada yuran tersembunyi. Sahkan emel anda dan mula sedia dalam masa 5 minit.</p>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="flex flex-col items-center">
                            <div class="step-number bg-gradient-to-br from-bonus-500 to-purple-600 text-white shadow-lg shadow-bonus-500/30">2</div>
                            <div class="flex-1 w-px bg-gradient-to-b from-bonus-500/30 to-transparent mt-2"></div>
                        </div>
                        <div class="pb-2">
                            <h4 class="text-base font-semibold text-white mb-1">Setkan Kadar Loyalty & Giveaway</h4>
                            <p class="text-base text-surface-400">Tentukan berapa mata untuk setiap RM pembelian, dan cipta kempen giveaway pertama anda. Sistem akan berfungsi secara automatik.</p>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="flex flex-col items-center">
                            <div class="step-number bg-gradient-to-br from-bonus-500 to-purple-600 text-white shadow-lg shadow-bonus-500/30">3</div>
                        </div>
                        <div>
                            <h4 class="text-base font-semibold text-white mb-1">Pantau & Kembangkan</h4>
                            <p class="text-base text-surface-400">Gunakan dashboard analitik untuk melihat prestasi, dan biarkan Viral Loop System membawa lebih ramai pelanggan kepada anda secara automatik.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Customer Steps --}}
            <div class="card-dark rounded-2xl p-8 lg:p-10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-40 h-40 bg-emerald-500/10 rounded-full blur-2xl"></div>
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 min-w-[48px] rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30" style="animation: glowPulse 3s ease-in-out 0.5s infinite">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white">Untuk Pengguna</h3>
                </div>

                <div class="space-y-8">
                    <div class="flex gap-6">
                        <div class="flex flex-col items-center">
                            <div class="step-number bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30">1</div>
                            <div class="flex-1 w-px bg-gradient-to-b from-emerald-500/30 to-transparent mt-2"></div>
                        </div>
                        <div class="pb-2">
                            <h4 class="text-base font-semibold text-white mb-1">Daftar Akaun Pengguna</h4>
                            <p class="text-base text-surface-400">Daftar secara PERCUMA menggunakan emel atau nombor telefon. Lengkapkan profil dan mula terokai kedai-kedai menarik dalam platform.</p>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="flex flex-col items-center">
                            <div class="step-number bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30">2</div>
                            <div class="flex-1 w-px bg-gradient-to-b from-emerald-500/30 to-transparent mt-2"></div>
                        </div>
                        <div class="pb-2">
                            <h4 class="text-base font-semibold text-white mb-1">Beli & Kumpul Mata</h4>
                            <p class="text-base text-surface-400">Buat pembelian di mana-mana kedai berdaftar. Mata loyalty akan dikredit secara automatik. Kongsikan dengan rakan untuk bonus rujukan tambahan.</p>
                        </div>
                    </div>

                    <div class="flex gap-6">
                        <div class="flex flex-col items-center">
                            <div class="step-number bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/30">3</div>
                        </div>
                        <div>
                            <h4 class="text-base font-semibold text-white mb-1">Tebus Ganjaran & Menang Giveaway</h4>
                            <p class="text-base text-surface-400">Tukarkan mata dengan pelbagai hadiah menarik, dan dapatkan tiket cabutan giveaway untuk peluang memenangi hadiah besar secara percuma!</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════════
     6. SOCIAL PROOF & FAQ
     ═══════════════════════════════════════════════════════════════════════ --}}
<section class="py-16 md:py-24 relative overflow-hidden bg-gradient-to-b from-gray-950 via-gray-950 to-gray-950">
    <div class="absolute inset-0 bg-grid-white opacity-[0.02]"></div>
    <div class="absolute top-0 left-0 w-96 h-96 bg-bonus-500/5 rounded-full blur-[120px]"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500/5 rounded-full blur-[120px]"></div>

    <div class="floating-badge top-20 left-[12%] hidden lg:block" style="animation: float 8s ease-in-out 0.3s infinite">💬</div>
    <div class="floating-badge bottom-20 right-[10%] hidden lg:block" style="animation: float 7s ease-in-out 1.8s infinite">⭐</div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

        {{-- Testimonials --}}
        <div class="max-w-3xl mx-auto text-center mb-14">
            <span class="text-bonus-400 font-semibold text-sm uppercase tracking-wider">Apa Kata Mereka</span>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3 mb-4">Dipercayai Oleh Usahawan & Pengguna</h2>
            <p class="text-lg text-surface-400">Ribuan Merchant dan Pengguna telah menyertai BonusHub. Inilah pengalaman mereka.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto mb-16">
            <div class="card-dark rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-bonus-500/15 rounded-full blur-2xl"></div>
                <div class="flex gap-1 mb-4">
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="text-surface-300 text-base leading-relaxed mb-4">"Sejak guna BonusHub, pelanggan saya semakin ramai. Viral Loop System betul-betul berkesan — pelanggan lama bawa pelanggan baru tanpa saya perlu buat apa-apa!"</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-bonus-400 to-purple-500 flex items-center justify-center text-white font-bold text-sm shadow-md">AN</div>
                    <div>
                        <p class="font-semibold text-white text-base">Ahmad N.</p>
                        <p class="text-sm text-surface-400">Pengusaha Kafe, Kuala Lumpur</p>
                    </div>
                </div>
            </div>

            <div class="card-dark rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-emerald-500/15 rounded-full blur-2xl"></div>
                <div class="flex gap-1 mb-4">
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="text-surface-300 text-base leading-relaxed mb-4">"Saya suka sebab boleh kumpul mata di banyak kedai berbeza. Giveaway dia pun best, saya dah menang hadiah twice! Memang recommended untuk semua pengguna."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-sm shadow-md">SN</div>
                    <div>
                        <p class="font-semibold text-white text-base">Siti N.</p>
                        <p class="text-sm text-surface-400">Pengguna Setia, Shah Alam</p>
                    </div>
                </div>
            </div>

            <div class="card-dark rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-gold-500/15 rounded-full blur-2xl"></div>
                <div class="flex gap-1 mb-4">
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <svg class="w-5 h-5 text-gold-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </div>
                <p class="text-surface-300 text-base leading-relaxed mb-4">"Dashboard analitik sangat membantu. Saya boleh track customer retention dan lihat program giveaway mana yang paling berkesan. ROI meningkat 40% dalam 2 bulan pertama!"</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gold-400 to-amber-500 flex items-center justify-center text-white font-bold text-sm shadow-md">MR</div>
                    <div>
                        <p class="font-semibold text-white text-base">Mohan R.</p>
                        <p class="text-sm text-surface-400">Pemilik Rantaian Kedai, Pulau Pinang</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- FAQ --}}
        <div id="faq" class="max-w-3xl mx-auto">
            <div class="text-center mb-10">
                <span class="text-bonus-400 font-semibold text-sm uppercase tracking-wider">Soalan Lazim</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3">Ada Soalan? Kami Ada Jawapan</h2>
            </div>

            <div id="faq-accordion" class="space-y-3">
                @php
                    $faqs = [
                        [
                            'q' => 'Apakah itu Sistem Loyalty dan bagaimana ia berfungsi?',
                            'a' => 'Sistem Loyalty adalah program ganjaran yang membolehkan pengguna mengumpul mata setiap kali membuat pembelian di kedai-kedai berdaftar. Mata ini boleh ditebus untuk pelbagai ganjaran. Merchant boleh menetapkan kadar mata mereka sendiri — contohnya 1 mata untuk setiap RM1 pembelian. Sistem berfungsi sepenuhnya secara automatik, dari pengiraan mata sehinggalah kepada proses penebusan.'
                        ],
                        [
                            'q' => 'Adakah benar-benar PERCUMA untuk mendaftar?',
                            'a' => 'Ya! Pendaftaran adalah 100% PERCUMA untuk kedua-dua Merchant dan Pengguna. Tiada yuran pendaftaran, tiada caj bulanan, dan tiada yuran tersembunyi. Akaun asas Merchant kekal percuma selamanya. Kami percaya setiap perniagaan berhak mendapat sistem loyalty tanpa perlu risau tentang kos.'
                        ],
                        [
                            'q' => 'Bagaimana Viral Loop System membantu perniagaan saya?',
                            'a' => 'Viral Loop System adalah teknologi pemasaran rujukan berantai paling pintar. Apabila pelanggan anda merujuk rakan mereka, kedua-duanya mendapat mata bonus. Rakan yang dirujuk kemudiannya akan merujuk lebih ramai rakan — mewujudkan kesan viral yang melipatgandakan pertumbuhan pelanggan anda secara organik, tanpa perlu anda belanja besar untuk iklan.'
                        ],
                        [
                            'q' => 'Apakah itu Giveaway dan bagaimana ia berfungsi?',
                            'a' => 'Giveaway adalah cabutan bertuah di mana pengguna berpeluang memenangi hadiah-hadiah menarik. Merchant boleh mencipta kempen giveaway dengan mudah — tetapkan hadiah, tempoh masa, dan syarat penyertaan. Setiap pembelian atau perkongsian memberikan pengguna tiket untuk cabutan. Ini adalah strategi viral marketing paling berkesan untuk menarik pelanggan baru dan meningkatkan jualan.'
                        ],
                        [
                            'q' => 'Apa yang membezakan BonusHub daripada aplikasi kad setia yang lain?',
                            'a' => 'BonusHub adalah platform komprehensif yang menggabungkan 3 fungsi utama dalam satu sistem: Sistem Loyalty Points, Cabutan Giveaway, dan Viral Loop System. Kebanyakan aplikasi kad setia hanya menawarkan fungsi asas loyalty sahaja. BonusHub juga menyediakan dashboard analitik lengkap untuk Merchant dan membolehkan pengguna mengumpul mata di pelbagai kedai berbeza — bukan terikat kepada satu perniagaan sahaja.'
                        ],
                        [
                            'q' => 'Bagaimana cara untuk memulakan sebagai Merchant?',
                            'a' => 'Sangat mudah! Hanya daftar akaun secara percuma, lengkapkan profil kedai anda, dan tetapkan kadar mata loyalty yang ingin diberikan. Anda boleh mula mendaftarkan pelanggan serta-merta dan mencipta kempen giveaway pertama anda dalam masa beberapa minit. Tiada pengalaman teknikal diperlukan.'
                        ],
                    ];
                @endphp

                @foreach ($faqs as $index => $faq)
                <div class="faq-item card-dark rounded-xl overflow-hidden transition-all duration-200 hover:bg-gray-700/80 border-gray-700/50">
                    <button
                        onclick="toggleFaq(this)"
                        class="faq-trigger w-full flex items-center justify-between px-6 py-5 text-left bg-transparent transition-colors"
                        aria-expanded="false">
                        <h3 class="font-semibold text-white pr-4 text-base sm:text-lg">{{ $faq['q'] }}</h3>
                        <svg class="faq-icon w-6 h-6 text-bonus-400 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                    <div class="faq-content px-6 max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                        <p class="pb-5 text-base text-surface-400 leading-relaxed">{{ $faq['a'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <script>
                function toggleFaq(button) {
                    const item = button.closest('.faq-item');
                    const content = item.querySelector('.faq-content');
                    const icon = item.querySelector('.faq-icon');
                    const isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';

                    document.querySelectorAll('.faq-item').forEach(el => {
                        const c = el.querySelector('.faq-content');
                        const i = el.querySelector('.faq-icon');
                        const b = el.querySelector('.faq-trigger');
                        c.style.maxHeight = '0px';
                        i.classList.remove('rotate-45');
                        b.setAttribute('aria-expanded', 'false');
                        el.classList.remove('shadow-[0_12px_48px_rgba(0,0,0,0.6)]', 'border-bonus-500/40');
                    });

                    if (!isOpen) {
                        content.style.maxHeight = content.scrollHeight + 'px';
                        icon.classList.add('rotate-45');
                        button.setAttribute('aria-expanded', 'true');
                        item.classList.add('shadow-[0_12px_48px_rgba(0,0,0,0.6)]', 'border-bonus-500/40');
                    }
                }
            </script>
        </div>

    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════════
     FINAL CTA SECTION
     ═══════════════════════════════════════════════════════════════════════ --}}
<section class="py-16 md:py-20 relative overflow-hidden bg-gradient-to-br from-bonus-600 via-purple-600 to-indigo-800">
    <div class="absolute inset-0 bg-grid-white opacity-[0.05]"></div>
    <div class="absolute top-0 right-0 w-80 h-80 bg-bonus-300/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-purple-300/20 rounded-full blur-3xl"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gold-400/10 rounded-full blur-3xl"></div>

    <div class="floating-badge top-12 left-[10%] hidden lg:block" style="animation: float 7s ease-in-out 0.5s infinite">🚀</div>
    <div class="floating-badge bottom-12 right-[12%] hidden lg:block" style="animation: float 8s ease-in-out 1.5s infinite">🎯</div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4">
                Jangan Tunggu Lagi — <span class="text-bonus-200">Sertai BonusHub Sekarang!</span>
            </h2>
            <p class="text-lg sm:text-xl text-bonus-100 max-w-2xl mx-auto mb-8">
                Ribuan Merchant dan Pengguna sudah mula meraih manfaat. Daftar PERCUMA hari ini dan jadilah sebahagian daripada revolusi loyalty & viral marketing di Malaysia.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ url('/login') }}"
                   class="group px-8 py-4 bg-white text-bonus-700 text-base font-bold rounded-2xl hover:bg-bonus-50 transition-all shadow-2xl flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Daftar Percuma Sebagai Merchant
                </a>
                <a href="{{ url('/login') }}"
                   class="group px-8 py-4 bg-white/10 backdrop-blur-sm border border-white/20 text-white text-base font-bold rounded-2xl hover:bg-white/20 transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Sertai Sebagai Pengguna
                </a>
            </div>
            <p class="mt-6 text-sm text-bonus-200/80">Percuma seumur hidup &bull; Tiada kad tersembunyi &bull; Batal bila-bila masa</p>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════════════════
     COUNTER ANIMATION SCRIPT
     ═══════════════════════════════════════════════════════════════════════ --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const counters = document.querySelectorAll('.counter-number');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.dataset.target);
                    const isRupiah = el.dataset.target === '123456';
                    let current = 0;
                    const step = Math.max(1, Math.floor(target / 80));
                    const interval = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            current = target;
                            clearInterval(interval);
                        }
                        if (isRupiah) {
                            el.textContent = 'RM ' + current.toLocaleString();
                        } else {
                            el.textContent = current.toLocaleString();
                        }
                    }, 25);
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.3 });
        counters.forEach(c => observer.observe(c));
    });
</script>

@endsection

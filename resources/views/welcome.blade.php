<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BonusHub — Rewards & Loyalty Platform Malaysia</title>
    <meta name="description" content="BonusHub helps businesses build customer loyalty with points, tiers, and rewards. Malaysia's #1 loyalty rewards platform." />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            {{-- Fallback only -- will use inline Tailwind via CDN or compiled --}}
            body { font-family: 'Inter', sans-serif; }
        </style>
    @endif
</head>
<body class="bg-surface-950 text-white antialiased">

    {{-- ─── FLOATING GRADIENT ORBS ─── --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute -top-40 -right-40 w-[600px] h-[600px] rounded-full bg-gradient-to-br from-bonus-500/20 via-purple-600/15 to-transparent blur-3xl animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full bg-gradient-to-tr from-amber-500/10 via-pink-600/10 to-transparent blur-3xl animate-float-slow"></div>
        <div class="absolute top-1/3 left-1/4 w-[400px] h-[400px] rounded-full bg-gradient-to-r from-cyan-500/5 via-bonus-400/10 to-transparent blur-3xl animate-float-delayed"></div>
        <div class="absolute top-1/2 right-1/4 w-[350px] h-[350px] rounded-full bg-gradient-to-l from-purple-600/10 via-bonus-500/8 to-transparent blur-3xl animate-drift"></div>
    </div>

    {{-- ─── GRID OVERLAY ─── --}}
    <div class="fixed inset-0 pointer-events-none bg-grid-dark opacity-40 -z-10"></div>

    {{-- ─── NAVBAR ─── --}}
    <nav class="fixed top-0 left-0 right-0 z-50 backdrop-blur-xl border-b border-white/[0.06] bg-surface-950/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="/" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-bonus-400 to-purple-600 flex items-center justify-center shadow-lg shadow-bonus-500/25">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight">
                        <span class="bg-gradient-to-r from-bonus-300 to-purple-400 bg-clip-text text-transparent">Bonus</span>
                        <span class="text-white">Hub</span>
                    </span>
                </a>

                {{-- Nav Links --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-sm font-medium text-surface-300 hover:text-white transition-colors">Features</a>
                    <a href="#how-it-works" class="text-sm font-medium text-surface-300 hover:text-white transition-colors">How It Works</a>
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-surface-300 hover:text-white transition-colors">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-surface-300 hover:text-white transition-colors">Login</a>
                        @endauth
                    @endif
                </div>

                {{-- CTA Button --}}
                @if (Route::has('register'))
                    @guest
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white
                                  bg-gradient-to-r from-bonus-500 to-purple-600
                                  hover:from-bonus-400 hover:to-purple-500
                                  shadow-lg shadow-bonus-500/25 hover:shadow-bonus-500/40
                                  transition-all duration-300 hover:-translate-y-0.5">
                            Get Started Free
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @endguest
                @endif

                {{-- Mobile menu button --}}
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg text-surface-400 hover:text-white hover:bg-white/10 transition-colors"
                        onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="hidden md:hidden border-t border-white/[0.06] bg-surface-900/95 backdrop-blur-xl">
            <div class="px-4 py-4 space-y-3">
                <a href="#features" class="block px-3 py-2 rounded-lg text-sm text-surface-300 hover:text-white hover:bg-white/5 transition-colors">Features</a>
                <a href="#how-it-works" class="block px-3 py-2 rounded-lg text-sm text-surface-300 hover:text-white hover:bg-white/5 transition-colors">How It Works</a>
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="block px-3 py-2 rounded-lg text-sm text-surface-300 hover:text-white hover:bg-white/5 transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg text-sm text-surface-300 hover:text-white hover:bg-white/5 transition-colors">Login</a>
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    {{-- ─── HERO SECTION ─── --}}
    <section class="relative min-h-screen flex items-center pt-20 pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="grid lg:grid-cols-2 gap-16 items-center">

                {{-- Left: Hero Content --}}
                <div class="relative z-10">
                    {{-- Pill badge --}}
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider
                                bg-bonus-500/10 border border-bonus-500/20 text-bonus-300 mb-8 animate-fade-in">
                        <span class="w-2 h-2 rounded-full bg-bonus-400 animate-pulse-slow"></span>
                        Malaysia's #1 Loyalty Platform
                    </div>

                    <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black leading-none tracking-tight animate-slide-up">
                        <span class="text-white">Turn Every</span><br>
                        <span class="bg-gradient-to-r from-bonus-300 via-purple-300 to-amber-300 bg-clip-text text-transparent">Customer Into</span><br>
                        <span class="text-white">A Loyal Fan</span>
                    </h1>

                    <p class="mt-8 text-lg sm:text-xl text-surface-400 leading-relaxed max-w-xl animate-slide-up" style="animation-delay: 0.1s;">
                        Reward your customers with points, tiers, and exclusive perks.
                        BonusHub makes loyalty simple — no code, no hassle, just happy customers coming back for more.
                    </p>

                    <div class="flex flex-wrap gap-4 mt-10 animate-slide-up" style="animation-delay: 0.2s;">
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl text-base font-semibold text-white
                                  bg-gradient-to-r from-bonus-500 to-purple-600
                                  hover:from-bonus-400 hover:to-purple-500
                                  shadow-xl shadow-bonus-500/25 hover:shadow-bonus-500/40
                                  transition-all duration-300 hover:-translate-y-1">
                            Start Free Trial
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="#how-it-works"
                           class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl text-base font-medium text-surface-300
                                  border border-white/10 hover:border-white/20 hover:text-white
                                  bg-white/5 hover:bg-white/10
                                  transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            See How It Works
                        </a>
                    </div>

                    {{-- Trust indicators --}}
                    <div class="flex flex-wrap items-center gap-6 mt-12 pt-8 border-t border-white/[0.06] animate-fade-in" style="animation-delay: 0.3s;">
                        <div class="flex -space-x-2">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-xs font-bold text-white ring-2 ring-surface-950">A</div>
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-bonus-400 to-purple-600 flex items-center justify-center text-xs font-bold text-white ring-2 ring-surface-950">M</div>
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-400 to-green-500 flex items-center justify-center text-xs font-bold text-white ring-2 ring-surface-950">S</div>
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center text-xs font-bold text-white ring-2 ring-surface-950">K</div>
                        </div>
                        <div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            </div>
                            <p class="text-xs text-surface-500 mt-0.5">Trusted by 500+ businesses across Malaysia</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Hero Visual --}}
                <div class="hidden lg:flex relative items-center justify-center">
                    <div class="relative w-full max-w-[480px]">
                        {{-- Main glass card mockup --}}
                        <div class="glass-card p-8 animate-tilt-in">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-bonus-400 to-purple-600 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-white">Dashboard</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse-slow"></span>
                                    <span class="text-xs text-surface-400">Live</span>
                                </div>
                            </div>

                            {{-- Stats row --}}
                            <div class="grid grid-cols-3 gap-3 mb-6">
                                <div class="bg-white/5 rounded-xl p-3 text-center">
                                    <p class="text-2xl font-black text-bonus-300">1.2k</p>
                                    <p class="text-[10px] text-surface-400 mt-0.5">Members</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-3 text-center">
                                    <p class="text-2xl font-black text-amber-300">8.5k</p>
                                    <p class="text-[10px] text-surface-400 mt-0.5">Points Earned</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-3 text-center">
                                    <p class="text-2xl font-black text-emerald-300">156</p>
                                    <p class="text-[10px] text-surface-400 mt-0.5">Rewards</p>
                                </div>
                            </div>

                            {{-- Recent activity list --}}
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.04] border border-white/[0.06]">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-xs font-bold text-white">AK</div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-white truncate">Ahmad Kasim</p>
                                        <p class="text-xs text-emerald-400">+250 points — Gold Tier</p>
                                    </div>
                                    <span class="text-xs text-surface-500">2m ago</span>
                                </div>
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.04] border border-white/[0.06]">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-bonus-400 to-purple-600 flex items-center justify-center text-xs font-bold text-white">SN</div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-white truncate">Siti Nurhaliza</p>
                                        <p class="text-xs text-bonus-300">+100 points — Silver Tier</p>
                                    </div>
                                    <span class="text-xs text-surface-500">5m ago</span>
                                </div>
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.04] border border-white/[0.06]">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-400 to-green-500 flex items-center justify-center text-xs font-bold text-white">RF</div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-white truncate">Reward claimed</p>
                                        <p class="text-xs text-amber-300">RM50 Voucher — Premium</p>
                                    </div>
                                    <span class="text-xs text-surface-500">8m ago</span>
                                </div>
                            </div>
                        </div>

                        {{-- Floating badges --}}
                        <div class="floating-badge -top-4 -right-4 animate-float">
                            <div class="bg-gradient-to-br from-amber-400 to-orange-500 rounded-xl px-4 py-2 shadow-xl shadow-amber-500/20">
                                <p class="text-xs font-bold text-white">🎯 5x Points</p>
                            </div>
                        </div>
                        <div class="floating-badge -bottom-4 -left-4 animate-float-slow">
                            <div class="bg-gradient-to-br from-emerald-400 to-green-500 rounded-xl px-4 py-2 shadow-xl shadow-emerald-500/20">
                                <p class="text-xs font-bold text-white">🏆 Platinum Tier</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── FEATURES SECTION ─── --}}
    <section id="features" class="relative py-24 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider
                            bg-bonus-500/10 border border-bonus-500/20 text-bonus-300 mb-4">Features</span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black tracking-tight">
                    Everything You Need to<br>
                    <span class="bg-gradient-to-r from-bonus-300 to-purple-300 bg-clip-text text-transparent">Build Customer Loyalty</span>
                </h2>
                <p class="mt-4 text-surface-400 text-lg max-w-2xl mx-auto">
                    From points to tiers to rewards — BonusHub gives you the tools to create a loyalty program your customers will love.
                </p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {{-- Feature 1 --}}
                <div class="glass-card group p-6 hover:bg-white/[0.08] transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-bonus-400 to-purple-600 flex items-center justify-center mb-5 shadow-lg shadow-bonus-500/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Points Rewards</h3>
                    <p class="text-sm text-surface-400 leading-relaxed">Set your own earn rates. Customers earn points with every purchase — automatically.</p>
                </div>

                {{-- Feature 2 --}}
                <div class="glass-card group p-6 hover:bg-white/[0.08] transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center mb-5 shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0016.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 01-2.77.896m0 0a6.023 6.023 0 01-2.77-.896" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Loyalty Tiers</h3>
                    <p class="text-sm text-surface-400 leading-relaxed">Silver, Gold, Platinum — create custom tiers with escalating perks to keep customers engaged.</p>
                </div>

                {{-- Feature 3 --}}
                <div class="glass-card group p-6 hover:bg-white/[0.08] transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-green-500 flex items-center justify-center mb-5 shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Rewards Catalogue</h3>
                    <p class="text-sm text-surface-400 leading-relaxed">Vouchers, physical items, digital downloads — let customers redeem points for what they love.</p>
                </div>

                {{-- Feature 4 --}}
                <div class="glass-card group p-6 hover:bg-white/[0.08] transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center mb-5 shadow-lg shadow-cyan-500/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Smart Analytics</h3>
                    <p class="text-sm text-surface-400 leading-relaxed">Track customer behaviour, redemption patterns, and campaign ROI with real-time dashboards.</p>
                </div>

                {{-- Feature 5 --}}
                <div class="glass-card group p-6 hover:bg-white/[0.08] transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center mb-5 shadow-lg shadow-pink-500/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Secure & Reliable</h3>
                    <p class="text-sm text-surface-400 leading-relaxed">Enterprise-grade security with encrypted data, fraud detection, and automatic backups.</p>
                </div>

                {{-- Feature 6 --}}
                <div class="glass-card group p-6 hover:bg-white/[0.08] transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-400 to-purple-600 flex items-center justify-center mb-5 shadow-lg shadow-purple-500/20 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.75 12.75M6 12l-2.25 3M6 12l3-1.5m0 0l2.25-4.5m-2.25 4.5l3 3m3 0l2.25 3m-2.25-3l2.25-4.5m-2.25 4.5l1.5 1.5m4.5-7.5L18 12l-1.5 4.5M12 6l1.5-3M12 6l-1.5-3M6 12l6 6m0-12l6-6" /></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">POS Integration</h3>
                    <p class="text-sm text-surface-400 leading-relaxed">Seamlessly connects with your existing POS system. No hardware changes needed.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── HOW IT WORKS ─── --}}
    <section id="how-it-works" class="relative py-24 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider
                            bg-bonus-500/10 border border-bonus-500/20 text-bonus-300 mb-4">How It Works</span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black tracking-tight">
                    Get Started in<br>
                    <span class="bg-gradient-to-r from-bonus-300 to-purple-300 bg-clip-text text-transparent">3 Simple Steps</span>
                </h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                {{-- Step 1 --}}
                <div class="text-center group">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-bonus-400 to-purple-600 flex items-center justify-center mb-6 shadow-xl shadow-bonus-500/20 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-2xl font-black text-white">1</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-3">Sign Up Free</h3>
                    <p class="text-sm text-surface-400 leading-relaxed">Create your merchant account in under 2 minutes. No credit card needed.</p>
                </div>

                {{-- Connector line --}}
                <div class="hidden md:flex items-start justify-center pt-8">
                    <div class="w-full max-w-[120px] h-px bg-gradient-to-r from-bonus-500/50 via-purple-500/50 to-transparent"></div>
                </div>

                {{-- Step 2 --}}
                <div class="text-center group">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center mb-6 shadow-xl shadow-amber-500/20 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-2xl font-black text-white">2</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-3">Customize Rules</h3>
                    <p class="text-sm text-surface-400 leading-relaxed">Set your points earn rate, tier thresholds, and reward catalogue in minutes.</p>
                </div>

                {{-- Connector line --}}
                <div class="hidden md:flex items-start justify-center pt-8">
                    <div class="w-full max-w-[120px] h-px bg-gradient-to-r from-amber-500/50 via-orange-500/50 to-transparent"></div>
                </div>

                {{-- Step 3 --}}
                <div class="text-center group">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-emerald-400 to-green-500 flex items-center justify-center mb-6 shadow-xl shadow-emerald-500/20 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-2xl font-black text-white">3</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-3">Go Live!</h3>
                    <p class="text-sm text-surface-400 leading-relaxed">Your loyalty program goes live instantly. Customers start earning and redeeming.</p>
                </div>
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl text-base font-semibold text-white
                          bg-gradient-to-r from-bonus-500 to-purple-600
                          hover:from-bonus-400 hover:to-purple-500
                          shadow-xl shadow-bonus-500/25 hover:shadow-bonus-500/40
                          transition-all duration-300 hover:-translate-y-1">
                    Start Your Free Trial
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- ─── STATS SECTION ─── --}}
    <section class="relative py-24 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="glass-card p-8 md:p-12">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 divide-y sm:divide-y-0 sm:divide-x divide-white/[0.06]">
                    <div class="text-center sm:pr-8">
                        <p class="text-4xl md:text-5xl font-black bg-gradient-to-r from-bonus-300 to-purple-400 bg-clip-text text-transparent">10K+</p>
                        <p class="text-sm text-surface-400 mt-2">Active Members</p>
                    </div>
                    <div class="text-center sm:px-8 pt-8 sm:pt-0">
                        <p class="text-4xl md:text-5xl font-black bg-gradient-to-r from-amber-300 to-orange-400 bg-clip-text text-transparent">500+</p>
                        <p class="text-sm text-surface-400 mt-2">Registered Businesses</p>
                    </div>
                    <div class="text-center sm:px-8 pt-8 sm:pt-0">
                        <p class="text-4xl md:text-5xl font-black bg-gradient-to-r from-emerald-300 to-green-400 bg-clip-text text-transparent">250K</p>
                        <p class="text-sm text-surface-400 mt-2">Points Issued</p>
                    </div>
                    <div class="text-center sm:pl-8 pt-8 sm:pt-0">
                        <p class="text-4xl md:text-5xl font-black bg-gradient-to-r from-pink-300 to-rose-400 bg-clip-text text-transparent">98%</p>
                        <p class="text-sm text-surface-400 mt-2">Satisfaction Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── CTA SECTION ─── --}}
    <section class="relative py-24 overflow-hidden">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="relative">
                {{-- Background glow --}}
                <div class="absolute inset-0 flex items-center justify-center -z-10">
                    <div class="w-[400px] h-[400px] rounded-full bg-gradient-to-r from-bonus-500/15 to-purple-600/15 blur-3xl animate-breathing"></div>
                </div>

                <h2 class="text-3xl sm:text-4xl md:text-5xl font-black tracking-tight mb-6">
                    Ready to Transform<br>
                    <span class="bg-gradient-to-r from-bonus-300 via-purple-300 to-amber-300 bg-clip-text text-transparent">Your Customer Loyalty?</span>
                </h2>
                <p class="text-surface-400 text-lg max-w-xl mx-auto mb-10">
                    Join hundreds of Malaysian businesses using BonusHub to reward their customers and drive repeat sales.
                </p>
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 px-10 py-4 rounded-xl text-lg font-bold text-white
                          bg-gradient-to-r from-bonus-500 to-purple-600
                          hover:from-bonus-400 hover:to-purple-500
                          shadow-2xl shadow-bonus-500/30 hover:shadow-bonus-500/50
                          transition-all duration-300 hover:-translate-y-1">
                    Get Started — It's Free
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <p class="text-xs text-surface-500 mt-4">No credit card required • Cancel anytime • Free forever plan available</p>
            </div>
        </div>
    </section>

    {{-- ─── FOOTER ─── --}}
    <footer class="border-t border-white/[0.06] py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-bonus-400 to-purple-600 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                        </div>
                        <span class="text-lg font-bold">
                            <span class="bg-gradient-to-r from-bonus-300 to-purple-400 bg-clip-text text-transparent">Bonus</span>Hub
                        </span>
                    </div>
                    <p class="text-sm text-surface-500 max-w-xs">Making customer loyalty simple for Malaysian businesses. Reward. Retain. Repeat.</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-white mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-sm text-surface-400 hover:text-white transition-colors">Features</a></li>
                        <li><a href="#how-it-works" class="text-sm text-surface-400 hover:text-white transition-colors">How It Works</a></li>
                        <li><a href="{{ route('register') }}" class="text-sm text-surface-400 hover:text-white transition-colors">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-white mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><span class="text-sm text-surface-500">Marz Technology & Trading</span></li>
                        <li><span class="text-sm text-surface-500">Malaysia</span></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 pt-8 border-t border-white/[0.06] text-center">
                <p class="text-xs text-surface-500">© {{ date('Y') }} BonusHub by Marz Technology & Trading. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>

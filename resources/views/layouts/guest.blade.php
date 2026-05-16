<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'BonusHub - Sistem Loyalty, Giveaway & Viral Marketing Percuma. Daftar sebagai Merchant atau Pengguna dan tingkatkan jualan dengan sistem rujukan pintar.')">
    <title>@yield('title', 'BonusHub') - BonusHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-50 text-surface-800 antialiased font-sans min-h-screen flex flex-col">

    {{-- Public Navbar --}}
    <nav class="bg-white/90 backdrop-blur-lg border-b border-surface-200 shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:h-20">
                <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-bonus-500 to-purple-600 flex items-center justify-center shadow-lg shadow-bonus-500/30 group-hover:shadow-bonus-500/50 transition-shadow">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-bonus-600 to-purple-600 bg-clip-text text-transparent">BonusHub</span>
                </a>

                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ url('/') }}" class="text-sm font-medium text-surface-600 hover:text-bonus-600 transition-colors">Laman Utama</a>
                    <a href="#ciri" class="text-sm font-medium text-surface-600 hover:text-bonus-600 transition-colors">Ciri-Ciri</a>
                    <a href="#cara-kerja" class="text-sm font-medium text-surface-600 hover:text-bonus-600 transition-colors">Cara Kerja</a>
                    <a href="#faq" class="text-sm font-medium text-surface-600 hover:text-bonus-600 transition-colors">FAQ</a>
                    <a href="{{ url('/login') }}" class="text-sm font-medium text-surface-600 hover:text-bonus-600 transition-colors">Log Masuk</a>
                    <a href="{{ url('/login') }}" class="px-5 py-2.5 bg-gradient-to-r from-bonus-500 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-bonus-600 hover:to-purple-700 transition-all shadow-lg shadow-bonus-500/25 hover:shadow-bonus-500/40">Daftar Percuma</a>
                </div>

                <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="md:hidden p-2.5 rounded-xl text-surface-500 hover:bg-surface-100 transition-colors" aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden border-t border-surface-100 bg-white">
            <div class="px-4 py-4 space-y-2">
                <a href="{{ url('/') }}" class="block px-4 py-2.5 text-sm font-medium text-surface-600 rounded-xl hover:bg-surface-100">Laman Utama</a>
                <a href="#ciri" class="block px-4 py-2.5 text-sm font-medium text-surface-600 rounded-xl hover:bg-surface-100">Ciri-Ciri</a>
                <a href="#cara-kerja" class="block px-4 py-2.5 text-sm font-medium text-surface-600 rounded-xl hover:bg-surface-100">Cara Kerja</a>
                <a href="#faq" class="block px-4 py-2.5 text-sm font-medium text-surface-600 rounded-xl hover:bg-surface-100">FAQ</a>
                <a href="{{ url('/login') }}" class="block px-4 py-2.5 text-sm font-medium text-surface-600 rounded-xl hover:bg-surface-100">Log Masuk</a>
                <a href="{{ url('/login') }}" class="block px-4 py-2.5 text-center text-sm font-semibold text-white bg-gradient-to-r from-bonus-500 to-purple-600 rounded-xl">Daftar Percuma</a>
            </div>
        </div>
    </nav>

    <main class="flex-1">
        @yield('content')
    </main>

    @include('layouts.partials.footer')

</body>
</html>

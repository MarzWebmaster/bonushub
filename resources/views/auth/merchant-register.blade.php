@extends('layouts.guest')
@section('title', 'Daftar Merchant')
@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg">
        <div class="card p-8 shadow-xl">
            {{-- Header --}}
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-surface-800">Daftar Sebagai Merchant</h1>
                <p class="text-surface-500 mt-1">Mula program loyalty untuk bisnes anda</p>
                <p class="text-xs text-surface-400 mt-1">✅ Percuma • ✅ Aktif serta-merta selepas pengesahan</p>
            </div>

            {{-- Step indicators --}}
            <div class="flex items-center justify-center mb-6">
                <div class="flex items-center">
                    <div id="step1-indicator" class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm font-bold">1</div>
                    <div id="step1-line" class="w-12 h-1 bg-gray-200 mx-2"></div>
                    <div id="step2-indicator" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">2</div>
                    <div id="step2-line" class="w-12 h-1 bg-gray-200 mx-2"></div>
                    <div id="step3-indicator" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">3</div>
                </div>
            </div>

            <form action="{{ route('merchant.register.post') }}" method="POST" class="space-y-4" id="merchant-register-form">
                @csrf
                <input type="hidden" name="_t" value="{{ time() }}">

                {{-- HONEYPOT — hidden from humans, visible to bots --}}
                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                {{-- STEP 1: Company & Email --}}
                <div id="step1">
                    <div class="mb-4">
                        <p class="text-sm text-surface-500 text-center">Langkah 1: Maklumat Syarikat</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Nama Syarikat</label>
                        <input type="text" name="company_name" id="company_name" class="form-input" placeholder="Contoh: Kedai Kopi Ali Sdn Bhd" required autofocus value="{{ old('company_name') }}">
                        @error('company_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Nama Anda</label>
                        <input type="text" name="name" id="name" class="form-input" placeholder="Ali bin Abu" required value="{{ old('name') }}">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-input" placeholder="ali@kedaikopi.com" required value="{{ old('email') }}">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="button" onclick="requestOtp()" id="request-otp-btn" class="btn-primary w-full py-3 text-base">
                        <span id="request-otp-text">Request Verification Code</span>
                        <span id="request-otp-loading" class="hidden">
                            <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Menghantar...
                        </span>
                    </button>
                </div>

                {{-- STEP 2: OTP Verification --}}
                <div id="step2" class="hidden">
                    <div class="mb-4">
                        <p class="text-sm text-surface-500 text-center">Langkah 2: Sahkan Email</p>
                    </div>

                    <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-surface-700">Kod dihantar ke:</p>
                                <p class="text-sm text-emerald-600 font-semibold" id="otp-email-display"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Masukkan Kod 6 Digit</label>
                        <input type="text" name="otp" id="otp-input" class="form-input text-center text-2xl tracking-[0.5em] font-mono" placeholder="000000" maxlength="6" required pattern="[0-9]{6}" inputmode="numeric">
                    </div>

                    <button type="button" onclick="verifyOtp()" id="verify-otp-btn" class="btn-primary w-full py-3 text-base mb-3">
                        <span id="verify-otp-text">Verify Code</span>
                        <span id="verify-otp-loading" class="hidden">
                            <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Mengesahkan...
                        </span>
                    </button>

                    <div class="text-center">
                        <button type="button" onclick="resendOtp()" id="resend-btn" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium" disabled>
                            Hantar Semula Kod (<span id="countdown">60</span>s)
                        </button>
                    </div>

                    <div class="mt-3 p-3 rounded-lg bg-amber-50 border border-amber-200">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <div>
                                <p class="text-sm text-amber-700 font-medium">Tak nampak email?</p>
                                <p class="text-xs text-amber-600">Sila semak folder <strong>Spam</strong> atau <strong>Promotions</strong> anda.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 text-center">
                        <button type="button" onclick="goToStep1()" class="text-sm text-surface-500 hover:text-surface-700">
                            ← Tukar Email
                        </button>
                    </div>
                </div>

                {{-- STEP 3: Phone & Password --}}
                <div id="step3" class="hidden">
                    <div class="mb-4">
                        <p class="text-sm text-surface-500 text-center">Langkah 3: Lengkapkan Pendaftaran</p>
                    </div>

                    <div class="p-3 rounded-lg bg-green-50 border border-green-200 mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-sm text-green-700 font-medium">Email berjaya disahkan! ✓</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">No. Telefon</label>
                        <input type="text" name="phone" id="phone" class="form-input" placeholder="0123456789" required value="{{ old('phone') }}">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Kata Laluan</label>
                        <input type="password" name="password" id="password" class="form-input" placeholder="Min. 8 aksara" required>
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Sahkan Kata Laluan</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Ulang kata laluan" required>
                    </div>

                    {{-- Cloudflare Turnstile — only shown when keys configured --}}
                    @if(config('services.turnstile.site_key') && !str_contains(config('services.turnstile.site_key'), 'placeholder'))
                    <div class="flex justify-center mb-4">
                        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light" data-language="ms"></div>
                    </div>
                    @error('turnstile')
                        <p class="text-red-500 text-xs text-center">{{ $message }}</p>
                    @enderror
                    @endif

                    <button type="submit" class="btn-primary w-full py-3 text-base flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Aktifkan Akaun Merchant
                    </button>
                </div>

                @if($errors->any())
                    <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-600">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
            </form>

            <p class="text-center text-sm text-surface-500 mt-6">
                Dah ada akaun? <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">Log Masuk</a>
            </p>

            <p class="text-center text-xs text-surface-400 mt-3">
                Pelanggan? <a href="{{ route('register') }}" class="text-bonus-600 hover:text-bonus-700">Daftar sebagai pengguna</a>
            </p>
        </div>
    </div>
</div>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script>
let emailVerified = false;
let countdownInterval = null;

function goToStep1() {
    document.getElementById('step1').classList.remove('hidden');
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step3').classList.add('hidden');
    updateIndicators(1);
}

function goToStep2() {
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.remove('hidden');
    document.getElementById('step3').classList.add('hidden');
    updateIndicators(2);
    document.getElementById('otp-email-display').textContent = document.getElementById('email').value;
    startCountdown();
}

function goToStep3() {
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step3').classList.remove('hidden');
    updateIndicators(3);
}

function updateIndicators(step) {
    document.getElementById('step1-indicator').className = step >= 1
        ? 'w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm font-bold'
        : 'w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold';

    document.getElementById('step2-indicator').className = step >= 2
        ? 'w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm font-bold'
        : 'w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold';

    document.getElementById('step3-indicator').className = step >= 3
        ? 'w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-sm font-bold'
        : 'w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold';

    document.getElementById('step1-line').className = step >= 2
        ? 'w-12 h-1 bg-emerald-500 mx-2'
        : 'w-12 h-1 bg-gray-200 mx-2';

    document.getElementById('step2-line').className = step >= 3
        ? 'w-12 h-1 bg-emerald-500 mx-2'
        : 'w-12 h-1 bg-gray-200 mx-2';
}

async function requestOtp() {
    const company = document.getElementById('company_name').value;
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;

    if (!company || !name || !email) {
        alert('Sila isi nama syarikat, nama, dan email terlebih dahulu.');
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Sila masukkan email yang sah.');
        return;
    }

    const btn = document.getElementById('request-otp-btn');
    const text = document.getElementById('request-otp-text');
    const loading = document.getElementById('request-otp-loading');

    btn.disabled = true;
    text.classList.add('hidden');
    loading.classList.remove('hidden');

    try {
        const response = await fetch('{{ route("otp.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                type: 'registration'
            })
        });

        const data = await response.json();

        if (data.success) {
            goToStep2();
        } else {
            alert(data.message || 'Gagal menghantar kod. Sila cuba lagi.');
        }
    } catch (error) {
        alert('Ralat jaringan. Sila cuba lagi.');
    } finally {
        btn.disabled = false;
        text.classList.remove('hidden');
        loading.classList.add('hidden');
    }
}

async function verifyOtp() {
    const email = document.getElementById('email').value;
    const otp = document.getElementById('otp-input').value;

    if (!otp || otp.length !== 6) {
        alert('Sila masukkan kod 6 digit.');
        return;
    }

    const btn = document.getElementById('verify-otp-btn');
    const text = document.getElementById('verify-otp-text');
    const loading = document.getElementById('verify-otp-loading');

    btn.disabled = true;
    text.classList.add('hidden');
    loading.classList.remove('hidden');

    try {
        const response = await fetch('{{ route("otp.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                otp: otp,
                type: 'registration'
            })
        });

        const data = await response.json();

        if (data.success) {
            emailVerified = true;
            goToStep3();
        } else {
            alert(data.message || 'Kod tidak sah. Sila cuba lagi.');
            document.getElementById('otp-input').value = '';
            document.getElementById('otp-input').focus();
        }
    } catch (error) {
        alert('Ralat jaringan. Sila cuba lagi.');
    } finally {
        btn.disabled = false;
        text.classList.remove('hidden');
        loading.classList.add('hidden');
    }
}

async function resendOtp() {
    const email = document.getElementById('email').value;

    try {
        const response = await fetch('{{ route("otp.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                type: 'registration'
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('Kod baru telah dihantar!');
            startCountdown();
        } else {
            alert(data.message || 'Gagal menghantar kod.');
        }
    } catch (error) {
        alert('Ralat jaringan.');
    }
}

function startCountdown() {
    let seconds = 60;
    const btn = document.getElementById('resend-btn');
    const display = document.getElementById('countdown');

    btn.disabled = true;
    display.textContent = seconds;

    if (countdownInterval) clearInterval(countdownInterval);

    countdownInterval = setInterval(() => {
        seconds--;
        display.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(countdownInterval);
            btn.disabled = false;
            display.textContent = '0';
        }
    }, 1000);
}

// Auto-submit on 6 digits
document.getElementById('otp-input')?.addEventListener('input', function(e) {
    if (this.value.length === 6) {
        verifyOtp();
    }
});
</script>
@endsection

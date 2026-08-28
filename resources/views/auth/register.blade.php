@extends('layouts.guest')
@section('title', 'Register')
@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="card p-8 shadow-xl">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-bonus-500 to-purple-600 flex items-center justify-center shadow-lg shadow-bonus-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-surface-800">Join BonusHub</h1>
                <p class="text-surface-500 mt-1">Create your account to start earning rewards</p>

                @if($campaign)
                <div class="mt-4 p-3 rounded-lg bg-gradient-to-r from-bonus-50 to-purple-50 border border-bonus-200">
                    <div class="flex items-center justify-center gap-2 text-sm">
                        <span class="text-lg">🎯</span>
                        <span class="font-semibold text-bonus-700">{{ $campaign->merchant->company_name ?? 'Merchant' }}</span>
                        <span class="text-surface-600">— {{ $campaign->name }}</span>
                    </div>
                    <p class="text-xs text-surface-500 mt-1">Register now to join this merchant's loyalty program!</p>
                </div>
                @endif
            </div>

            {{-- Step indicators --}}
            <div class="flex items-center justify-center mb-6">
                <div class="flex items-center">
                    <div id="step1-indicator" class="w-8 h-8 rounded-full bg-bonus-500 text-white flex items-center justify-center text-sm font-bold">1</div>
                    <div id="step1-line" class="w-12 h-1 bg-gray-200 mx-2"></div>
                    <div id="step2-indicator" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">2</div>
                    <div id="step2-line" class="w-12 h-1 bg-gray-200 mx-2"></div>
                    <div id="step3-indicator" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">3</div>
                </div>
            </div>

            <form action="{{ route('register.post') }}" method="POST" class="space-y-4" id="register-form">
                @csrf
                @if($ref)
                    <input type="hidden" name="ref" value="{{ $ref }}">
                @endif

                {{-- STEP 1: Name & Email --}}
                <div id="step1">
                    <div class="mb-4">
                        <p class="text-sm text-surface-500 text-center">Langkah 1: Maklumat Asas</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" class="form-input" placeholder="Ali bin Abu" required autofocus value="{{ old('name') }}">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-input" placeholder="ali@email.com" required value="{{ old('email') }}">
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

                    <div class="p-4 rounded-lg bg-bonus-50 border border-bonus-200 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-bonus-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-bonus-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-surface-700">Kod dihantar ke:</p>
                                <p class="text-sm text-bonus-600 font-semibold" id="otp-email-display"></p>
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
                        <button type="button" onclick="resendOtp()" id="resend-btn" class="text-sm text-bonus-600 hover:text-bonus-700 font-medium" disabled>
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
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-input" placeholder="0123456789" required value="{{ old('phone') }}">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn-primary w-full py-3 text-base">Create Account</button>
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
                Already have an account? <a href="{{ route('login') }}" class="text-bonus-600 hover:text-bonus-700 font-medium">Sign In</a>
            </p>
        </div>
    </div>
</div>

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
    // Step 1
    document.getElementById('step1-indicator').className = step >= 1
        ? 'w-8 h-8 rounded-full bg-bonus-500 text-white flex items-center justify-center text-sm font-bold'
        : 'w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold';

    // Step 2
    document.getElementById('step2-indicator').className = step >= 2
        ? 'w-8 h-8 rounded-full bg-bonus-500 text-white flex items-center justify-center text-sm font-bold'
        : 'w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold';

    // Step 3
    document.getElementById('step3-indicator').className = step >= 3
        ? 'w-8 h-8 rounded-full bg-bonus-500 text-white flex items-center justify-center text-sm font-bold'
        : 'w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold';

    // Lines
    document.getElementById('step1-line').className = step >= 2
        ? 'w-12 h-1 bg-bonus-500 mx-2'
        : 'w-12 h-1 bg-gray-200 mx-2';

    document.getElementById('step2-line').className = step >= 3
        ? 'w-12 h-1 bg-bonus-500 mx-2'
        : 'w-12 h-1 bg-gray-200 mx-2';
}

async function requestOtp() {
    const email = document.getElementById('email').value;
    const name = document.getElementById('name').value;

    if (!name || !email) {
        alert('Sila isi nama dan email terlebih dahulu.');
        return;
    }

    // Basic email validation
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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>SIKAP - Reset Password</title>
    <link rel="icon" href="{{ asset('assets/logo-lanri.png') }}" type="image/x-icon">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen bg-gradient-to-br flex items-center justify-center p-4">

<div class="w-full max-w-md">
    {{-- Brand --}}
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-lg mb-3">
            <img src="{{ asset('assets/logo-lanri.png') }}" alt="SIKAP" class="w-14 h-14 object-contain" />
        </div>
        <h1 class="text-2xl font-extrabold">SIKAP</h1>
        <p class="text-xs tracking-widest uppercase mt-1">Reset Password</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8">
        {{-- Step 1: Input Email --}}
        <div id="step-email">
            <h2 class="text-lg font-bold text-gray-800 mb-1">Lupa Password?</h2>
            <p class="text-xs text-gray-500 mb-6">Masukkan email terdaftar. Kami akan kirim kode OTP.</p>

            <form id="form-email" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Email</label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" id="email" name="email" required
                            class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="email@contoh.com" />
                    </div>
                </div>

                <button type="submit" id="btn-send-otp"
                    class="w-full bg-gradient-to-r from-blue-800 to-blue-600 hover:from-blue-900 hover:to-blue-700 text-white font-semibold py-3 rounded-lg transition shadow-lg shadow-blue-200 flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Kode OTP
                </button>
            </form>
        </div>

        {{-- Step 2: Input OTP + Password Baru --}}
        <div id="step-reset" class="hidden">
            <h2 class="text-lg font-bold text-gray-800 mb-1">Verifikasi & Reset</h2>
            <p class="text-xs text-gray-500 mb-6">Cek email Anda. Kode berlaku 15 menit.</p>

            <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg mb-4">
                <p class="text-[10px] text-blue-700 uppercase font-bold tracking-wider mb-1">Email Tujuan</p>
                <p class="text-xs font-mono text-blue-900" id="display-email">-</p>
            </div>

            <form id="form-reset" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Kode OTP (6 digit)</label>
                    <input type="text" id="otp" name="otp" maxlength="6" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-lg text-center text-2xl font-mono tracking-[0.5em] focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="000000" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Password Baru</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-3 pr-10 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="Min 12 karakter" />
                        <button type="button" onclick="togglePassword('password', 'eye-pass')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="eye-pass" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1">Min 12 karakter, huruf besar, kecil, angka, simbol.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5 uppercase tracking-wide">Konfirmasi Password</label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            class="w-full px-4 py-3 pr-10 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            placeholder="Ulangi password baru" />
                        <button type="button" onclick="togglePassword('password_confirmation', 'eye-confirm')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="eye-confirm" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" id="btn-reset"
                    class="w-full bg-gradient-to-r from-green-700 to-green-600 hover:from-green-800 hover:to-green-700 text-white font-semibold py-3 rounded-lg transition shadow-lg shadow-green-200 flex items-center justify-center gap-2">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    Reset Password
                </button>

                <button type="button" onclick="backToEmail()"
                    class="w-full text-gray-500 hover:text-gray-700 text-xs font-medium py-2 transition">
                    Kembali
                </button>
            </form>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center mt-4">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-blue-100 hover:text-white text-xs font-semibold transition">
            <i data-lucide="arrow-left" class="w-3 h-3"></i>
            Kembali ke Login
        </a>
    </div>
</div>

<script>
window.FORGOT_PASSWORD_DATA = {
    routes: {
        sendOtp: @json(route('forgot-password.send-otp')),
        reset: @json(route('forgot-password.reset')),
    },
    csrf: @json(csrf_token()),
};
</script>

<script src="{{ asset('js/pages/forgot-password.js') }}?v={{ filemtime(public_path('js/pages/forgot-password.js')) }}"></script>

<script>
    if (window.lucide) lucide.createIcons();
</script>
</body>
</html>
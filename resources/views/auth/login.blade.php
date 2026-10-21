<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>SIKAP - Login</title>
    <link rel="icon" href="{{ asset('assets/logo-lanri.png') }}" type="image/x-icon">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="h-screen overflow-hidden bg-white">

<div class="flex h-screen">

    {{-- ================= PANEL KIRI - BRANDING ================= --}}
    <div class="hidden lg:flex lg:w-1/2 hero-panel text-white flex-col justify-between p-12 relative overflow-hidden">
        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-2xl bg-white/95 p-2 shadow-lg">
                    <img src="{{ asset('assets/logo-lanri.png') }}" alt="SIKAP" class="w-full h-full object-contain" />
                </div>
                <div>
                    <h2 class="text-xl font-extrabold tracking-wide">SIKAP</h2>
                    <p class="text-[11px] text-blue-100 tracking-widest uppercase">Sistem Informasi Keuangan &amp; Anggaran</p>
                </div>
            </div>
            <p class="text-[10px] text-blue-200 tracking-[0.2em] uppercase mt-6">PUSJAR SKMP</p>
        </div>

        <div class="relative z-10 max-w-lg">
            <h1 class="text-4xl xl:text-5xl font-extrabold leading-tight mb-6">
                Sistem Informasi<br />
                Keuangan &amp; Anggaran Pusjar SKMP<br />
            </h1>
            <p class="text-blue-100 text-sm leading-relaxed max-w-md">
                Bersama membangun tata kelola keuangan yang transparan,
                akuntabel, dan terukur untuk Indonesia yang lebih baik.
            </p>
        </div>

        <div class="relative z-10 flex items-center gap-3 text-xs text-blue-200">
            <div class="w-8 h-px bg-blue-300"></div>
            <span>© 2026 SIKAP · PUSJAR SKMP</span>
        </div>

        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-[400px] h-[400px] bg-blue-300/20 rounded-full blur-3xl pointer-events-none"></div>

        <svg class="absolute inset-0 w-full h-full opacity-[0.07] pointer-events-none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="32" height="32" patternUnits="userSpaceOnUse">
                    <path d="M 32 0 L 0 0 0 32" fill="none" stroke="white" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)"/>
        </svg>
    </div>

    {{-- ================= PANEL KANAN - FORM LOGIN ================= --}}
    <div class="w-full lg:w-1/2 flex flex-col form-panel overflow-y-auto">
        <div class="lg:hidden flex items-center justify-between px-6 pt-6 pb-2">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 p-1.5">
                    <img src="{{ asset('assets/logo-lanri.png') }}" alt="SIKAP" class="w-full h-full object-contain" />
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-blue-900 tracking-wide">SIKAP</h2>
                    <p class="text-[9px] text-gray-500 tracking-wider uppercase">Sistem Target &amp; Realisasi</p>
                </div>
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center px-6 py-10 lg:px-16">
            <div class="w-full max-w-md">

                <div class="flex flex-col items-center text-center mb-8">
                    <div class="w-24 h-24 mb-4">
                        <img src="{{ asset('assets/logo-lanri.png') }}" alt="SIKAP Logo" class="w-full h-full object-contain" />
                    </div>
                    <h2 class="text-xl font-extrabold text-blue-900 tracking-wide">SIKAP</h2>
                    <p class="text-[10px] text-gray-500 tracking-[0.25em] uppercase mt-1">Sistem Target &amp; Realisasi</p>

                    <h1 class="text-xl font-bold text-gray-800 mt-4">Masuk ke Akun Anda</h1>
                    <p class="text-xs text-gray-500 mt-1"></p>
                </div>

                <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <div class="relative">
                            <i data-lucide="mail" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="{{ old('username') }}"
                                placeholder="Masukkan username"
                                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                required autofocus />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <i data-lucide="lock" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan password"
                                class="w-full pl-10 pr-10 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                required />
                            <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                                <i id="eyeIcon" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500" />
                            <span class="text-gray-600">Ingat saya</span>
                        </label>
                        <a href="{{ route('forgot-password.form') }}" class="text-blue-700 hover:text-blue-900 hover:underline font-medium">
                            Lupa password?
                        </a>
                    </div>

                    <button type="submit"
                        class="w-full bg-gradient-to-r from-blue-800 to-blue-600 hover:from-blue-900 hover:to-blue-700 text-white font-semibold py-3 rounded-lg transition-all shadow-lg shadow-blue-200 flex items-center justify-center gap-2 mt-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        MASUK
                    </button>
                </form>

                <div class="mt-10 pt-6 border-t border-gray-100 text-center">
                    <div class="inline-flex items-center gap-2 text-xs text-gray-500">
                        <img src="{{ asset('assets/logo-lanri.png') }}" alt="Logo" class="w-6 h-6 object-contain" />
                        <div class="text-left">
                            <p class="font-bold text-gray-700 leading-tight">SIKAP</p>
                            <p class="text-[10px] text-gray-400 leading-tight">Sistem Informasi Keuangan &amp; Anggaran Pusjar SKMP</p>
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-3">© 2026 SIKAP - PUSJAR SKMP</p>
                </div>

            </div>
        </div>
    </div>

</div>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/pages/login.js') }}"></script>

@if (session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Toast.fire({ icon: 'error', title: @json(session('error')) });
    });
</script>
@endif

@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Toast.fire({ icon: 'success', title: @json(session('success')) });
    });
</script>
@endif

<script>
    if (window.lucide) lucide.createIcons();
</script>
</body>
</html>
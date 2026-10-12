<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Error') - SIKAP</title>
    <link rel="icon" href="{{ asset('assets/logo-lanri.png') }}" type="image/x-icon">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 flex items-center justify-center p-4">

    <div class="max-w-lg w-full">

        {{-- LOGO --}}
        <div class="flex items-center justify-center mb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-white shadow-md p-2">
                    <img src="{{ asset('assets/logo-lanri.png') }}" alt="SIKAP" class="w-full h-full object-contain" />
                </div>
                <div>
                    <h2 class="text-lg font-extrabold text-blue-900 tracking-wide">SIKAP</h2>
                    <p class="text-[9px] text-gray-500 tracking-widest uppercase">Sistem Informasi Keuangan</p>
                </div>
            </div>
        </div>

        {{-- CARD ERROR --}}
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            @yield('content')
        </div>

        {{-- FOOTER --}}
        <p class="text-center text-[10px] text-gray-400 mt-6">
            © 2026 SIKAP · Pusjar SKMP
        </p>
    </div>

    <script>
        if (window.lucide) lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>

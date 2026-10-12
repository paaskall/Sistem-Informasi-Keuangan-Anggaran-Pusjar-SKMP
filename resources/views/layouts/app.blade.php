<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'SIKAP')</title>
    <link rel="icon" href="{{ asset('assets/logo-lanri.png') }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: {
                        primary: '#0A669D',
                        secondary: '#0DACFF',
                        accent: '#F1C300'
                    },
                    canvas: '#F4F7FA',
                    border: '#DCE4EB',
                    muted: '#526575',
                    slate: '#172B3A',
                    'primary-dark': '#085480',
                    'primary-darker': '#063E5F',
                    'primary-light': '#0A7BB8',
                    'secondary-dark': '#0894DD',
                    'secondary-darker': '#0678B5',
                    'secondary-light': '#3DC0FF',
                    'accent-dark': '#D4AB00',
                    'accent-darker': '#B89300',
                    'accent-light': '#F5D53D',
                    'info-bg': '#E6F2F9'
                },
                fontSize: {
                    'display': ['72px', { lineHeight: '80px', fontWeight: '600' }],
                    'h1': ['60px', { lineHeight: '72px', fontWeight: '700' }],
                    'h2': ['48px', { lineHeight: '64px', fontWeight: '700' }],
                    'h3': ['40px', { lineHeight: '48px', fontWeight: '500' }],
                    'h4': ['32px', { lineHeight: '40px', fontWeight: '600' }],
                    'h5': ['24px', { lineHeight: '32px', fontWeight: '600' }],
                    'h6': ['20px', { lineHeight: '28px', fontWeight: '600' }],
                    'body-lg': ['18px', { lineHeight: '28px', fontWeight: '400' }],
                    'body': ['16px', { lineHeight: '24px', fontWeight: '400' }],
                    'label': ['14px', { lineHeight: '20px', fontWeight: '600' }],
                    'caption': ['12px', { lineHeight: '16px', fontWeight: '500' }]
                },
                borderRadius: {
                    'sm': '4px',
                    'md': '8px',
                    'lg': '16px'
                },
                boxShadow: {
                    'soft': '0 2px 2px rgba(23, 43, 58, 0.06)',
                    'medium': '0 4px 6px rgba(23, 43, 58, 0.20)',
                    'floating': '0 16px 40px -8px rgba(23, 43, 58, 0.14)'
                },
                transitionDuration: {
                    '120': '120ms',
                    '200': '200ms',
                    '280': '280ms'
                },
                transitionTimingFunction: {
                    'blueprint': 'cubic-bezier(0.4, 0, 0.2, 1)'
                }
            }
        }
    }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800">

<div class="min-h-screen bg-gray-50">

    {{-- ============ SIDEBAR DESKTOP (Fixed) ============ --}}
    <aside class="w-64 bg-white border-r border-gray-200 flex-col hidden md:flex fixed inset-y-0 left-0 z-30">
        <x-layout.sidebar :active="$active ?? null" />
    </aside>

    {{-- ============ SIDEBAR MOBILE ============ --}}
    <aside id="mobileSidebar"
           class="w-64 bg-white border-r border-gray-200 flex-col hidden fixed inset-y-0 left-0 z-40 shadow-2xl">
        <x-layout.sidebar :active="$active ?? null" />
    </aside>
    <div id="sidebarOverlay"
         class="fixed inset-0 bg-black/40 hidden md:hidden"
         style="z-index: 35;"
         onclick="closeMobileSidebar()"></div>

    {{-- ============ MAIN ============ --}}
    <div class="md:ml-64 flex flex-col min-h-screen">
        <header class="bg-white border-b border-gray-200 px-4 sm:px-6 py-4 flex items-center justify-between sticky top-0 z-20">
            <x-layout.topbar />
        </header>

        <div class="p-4 sm:p-6 flex-1">
            @yield('content')
        </div>
    </div>
</div>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/components/sidebar.js') }}"></script>
@stack('scripts')

@if (session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Toast.fire({ icon: 'success', title: @json(session('success')) });
    });
</script>
@endif

@if (session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Toast.fire({ icon: 'error', title: @json(session('error')) });
    });
</script>
@endif

<script>
    if (window.lucide) lucide.createIcons();
</script>
</body>
</html>
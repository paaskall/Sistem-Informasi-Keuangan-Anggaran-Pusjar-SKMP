@extends('errors.layout')

@section('title', 'Akses Ditolak')

@section('content')
<div class="p-8 text-center">
    {{-- ICON --}}
    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-red-50 flex items-center justify-center relative">
        <i data-lucide="shield-alert" class="w-12 h-12 text-red-600"></i>
        <div class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-red-600 flex items-center justify-center">
            <i data-lucide="lock" class="w-4 h-4 text-white"></i>
        </div>
    </div>

    <p class="text-6xl font-black text-red-600 mb-2">403</p>
    <h1 class="text-2xl font-bold text-gray-800 mb-3">Akses Ditolak</h1>
    <p class="text-sm text-gray-500 mb-6 leading-relaxed">
        @if ($exception && $exception->getMessage())
            {{ $exception->getMessage() }}
        @else
            Anda tidak memiliki izin untuk mengakses halaman ini.<br/>
            Hubungi administrator jika Anda merasa ini kesalahan.
        @endif
    </p>

    {{-- INFO ROLE --}}
    @auth
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 mb-6 inline-flex items-center gap-2 text-xs text-blue-700">
            <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
            <span>Anda login sebagai <b>{{ auth()->user()->role_label }}</b></span>
        </div>
    @endauth

    {{-- ACTIONS --}}
    <div class="flex flex-wrap gap-2 justify-center">
        <a href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isKeuangan() ? route('keuangan.dashboard') : route('pimpinan.dashboard'))) : route('login') }}"
           class="bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg shadow-md shadow-blue-200 flex items-center gap-2 transition">
            <i data-lucide="home" class="w-4 h-4"></i>
            Kembali ke Dashboard
        </a>
        <button onclick="history.back()"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2.5 rounded-lg flex items-center gap-2 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Halaman Sebelumnya
        </button>
    </div>
</div>

@push('scripts')
<script>
    // Auto-redirect ke dashboard role setelah 8 detik
    setTimeout(() => {
        const target = "{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isKeuangan() ? route('keuangan.dashboard') : route('pimpinan.dashboard'))) : route('login') }}";
        if (target && window.location.href !== target) {
            window.location.href = target;
        }
    }, 8000);
</script>
@endpush
@endsection

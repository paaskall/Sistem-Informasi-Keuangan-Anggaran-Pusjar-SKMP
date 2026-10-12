@extends('errors.layout')

@section('title', 'Kesalahan Server')

@section('content')
<div class="p-8 text-center">
    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-red-50 flex items-center justify-center relative">
        <i data-lucide="server-crash" class="w-12 h-12 text-red-600"></i>
    </div>

    <p class="text-6xl font-black text-red-600 mb-2">500</p>
    <h1 class="text-2xl font-bold text-gray-800 mb-3">Terjadi Kesalahan Server</h1>
    <p class="text-sm text-gray-500 mb-6 leading-relaxed">
        Maaf, sistem mengalami gangguan internal.<br/>
        Tim teknis sudah diberitahu. Silakan coba beberapa saat lagi.
    </p>

    {{-- ERROR ID (kalau production) --}}
    @if (!config('app.debug'))
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 mb-6 inline-block text-xs text-gray-600 font-mono">
            Error ID: {{ now()->format('YmdHis') }}-{{ Str::random(6) }}
        </div>
    @endif

    <div class="flex flex-wrap gap-2 justify-center">
        <a href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isKeuangan() ? route('keuangan.dashboard') : route('pimpinan.dashboard'))) : route('login') }}"
           class="bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg shadow-md shadow-blue-200 flex items-center gap-2 transition">
            <i data-lucide="home" class="w-4 h-4"></i>
            Kembali ke Dashboard
        </a>
        <button onclick="location.reload()"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2.5 rounded-lg flex items-center gap-2 transition">
            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            Coba Lagi
        </button>
    </div>
</div>
@endsection

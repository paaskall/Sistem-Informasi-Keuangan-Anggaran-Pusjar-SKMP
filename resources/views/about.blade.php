@extends('layouts.app')

@section('title', 'Tentang Aplikasi')

@section('content')
@php
    $info = config('app-info');
    $user = auth()->user();
    $role = $user?->role;
@endphp

<div class="max-w-4xl mx-auto space-y-6">

    {{-- HERO SECTION --}}
    <div class="bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500 rounded-2xl shadow-xl overflow-hidden relative">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full -ml-24 -mb-24"></div>

        <div class="relative p-8 sm:p-12 text-white text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-lg mb-4">
                <img src="{{ asset($info['logo']) }}" alt="Logo" class="w-14 h-14 object-contain" />
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold mb-2">{{ $info['name'] }}</h1>
            <p class="text-blue-100 text-sm sm:text-base max-w-2xl mx-auto">{{ $info['full_name'] }}</p>
            <div class="mt-4 inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-1.5 rounded-full">
                <i data-lucide="tag" class="w-4 h-4"></i>
                <span class="text-sm font-semibold">Versi {{ $info['version'] }}</span>
            </div>
        </div>
    </div>

    {{-- DESKRIPSI --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-3 flex items-center gap-2">
            <i data-lucide="info" class="w-5 h-5 text-blue-700"></i>
            Tentang SIKAP
        </h2>
        <p class="text-sm text-gray-600 leading-relaxed">{{ $info['description'] }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-6">
            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                <i data-lucide="building" class="w-5 h-5 text-blue-700 flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Institusi</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $info['institution'] }}</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                <i data-lucide="briefcase" class="w-5 h-5 text-blue-700 flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Unit</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $info['unit'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- FITUR UTAMA --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="star" class="w-5 h-5 text-amber-500"></i>
            Fitur Utama
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($info['features'] as $feature)
            <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:border-blue-200 hover:bg-blue-50/50 transition">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-600 to-blue-500 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i data-lucide="{{ $feature['icon'] }}" class="w-5 h-5 text-white"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-gray-800">{{ $feature['title'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- STATISTIK APLIKASI --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="bar-chart-3" class="w-5 h-5 text-emerald-600"></i>
            Statistik Aplikasi
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 border border-blue-100 rounded-xl p-4 text-center">
                <p class="text-[10px] text-blue-700 uppercase font-bold tracking-wider mb-1">User Aktif</p>
                <p class="text-2xl font-bold text-blue-800">{{ $stats['total_users'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 border border-emerald-100 rounded-xl p-4 text-center">
                <p class="text-[10px] text-emerald-700 uppercase font-bold tracking-wider mb-1">Total Target</p>
                <p class="text-2xl font-bold text-emerald-800">{{ $stats['total_target'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 border border-amber-100 rounded-xl p-4 text-center">
                <p class="text-[10px] text-amber-700 uppercase font-bold tracking-wider mb-1">Realisasi</p>
                <p class="text-2xl font-bold text-amber-800">{{ $stats['total_realisasi'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-red-50 to-red-100/50 border border-red-100 rounded-xl p-4 text-center">
                <p class="text-[10px] text-red-700 uppercase font-bold tracking-wider mb-1">Kendala Aktif</p>
                <p class="text-2xl font-bold text-red-800">{{ $stats['total_kendala_aktif'] }}</p>
            </div>
        </div>
    </div>

    {{-- TIM PENGEMBANG --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="users" class="w-5 h-5 text-purple-600"></i>
            Pengembang
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($info['team'] as $member)
            {{-- Bungkus dengan <a> jika mengarah ke link eksternal --}}
            <a href="https://aboutpaskal.vercel.app/" target="_blank" rel="noopener noreferrer" 
            class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition group cursor-pointer">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-600 to-pink-500 flex items-center justify-center flex-shrink-0 text-white text-lg font-bold shadow-sm">
                    {{ $member['initial'] }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-gray-800 group-hover:text-purple-700 transition">{{ $member['name'] }}</p>
                    <p class="text-xs text-gray-500 group-hover:text-purple-600 transition">{{ $member['role'] }}</p>
                </div>
                {{-- Tambahkan icon external link agar user tahu ini bisa diklik --}}
                <i data-lucide="arrow-up-right" class="w-4 h-4 text-gray-400 group-hover:text-purple-600 transition"></i>
            </a>
            @endforeach
        </div>
    </div>

    {{-- TAUTAN PENTING (SEMUA ROLE) --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="link" class="w-5 h-5 text-blue-700"></i>
            Tautan Penting
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

            {{-- Dokumentasi (SEMUA ROLE) --}}
            <a href="{{ route('documentation') }}"
               class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-blue-200 rounded-lg transition group">
                <div class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center flex-shrink-0 group-hover:border-blue-300 group-hover:bg-blue-100">
                    <i data-lucide="book-open" class="w-4 h-4 text-gray-600 group-hover:text-blue-700"></i>
                </div>
                <span class="text-sm font-semibold text-gray-800 group-hover:text-blue-700">Dokumentasi</span>
                <i data-lucide="arrow-right" class="w-3 h-3 text-gray-400 group-hover:text-blue-700 ml-auto"></i>
            </a>

            {{-- Changelog (SEMUA ROLE) --}}
            <a href="{{ route('changelog') }}"
               class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-blue-200 rounded-lg transition group">
                <div class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center flex-shrink-0 group-hover:border-blue-300 group-hover:bg-blue-100">
                    <i data-lucide="list" class="w-4 h-4 text-gray-600 group-hover:text-blue-700"></i>
                </div>
                <span class="text-sm font-semibold text-gray-800 group-hover:text-blue-700">Changelog</span>
                <i data-lucide="arrow-right" class="w-3 h-3 text-gray-400 group-hover:text-blue-700 ml-auto"></i>
            </a>

            {{-- Laporkan Bug (Keuangan & Admin Keuangan) --}}
            @if(in_array($role, ['keuangan', 'admin_keuangan']))
            <a href="{{ route('bug-report.index') }}"
               class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-amber-50 border border-gray-100 hover:border-amber-200 rounded-lg transition group">
                <div class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center flex-shrink-0 group-hover:border-amber-300 group-hover:bg-amber-100">
                    <i data-lucide="bug" class="w-4 h-4 text-gray-600 group-hover:text-amber-700"></i>
                </div>
                <span class="text-sm font-semibold text-gray-800 group-hover:text-amber-700">Laporkan Bug</span>
                <i data-lucide="arrow-right" class="w-3 h-3 text-gray-400 group-hover:text-amber-700 ml-auto"></i>
            </a>
            @endif

        </div>
    </div>

    {{-- FOOTER --}}
    <div class="text-center py-6">
        <p class="text-sm text-gray-500 font-semibold">{{ $info['copyright'] }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $info['tagline'] }}</p>
    </div>
</div>
@endsection
@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
<div class="p-8 text-center">
    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-amber-50 flex items-center justify-center relative">
        <i data-lucide="search-x" class="w-12 h-12 text-amber-600"></i>
    </div>

    <p class="text-6xl font-black text-amber-600 mb-2">404</p>
    <h1 class="text-2xl font-bold text-gray-800 mb-3">Halaman Tidak Ditemukan</h1>
    <p class="text-sm text-gray-500 mb-6 leading-relaxed">
        Halaman yang Anda cari tidak ada atau sudah dipindahkan.<br/>
        Periksa kembali URL atau kembali ke dashboard.
    </p>

    <div class="flex flex-wrap gap-2 justify-center">
        <a href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isKeuangan() ? route('keuangan.dashboard') : route('pimpinan.dashboard'))) : route('login') }}"
           class="bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold px-5 py-2.5 rounded-lg shadow-md shadow-blue-200 flex items-center gap-2 transition">
            <i data-lucide="home" class="w-4 h-4"></i>
            Kembali ke Dashboard
        </a>
        <button onclick="history.back()"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2.5 rounded-lg flex items-center gap-2 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </button>
    </div>
</div>
@endsection

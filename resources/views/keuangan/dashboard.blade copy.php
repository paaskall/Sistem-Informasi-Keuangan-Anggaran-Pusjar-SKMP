@extends('layouts.app')

@section('title', 'SIKAP - Dashboard Keuangan')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <div class="w-1 h-8 bg-gradient-to-b from-blue-700 to-blue-400 rounded-full"></div>
            <h2 class="text-2xl font-bold text-gray-800">Dashboard Keuangan</h2>
        </div>
        <p class="text-sm text-gray-500 ml-4">Ringkasan keuangan tahun <span id="label-tahun">{{ $tahun }}</span></p>
    </div>
    <div class="flex gap-2 items-center">
        <select id="filter-tahun" onchange="changeTahun(this.value)"
            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
            @foreach ($tahunOptions as $y)
                <option value="{{ $y }}" {{ $y === $tahun ? 'selected' : '' }}>
                    {{ $y }}{{ $y != $tahunBerjalan ? '' : '' }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- SUMMARY CARDS: ANGGARAN + PNBP (side by side) --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div id="summary-card">
        <div class="text-center py-10 text-gray-400">Memuat...</div>
    </div>
    <div id="pnbp-summary-card">
        <div class="text-center py-10 text-gray-400">Memuat...</div>
    </div>
</div>

{{-- CHART BULANAN --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-6 overflow-hidden">
    <div class="bg-gradient-to-r from-gray-50 to-blue-50/50 border-b border-gray-100 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-500 flex items-center justify-center shadow-md shadow-blue-200">
                <i data-lucide="bar-chart-3" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">Target dan Realisasi Anggaran Bulanan</h3>
                <p class="text-xs text-gray-500 mt-0.5">Perbandingan target & realisasi per bulan <span id="chart-tahun">{{ $tahun }}</span></p>
            </div>
        </div>
        <div class="flex items-center gap-4 text-[11px]">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-md bg-gradient-to-br from-blue-600 to-blue-400 shadow-sm"></div>
                <span class="font-semibold text-gray-600">Target</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-md bg-gradient-to-br from-emerald-600 to-emerald-400 shadow-sm"></div>
                <span class="font-semibold text-gray-600">Realisasi</span>
            </div>
        </div>
    </div>
    <div class="p-6">
        <div id="chart-container">
            <p class="text-sm text-gray-400 text-center py-8">Memuat...</p>
        </div>
    </div>
</div>

{{-- PROGRESS PENGADAAN --}}
<div id="progress-pengadaan-container" class="mb-6"></div>

{{-- RENCANA REALISASI BULAN INI --}}
<div id="rencana-bulan-ini-container" class="mb-6"></div>

{{-- HIGHLIGHT BULAN INI --}}
<div id="bulan-ini-container"></div>

{{-- AKSI CEPAT --}}
<div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
        <i data-lucide="zap" class="w-5 h-5 text-blue-700"></i> Aksi Cepat
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="{{ route('keuangan.target') }}" class="flex flex-col items-center gap-2 p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition text-blue-700 group">
            <i data-lucide="target" class="w-6 h-6 group-hover:scale-110 transition"></i>
            <span class="text-xs font-medium">Kelola Target</span>
        </a>
        <a href="{{ route('keuangan.realisasi') }}" class="flex flex-col items-center gap-2 p-4 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition text-emerald-700 group">
            <i data-lucide="trending-up" class="w-6 h-6 group-hover:scale-110 transition"></i>
            <span class="text-xs font-medium">Input Realisasi</span>
        </a>
        <a href="{{ route('keuangan.pnbp') }}" class="flex flex-col items-center gap-2 p-4 bg-teal-50 hover:bg-teal-100 rounded-lg transition text-teal-700 group">
            <i data-lucide="receipt" class="w-6 h-6 group-hover:scale-110 transition"></i>
            <span class="text-xs font-medium">PNBP</span>
        </a>
        <a href="{{ route('keuangan.kendala') }}" class="flex flex-col items-center gap-2 p-4 bg-red-50 hover:bg-red-100 rounded-lg transition text-red-700 group">
            <i data-lucide="alert-triangle" class="w-6 h-6 group-hover:scale-110 transition"></i>
            <span class="text-xs font-medium">Kendala</span>
        </a>
    </div>
</div>

<script>
window.DASHBOARD_DATA = {
    initialTahun: @json($tahun),
    tahunBerjalan: @json($tahunBerjalan),
    role: 'keuangan',
    routes: {
        data: @json(route('keuangan.dashboard.data')),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/dashboard.js') }}?v={{ time() }}"></script>
@endpush

@extends('layouts.app')

@section('title', 'SIKAP - Dashboard Keuangan')

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <x-ui.page-title
        title="Dashboard Keuangan"
        subtitle="Ringkasan keuangan tahun {{ $tahun }}"
    />
    <div class="flex gap-2 items-center">
        <select id="filter-tahun" onchange="changeTahun(this.value)"
            class="h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white">
            @foreach ($tahunOptions as $y)
                <option value="{{ $y }}" {{ $y === $tahun ? 'selected' : '' }}>
                    {{ $y }}{{ $y != $tahunBerjalan ? '' : '' }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- BANNER ARSIP --}}
<div id="arsip-banner" class="hidden mb-4">
    <x-ui.alert variant="warning" title="Read Only" dismissible>
    </x-ui.alert>
</div>

{{-- SUMMARY CARDS: ANGGARAN + PNBP --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div id="summary-card">
        <div class="text-center py-10 text-muted text-label">Memuat...</div>
    </div>
    <div id="pnbp-summary-card">
        <div class="text-center py-10 text-muted text-label">Memuat...</div>
    </div>
</div>

{{-- MATRIKS MANAJEMEN RISIKO (CATATAN KENDALA) --}}
<div id="catatan-kendala-container" class="mb-6"></div>

{{-- CHART BULANAN --}}
<x-ui.card padding="p-0" class="mb-6">
    <div class="bg-gradient-to-r from-gray-50 to-blue-50/50 border-b border-gray-100 px-6 py-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center shadow-soft">
                <i data-lucide="bar-chart-3" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <h3 class="text-h6 font-semibold text-slate">Target dan Realisasi Anggaran Bulanan</h3>
                <p class="text-caption text-muted mt-0.5">Perbandingan target & realisasi per bulan <span id="chart-tahun">{{ $tahun }}</span></p>
            </div>
        </div>
        <div class="flex items-center gap-4 text-caption">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-sm bg-gradient-to-br from-brand-primary to-brand-secondary shadow-sm"></div>
                <span class="font-semibold text-gray-600">Target</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-sm bg-gradient-to-br from-green-600 to-green-400 shadow-sm"></div>
                <span class="font-semibold text-gray-600">Realisasi</span>
            </div>
        </div>
    </div>
    <div class="p-6">
        <div id="chart-container">
            <p class="text-sm text-muted text-center py-8">Memuat...</p>
        </div>
    </div>
</x-ui.card>

{{-- PROGRESS PENGADAAN --}}
<div id="progress-pengadaan-container" class="mb-6"></div>

{{-- RENCANA PENGADAAN --}}
<div id="rencana-pengadaan-container" class="mb-6"></div>

{{-- KENDALA DALAM PENGADAAN --}}
<div id="kendala-pengadaan-container" class="mb-6"></div>

{{-- RENCANA REALISASI BULAN INI --}}
<div id="rencana-bulan-ini-container" class="mb-6"></div>

{{-- HIGHLIGHT BULAN INI --}}
<div id="bulan-ini-container"></div>

{{-- AKSELERASI PENYERAPAN ANGGARAN --}}
<div id="akselerasi-container" class="mb-6"></div>

{{-- AKSI CEPAT --}}
<x-ui.card>
    <x-ui.section-title title="Aksi Cepat" icon="zap" size="sm" class="mb-4" />

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="{{ route('keuangan.target') }}" class="flex flex-col items-center gap-2 p-4 bg-blue-50 hover:bg-blue-100 rounded-md transition-blueprint text-brand-primary group">
            <i data-lucide="target" class="w-6 h-6 group-hover:scale-110 transition"></i>
            <span class="text-caption font-semibold">Kelola Target</span>
        </a>
        <a href="{{ route('keuangan.realisasi') }}" class="flex flex-col items-center gap-2 p-4 bg-blue-50 hover:bg-blue-100 rounded-md transition-blueprint text-brand-primary group">
            <i data-lucide="trending-up" class="w-6 h-6 group-hover:scale-110 transition"></i>
            <span class="text-caption font-semibold">Input Realisasi</span>
        </a>
        <a href="{{ route('keuangan.pnbp') }}" class="flex flex-col items-center gap-2 p-4 bg-blue-50 hover:bg-blue-100 rounded-md transition-blueprint text-brand-primary group">
            <i data-lucide="receipt" class="w-6 h-6 group-hover:scale-110 transition"></i>
            <span class="text-caption font-semibold">PNBP</span>
        </a>
        <a href="{{ route('keuangan.kendala') }}" class="flex flex-col items-center gap-2 p-4 bg-blue-50 hover:bg-blue-100 rounded-md transition-blueprint text-brand-primary group">
            <i data-lucide="alert-triangle" class="w-6 h-6 group-hover:scale-110 transition"></i>
            <span class="text-caption font-semibold">Kendala</span>
        </a>
    </div>
</x-ui.card>

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
<script src="{{ asset('js/pages/dashboard.js') }}?v={{ filemtime(public_path('js/pages/dashboard.js')) }}"></script>
@endpush
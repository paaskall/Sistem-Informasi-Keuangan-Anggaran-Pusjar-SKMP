@extends('layouts.app')

@section('title', 'SIKAP - Dashboard Viewer')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <x-ui.page-title
        title="Dashboard Keuangan"
        subtitle="Ringkasan eksekutif tahun {{ $tahun }}"
        :breadcrumb="[
            ['label' => 'Dashboard Viewer'],
        ]"
    />
    <div class="flex gap-2 items-center">
        <x-ui.badge variant="warning" icon="lock">Read Only</x-ui.badge>
        <select id="filter-tahun" onchange="changeTahun(this.value)"
            class="h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white transition-blueprint">
            @foreach ($tahunOptions as $y)
                <option value="{{ $y }}" {{ $y === $tahun ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </div>
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
            <p class="text-label text-muted text-center py-8">Memuat...</p>
        </div>
    </div>
</x-ui.card>

{{-- PROGRESS PENGADAAN --}}
<div id="progress-pengadaan-container" class="mb-6"></div>

<script>
<script>
window.DASHBOARD_DATA = {
    initialTahun: @json($tahun),
    tahunBerjalan: @json($tahunBerjalan),
    role: 'pimpinan',
    routes: {
        data: @json(route('pimpinan.dashboard.data')),
        exportPdf: @json(route('pimpinan.laporan.export.pdf')),
        exportCsv: @json(route('pimpinan.laporan.export.csv')),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/dashboard.js') }}?v={{ filemtime(public_path('js/pages/dashboard.js')) }}"></script>
@endpush
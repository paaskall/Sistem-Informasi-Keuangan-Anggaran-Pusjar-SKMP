@extends('layouts.app')

@section('title', 'SIKAP - Laporan Pimpinan')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <x-ui.page-title
        title="Laporan"
        subtitle="Rekap bulanan target & realisasi"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('pimpinan.dashboard')],
            ['label' => 'Laporan'],
        ]"
    />
    <div class="flex gap-2">
        <x-ui.button variant="danger" icon="file-text" onclick="doExport('PDF')">PDF</x-ui.button>
        <x-ui.button variant="success" icon="table" onclick="doExport('CSV')">CSV</x-ui.button>
    </div>
</div>

{{-- STAT CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5" id="stat-cards">
    <div class="text-center py-6 text-muted text-label col-span-3">Memuat...</div>
</div>

{{-- FILTER --}}
<x-ui.card padding="p-4" class="mb-5">
    <div class="flex flex-wrap gap-3 items-end">
        <div class="min-w-[200px]">
            <x-ui.label for="filter-tahun" uppercase>Tahun</x-ui.label>
            <select id="filter-tahun" onchange="changeTahun(this.value)"
                class="w-full h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white transition-blueprint">
                @foreach ($tahunOptions as $y)
                    <option value="{{ $y }}" {{ $y === $tahun ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <x-ui.caption id="filter-info" variant="muted" class="ml-auto self-center">Memuat...</x-ui.caption>
    </div>
</x-ui.card>

{{-- TABEL LAPORAN --}}
<x-ui.card padding="p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-16">#</th>
                    <th class="text-left px-5 py-4 text-caption font-semibold uppercase">Bulan</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-36">Program</th>
                    <th class="text-right px-5 py-4 text-caption font-semibold uppercase">Target</th>
                    <th class="text-right px-5 py-4 text-caption font-semibold uppercase">Realisasi</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-32">Capaian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="laporan-tbody">
                <tr><td colspan="6" class="text-center py-10 text-muted">Memuat...</td></tr>
            </tbody>
            <tfoot id="laporan-tfoot"></tfoot>
        </table>
    </div>
</x-ui.card>

<script>
window.LAPORAN_DATA = {
    initialTahun: @json($tahun),
    routes: {
        data: @json(route('pimpinan.laporan.data')),
        exportPdf: @json(route('pimpinan.laporan.export.pdf')),
        exportCsv: @json(route('pimpinan.laporan.export.csv')),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/laporan.js') }}?v={{ filemtime(public_path('js/pages/laporan.js')) }}"></script>
@endpush
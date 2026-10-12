@extends('layouts.app')

@section('title', 'SIKAP - Laporan')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <div class="w-1 h-8 bg-gradient-to-b from-blue-700 to-blue-400 rounded-full"></div>
            <h2 class="text-2xl font-bold text-gray-800">Laporan</h2>
        </div>
        <p class="text-sm text-gray-500 ml-4">Rekap bulanan target & realisasi</p>
    </div>
    <div class="flex gap-2">
        <button type="button" onclick="doExport('PDF')"
            class="bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium px-4 py-2.5 rounded-lg flex items-center gap-2 transition">
            <i data-lucide="file-text" class="w-4 h-4"></i> PDF
        </button>
        <button type="button" onclick="doExport('CSV')"
            class="bg-green-50 hover:bg-green-100 text-green-700 text-sm font-medium px-4 py-2.5 rounded-lg flex items-center gap-2 transition">
            <i data-lucide="table" class="w-4 h-4"></i> CSV
        </button>
    </div>
</div>

{{-- STAT CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5" id="stat-cards">
    <div class="text-center py-6 text-gray-400 col-span-3">Memuat...</div>
</div>

{{-- FILTER --}}
<div class="bg-white rounded-xl border border-gray-100 p-4 mb-5 shadow-sm flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1.5">Tahun</label>
        <select id="filter-tahun" onchange="changeTahun(this.value)"
            class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
            @foreach ($tahunOptions as $y)
                <option value="{{ $y }}" {{ $y === $tahun ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </div>
    <div class="text-xs text-gray-500 ml-auto self-center" id="filter-info">Memuat...</div>
</div>

{{-- TABEL LAPORAN --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-center px-5 py-4 font-semibold w-16">#</th>
                    <th class="text-left px-5 py-4 font-semibold">Bulan</th>
                    <th class="text-center px-5 py-4 font-semibold w-36">Program</th>
                    <th class="text-right px-5 py-4 font-semibold">Target</th>
                    <th class="text-right px-5 py-4 font-semibold">Realisasi</th>
                    <th class="text-center px-5 py-4 font-semibold w-32">Capaian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="laporan-tbody">
                <tr><td colspan="6" class="text-center py-10 text-gray-400">Memuat...</td></tr>
            </tbody>
            <tfoot id="laporan-tfoot"></tfoot>
        </table>
    </div>
</div>

<script>
window.LAPORAN_DATA = {
    initialTahun: @json($tahun),
    routes: {
        data: @json(route('keuangan.laporan.data')),
        exportPdf: @json(route('keuangan.laporan.export.pdf')),
        exportCsv: @json(route('keuangan.laporan.export.csv')),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/laporan.js') }}"></script>
@endpush
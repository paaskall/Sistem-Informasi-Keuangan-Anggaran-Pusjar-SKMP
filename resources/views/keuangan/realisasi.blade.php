@extends('layouts.app')

@section('title', 'SIKAP - Realisasi')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3">
    <x-ui.page-title 
        title="Realisasi Keuangan" 
        subtitle="Input realisasi per target bulanan"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('keuangan.dashboard')],
            ['label' => 'Realisasi'],
        ]" 
    />
    <x-ui.button variant="primary" icon="plus" onclick="openRealisasiForm()" id="btn-add-realisasi">
        Input Realisasi
    </x-ui.button>
</div>

{{-- BANNER ARSIP --}}
<div id="arsip-banner" class="hidden mb-4">
    <x-ui.alert variant="warning" title="Mode Arsip - Read Only" dismissible>
        Data tahun <span id="arsip-tahun" class="font-bold">-</span> sudah dikunci.
    </x-ui.alert>
</div>

{{-- STAT CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6" id="stat-cards">
    <div class="text-center py-6 text-muted text-label col-span-3">Memuat...</div>
</div>

{{-- FILTER TAHUN --}}
<x-ui.card padding="p-4" class="mb-5">
    <div class="flex flex-wrap gap-3 items-end">
        <div class="min-w-[200px]">
            <x-ui.label for="filter-tahun" uppercase>Tahun Anggaran</x-ui.label>
            <select id="filter-tahun" onchange="changeTahun(this.value)"
                class="w-full h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white">
                @foreach ($tahunOptions as $y)
                    <option value="{{ $y }}" {{ $y === $tahun ? 'selected' : '' }}>
                        {{ $y }}{{ $y != $tahunBerjalan ? ' 🔒' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <x-ui.caption id="filter-info" variant="muted" class="ml-auto self-center">Memuat...</x-ui.caption>
    </div>
</x-ui.card>

{{-- TABEL REALISASI --}}
<x-ui.card padding="p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-16">#</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase">Bulan</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-32">Tanggal</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-40">Detail</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-56">Realisasi</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="realisasi-tbody">
                <tr><td colspan="6" class="text-center py-10 text-muted">Memuat...</td></tr>
            </tbody>
        </table>
    </div>
</x-ui.card>

<script>
window.REALISASI_DATA = {
    initialTahun: @json($tahun),
    tahunBerjalan: @json($tahunBerjalan),
    routes: {
        data: @json(route('keuangan.realisasi.data')),
        store: @json(route('keuangan.realisasi.store')),
        update: @json(route('keuangan.realisasi.update', ['id' => '__ID__'])),
        destroy: @json(route('keuangan.realisasi.destroy', ['id' => '__ID__'])),
        targetDetails: @json(route('keuangan.realisasi.target-details', ['targetId' => '__TID__'])),
    },
    references: {
        kategoris: @json(\App\Models\KategoriKendala::orderBy('nama')->get(['id', 'nama'])),
        komponens: @json(\App\Models\KomponenBelanja::orderBy('kode')->get(['id', 'kode', 'nama'])),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/realisasi.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('js/pages/realisasi.js') }}?v={{ time() }}"></script>
@endpush

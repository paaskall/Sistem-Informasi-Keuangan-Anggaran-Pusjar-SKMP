@extends('layouts.app')

@section('title', 'SIKAP - Kelola Pengadaan')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3">
    <x-ui.page-title 
        title="Kelola Pengadaan" 
        subtitle="Kelola paket pengadaan & progress tahapan"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('keuangan.dashboard')],
            ['label' => 'Pengadaan'],
        ]" 
    />
    <x-ui.button variant="primary" icon="plus" onclick="openPengadaanForm()" id="btn-add-pengadaan">
        Tambah Pengadaan
    </x-ui.button>
</div>

{{-- STAT CARDS --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-5 mb-6" id="stat-cards">
    <div class="text-center py-6 text-muted text-label col-span-4">Memuat...</div>
</div>

{{-- FILTER --}}
<x-ui.card padding="p-4" class="mb-5">
    <div class="flex flex-wrap gap-3 items-end">
        <div class="min-w-[180px]">
            <x-ui.label for="filter-tahun" uppercase>Tahun</x-ui.label>
            <select id="filter-tahun" onchange="loadData()"
                class="w-full h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white">
                <option value="">Semua</option>
                <option value="2026" selected>2026</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
            </select>
        </div>
    </div>
</x-ui.card>

{{-- TABLE --}}
<x-ui.card padding="p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="text-center px-4 py-3 text-caption font-semibold uppercase w-12">No</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Jenis Pengadaan / Kontrak</th>
                    <th class="text-right px-4 py-3 text-caption font-semibold uppercase w-44">Anggaran</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-52">Waktu Pelaksanaan</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-40">Progress</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-40">Keterangan</th>
                    <th class="text-right px-4 py-3 text-caption font-semibold uppercase w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="pengadaan-tbody">
                <tr><td colspan="7" class="text-center py-10 text-muted">Memuat...</td></tr>
            </tbody>
        </table>
    </div>
</x-ui.card>

<script>
window.PENGADAAN_DATA = {
    routes: {
        data: @json(route('keuangan.pengadaan.data')),
        store: @json(route('keuangan.pengadaan.store')),
        update: @json(route('keuangan.pengadaan.update', ['id' => '__ID__'])),
        destroy: @json(route('keuangan.pengadaan.destroy', ['id' => '__ID__'])),
        detail: @json(route('keuangan.pengadaan.detail', ['id' => '__ID__'])),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/pengadaan.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('js/pages/pengadaan.js') }}?v={{ time() }}"></script>
@endpush

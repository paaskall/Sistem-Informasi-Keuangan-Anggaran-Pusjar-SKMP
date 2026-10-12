@extends('layouts.app')

@section('title', 'SIKAP - Pengadaan')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <x-ui.page-title
        title="Pengadaan"
        subtitle="Pantau progress paket pengadaan"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('pimpinan.dashboard')],
            ['label' => 'Pengadaan'],
        ]"
    />
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
                class="w-full h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white transition-blueprint">
                <option value="">Semua</option>
                <option value="2026" selected>2026</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
            </select>
        </div>
        <x-ui.caption id="filter-info" variant="muted" class="ml-auto self-center">Memuat...</x-ui.caption>
    </div>
</x-ui.card>

{{-- TABLE --}}
<x-ui.card padding="p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-32">Kode</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Nama Paket</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-24">Jenis</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-40">Progress</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-28">Status</th>
                    <th class="text-right px-4 py-3 text-caption font-semibold uppercase w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="pengadaan-tbody">
                <tr><td colspan="6" class="text-center py-10 text-muted">Memuat...</td></tr>
            </tbody>
        </table>
    </div>
</x-ui.card>

<script>
window.PENGADAAN_DATA = {
    routes: {
        data: @json(route('pimpinan.pengadaan.data')),
        detail: @json(route('pimpinan.pengadaan.detail', ['id' => '__ID__'])),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/pengadaan.css') }}?v={{ filemtime(public_path('css/pages/pengadaan.css')) }}">
@endpush

@push('scripts')
<script src="{{ asset('js/pages/pengadaan-viewer.js') }}?v={{ filemtime(public_path('js/pages/pengadaan-viewer.js')) }}"></script>
@endpush
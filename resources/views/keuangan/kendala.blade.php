@extends('layouts.app')

@section('title', 'SIKAP - Kendala Realisasi')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3">
    <x-ui.page-title 
        title="Kendala Realisasi" 
        subtitle="Catat kendala & tindak lanjut"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('keuangan.dashboard')],
            ['label' => 'Kendala'],
        ]" 
    />
    <x-ui.button variant="primary" icon="plus" onclick="openKendalaForm()" id="btn-add-kendala">
        Tambah Kendala
    </x-ui.button>
</div>

{{-- STAT CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6" id="stat-cards">
    <div class="text-center py-6 text-muted text-label col-span-3">Memuat...</div>
</div>

{{-- FILTER --}}
<x-ui.card padding="p-4" class="mb-5">
    <div class="flex flex-wrap gap-3 items-end">
        <div class="min-w-[180px]">
            <x-ui.label for="filter-kategori" uppercase>Kategori</x-ui.label>
            <select id="filter-kategori" onchange="loadData()"
                class="w-full h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white">
                <option value="semua">Semua</option>
            </select>
        </div>
        <div class="min-w-[150px]">
            <x-ui.label for="filter-status" uppercase>Status</x-ui.label>
            <select id="filter-status" onchange="loadData()"
                class="w-full h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white">
                <option value="semua">Semua</option>
                <option value="Proses">Proses</option>
                <option value="Selesai">Selesai</option>
            </select>
        </div>
        <x-ui.caption id="filter-info" variant="muted" class="ml-auto self-center">Memuat...</x-ui.caption>
    </div>
</x-ui.card>

{{-- TABEL KENDALA --}}
<x-ui.card padding="p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-32">Tanggal</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-32">Kategori</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-48">Terkait</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Deskripsi</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-24">Status</th>
                    <th class="text-right px-4 py-3 text-caption font-semibold uppercase w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="kendala-tbody">
                <tr><td colspan="6" class="text-center py-8 text-muted">Memuat...</td></tr>
            </tbody>
        </table>
    </div>
</x-ui.card>

<script>
window.KENDALA_DATA = {
    routes: {
        data: @json(route('keuangan.kendala.data')),
        store: @json(route('keuangan.kendala.store')),
        update: @json(route('keuangan.kendala.update', ['id' => '__ID__'])),
        destroy: @json(route('keuangan.kendala.destroy', ['id' => '__ID__'])),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/kendala.js') }}?v={{ time() }}"></script>
@endpush

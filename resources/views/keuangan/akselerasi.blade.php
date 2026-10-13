@extends('layouts.app')

@section('title', 'SIKAP — Akselerasi Penyerapan Anggaran')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <x-ui.page-title
        title="Akselerasi Penyerapan Anggaran"
        subtitle="Kelola kegiatan percepatan pencapaian target anggaran"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('keuangan.dashboard')],
            ['label' => 'Akselerasi Penyerapan'],
        ]"
    />
    <x-ui.button variant="primary" icon="plus" onclick="openAkselerasiForm()">
        Tambah Kegiatan
    </x-ui.button>
</div>

<x-ui.card padding="p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="text-center px-4 py-3 text-caption font-semibold uppercase w-12">No</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Kegiatan</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-44">Jangka Waktu</th>
                    <th class="text-center px-4 py-3 text-caption font-semibold uppercase w-32">Progress</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase w-60">Keterangan</th>
                    <th class="text-right px-4 py-3 text-caption font-semibold uppercase w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="akselerasi-tbody">
                <tr><td colspan="6" class="text-center py-10 text-muted">Memuat...</td></tr>
            </tbody>
        </table>
    </div>
</x-ui.card>

<script>
window.AKSELERASI_DATA = {
    routes: {
        data: @json(route('keuangan.akselerasi.data')),
        store: @json(route('keuangan.akselerasi.store')),
        update: @json(route('keuangan.akselerasi.update', ['id' => '__ID__'])),
        destroy: @json(route('keuangan.akselerasi.destroy', ['id' => '__ID__'])),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/akselerasi.js') }}?v={{ filemtime(public_path('js/pages/akselerasi.js')) }}"></script>
@endpush
@extends('layouts.app')

@section('title', 'SIKAP - Realisasi Penerimaan PNBP')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3">
    <x-ui.page-title 
        title="Realisasi Penerimaan PNBP" 
        subtitle="Penerimaan Negara Bukan Pajak"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('keuangan.dashboard')],
            ['label' => 'PNBP'],
        ]" 
    />
    <div class="flex gap-2 items-center">
        <select id="filter-tahun" onchange="changeTahun(this.value)"
            class="h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white">
            @foreach ($availableYears as $y)
                <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
            @if (!in_array(date('Y'), $availableYears))
                <option value="{{ date('Y') }}">{{ date('Y') }}</option>
            @endif
        </select>
        <x-ui.button variant="primary" icon="plus" onclick="openPnbpForm()" id="btn-add-pnbp">
            Tambah Tahun PNBP
        </x-ui.button>
    </div>
</div>

{{-- SUMMARY CARD --}}
<div id="pnbp-summary" class="mb-6">
    <div class="text-center py-10 text-muted text-label">Memuat...</div>
</div>

{{-- TABEL PNBP --}}
<x-ui.card padding="p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-16">#</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase">Bulan</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-44">Sumber</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-56">Target</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="pnbp-tbody">
                <tr><td colspan="5" class="text-center py-10 text-muted">Memuat...</td></tr>
            </tbody>
            <tfoot id="pnbp-tfoot"></tfoot>
        </table>
    </div>
</x-ui.card>

{{-- EMPTY STATE --}}
<div id="pnbp-empty" class="hidden">
    <x-ui.card padding="p-10">
        <div class="text-center">
            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-blue-50 flex items-center justify-center">
                <i data-lucide="receipt" class="w-10 h-10 text-brand-primary"></i>
            </div>
            <h3 class="text-h6 font-semibold text-slate mb-2">Belum Ada Data PNBP</h3>
            <p class="text-caption text-muted mb-4">Belum ada data PNBP untuk tahun <span id="empty-tahun" class="font-bold">-</span>.</p>
            <x-ui.button variant="primary" icon="plus" onclick="openPnbpForm()">
                Buat Data PNBP
            </x-ui.button>
        </div>
    </x-ui.card>
</div>

<script>
window.PNBP_DATA = {
    initialTahun: @json($tahun),
    routes: {
        data: @json(route('keuangan.pnbp.data')),
        store: @json(route('keuangan.pnbp.store')),
        updateDetail: @json(route('keuangan.pnbp.detail.update', ['detailId' => '__DID__'])),
        destroyDetail: @json(route('keuangan.pnbp.detail.destroy', ['detailId' => '__DID__'])),
        destroy: @json(route('keuangan.pnbp.destroy', ['id' => '__ID__'])),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/target.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('js/pages/pnbp.js') }}?v={{ time() }}"></script>
@endpush

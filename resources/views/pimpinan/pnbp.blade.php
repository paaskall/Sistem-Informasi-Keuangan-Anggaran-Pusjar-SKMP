@extends('layouts.app')

@section('title', 'SIKAP - Realisasi Penerimaan PNBP')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <x-ui.page-title
        title="Realisasi Penerimaan PNBP"
        subtitle="Penerimaan Negara Bukan Pajak"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('pimpinan.dashboard')],
            ['label' => 'PNBP'],
        ]"
    />
    <div class="flex gap-2 items-center">
        <select id="filter-tahun" onchange="changeTahun(this.value)"
            class="h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white transition-blueprint">
            @foreach ($availableYears as $y)
                <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <x-ui.badge variant="default" icon="lock">Read Only</x-ui.badge>
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
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-24">Detail</th>
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
            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-green-50 flex items-center justify-center">
                <i data-lucide="receipt" class="w-10 h-10 text-green-700"></i>
            </div>
            <h3 class="text-h6 font-semibold text-slate mb-2">Belum Ada Data PNBP</h3>
            <p class="text-caption text-muted">Belum ada data PNBP untuk tahun <span id="empty-tahun" class="font-bold">-</span>.</p>
        </div>
    </x-ui.card>
</div>

<script>
window.PNBP_DATA = {
    initialTahun: @json($tahun),
    isViewer: true,
    routes: {
        data: @json(route('pimpinan.pnbp.data')),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/target.css') }}?v={{ filemtime(public_path('css/pages/target.css')) }}">
@endpush

@push('scripts')
<script src="{{ asset('js/pages/pnbp-viewer.js') }}?v={{ filemtime(public_path('js/pages/pnbp-viewer.js')) }}"></script>
@endpush
@extends('layouts.app')

@section('title', 'SIKAP - Target (Viewer)')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <x-ui.page-title
        title="Kelola Target"
        subtitle="Lihat target bulanan"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('viewer.dashboard')],
            ['label' => 'Target'],
        ]"
    />
    <x-ui.badge variant="warning" icon="lock">Read Only</x-ui.badge>
</div>

{{-- STAT CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6" id="stat-cards">
    <div class="text-center py-6 text-muted text-label col-span-3">Memuat...</div>
</div>

{{-- FILTER --}}
<x-ui.card padding="p-4" class="mb-5">
    <div class="flex flex-wrap gap-3 items-end">
        <div class="min-w-[200px]">
            <x-ui.label for="filter-tahun" uppercase>Tahun Anggaran</x-ui.label>
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

{{-- TABEL TARGET --}}
<x-ui.card padding="p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-16">#</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase">Bulan</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-44">Komponen</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-56">Target</th>
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-24">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="target-tbody">
                <tr><td colspan="5" class="text-center py-10 text-muted">Memuat...</td></tr>
            </tbody>
            <tfoot id="target-tfoot"></tfoot>
        </table>
    </div>
</x-ui.card>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/target.css') }}?v={{ filemtime(public_path('css/pages/target.css')) }}">
@endpush

@push('scripts')
<script>
window.TARGET_DATA = {
    initialTahun: @json($tahun),
    tahunBerjalan: @json($tahunBerjalan),
    isViewer: true,
    routes: {
        data: @json(route('viewer.target.data')),
    },
    csrf: @json(csrf_token()),
};
</script>
<script src="{{ asset('js/pages/target-viewer.js') }}?v={{ filemtime(public_path('js/pages/target-viewer.js')) }}"></script>
@endpush
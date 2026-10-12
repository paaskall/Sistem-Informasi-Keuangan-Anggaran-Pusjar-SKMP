@extends('layouts.app')

@section('title', 'SIKAP - Kelola Target')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3">
    <x-ui.page-title 
        title="Kelola Target" 
        subtitle="Target bulanan disusun dari komponen-komponen di dalamnya"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('keuangan.dashboard')],
            ['label' => 'Target'],
        ]" 
    />
    <x-ui.button variant="primary" icon="plus" onclick="openTargetForm()" id="btn-add-target">
        Tambah Target Bulanan
    </x-ui.button>
</div>

{{-- BANNER ARSIP --}}
<div id="arsip-banner" class="hidden mb-4">
    <x-ui.alert variant="warning" title="Mode Arsip - Read Only" dismissible>
        Data tahun <span id="arsip-tahun" class="font-bold">-</span>
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
                        {{ $y }}{{ $y != $tahunBerjalan ? '' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <x-ui.caption id="filter-info" variant="muted" class="ml-auto self-center hidden">Memuat...</x-ui.caption>
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
                    <th class="text-center px-5 py-4 text-caption font-semibold uppercase w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="target-tbody">
                <tr><td colspan="5" class="text-center py-10 text-muted">Memuat...</td></tr>
            </tbody>
            <tfoot id="target-tfoot"></tfoot>
        </table>
    </div>
</x-ui.card>

<script>
window.TARGET_DATA = {
    initialTahun: @json($tahun),
    tahunBerjalan: @json($tahunBerjalan),
    routes: {
        data: @json(route('keuangan.target.data')),
        store: @json(route('keuangan.target.store')),
        update: @json(route('keuangan.target.update', ['id' => '__ID__'])),
        destroy: @json(route('keuangan.target.destroy', ['id' => '__ID__'])),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/target.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('js/pages/target.js') }}?v={{ time() }}"></script>
@endpush

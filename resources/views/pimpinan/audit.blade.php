@extends('layouts.app')

@section('title', 'SIKAP - Audit Trail')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <x-ui.page-title
        title="Audit Trail"
        subtitle="Log aktivitas sistem (read-only)"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('pimpinan.dashboard')],
            ['label' => 'Audit Trail'],
        ]"
    />
</div>

{{-- FILTER --}}
<x-ui.card padding="p-4" class="mb-4">
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div>
            <x-ui.label for="f-tanggal" uppercase>Tanggal</x-ui.label>
            <input type="date" id="f-tanggal"
                class="w-full h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white transition-blueprint" />
        </div>
        <div>
            <x-ui.label for="f-user" uppercase>User</x-ui.label>
            <select id="f-user"
                class="w-full h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white transition-blueprint">
                <option value="semua">Semua</option>
            </select>
        </div>
        <div>
            <x-ui.label for="f-module" uppercase>Modul</x-ui.label>
            <select id="f-module"
                class="w-full h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white transition-blueprint">
                <option value="semua">Semua</option>
            </select>
        </div>
        <div>
            <x-ui.label for="f-action" uppercase>Aktivitas</x-ui.label>
            <select id="f-action"
                class="w-full h-11 px-3 rounded-md text-body border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100 bg-white transition-blueprint">
                <option value="semua">Semua</option>
                <option value="CREATE">CREATE</option>
                <option value="UPDATE">UPDATE</option>
                <option value="DELETE">DELETE</option>
                <option value="RESTORE">RESTORE</option>
            </select>
        </div>
    </div>
</x-ui.card>

{{-- ACTION BUTTONS --}}
<div class="flex flex-wrap gap-2 mb-4 items-center">
    <x-ui.button variant="primary" icon="filter" onclick="applyFilters()">Tampilkan</x-ui.button>
    <x-ui.button variant="ghost" icon="rotate-ccw" onclick="resetFilters()">Reset</x-ui.button>

    <div class="flex items-center gap-2 ml-2">
        <x-ui.caption variant="muted">Per halaman:</x-ui.caption>
        <select id="f-per-page" onchange="changePerPage(this.value)"
            class="h-9 px-2 rounded-md text-caption border border-gray-200 bg-white transition-blueprint">
            <option value="10">10</option>
            <option value="20" selected>20</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <x-ui.caption id="log-count" variant="muted" class="ml-auto self-center">0 log</x-ui.caption>
</div>

{{-- TABLE --}}
<x-ui.card padding="p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Tanggal</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">User</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Modul</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Aktivitas</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Record</th>
                    <th class="text-right px-4 py-3 text-caption font-semibold uppercase w-16">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="audit-tbody">
                <tr><td colspan="6" class="text-center py-8 text-muted">Memuat...</td></tr>
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div id="pagination-container" class="px-4 py-3 border-t border-gray-100 bg-gray-50/50"></div>
</x-ui.card>

<script>
window.AUDIT_DATA = {
    routes: {
        data: @json(route('pimpinan.audit.data')),
        show: @json(route('pimpinan.audit.show', ['id' => '__ID__'])),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/audit.js') }}?v={{ filemtime(public_path('js/pages/audit.js')) }}"></script>
@endpush
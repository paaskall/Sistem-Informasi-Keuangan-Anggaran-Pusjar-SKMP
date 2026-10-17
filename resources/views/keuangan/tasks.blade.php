@extends('layouts.app')

@section('title', 'SIKAP - Task Bulanan')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <x-ui.page-title
        title="Task Bulanan"
        subtitle="Kelola agenda & reminder task bulanan"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('keuangan.dashboard')],
            ['label' => 'Task Bulanan'],
        ]"
    />
    <x-ui.button variant="primary" icon="plus" onclick="openTaskForm()">
        Tambah Task
    </x-ui.button>
</div>

{{-- STATS CARDS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="circle-dashed" class="w-5 h-5 text-blue-700"></i>
            </div>
            <div>
                <p class="text-[10px] text-blue-700 uppercase font-bold tracking-wider">Belum</p>
                <p class="text-xl font-bold text-blue-800" id="stat-belum">0</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="loader" class="w-5 h-5 text-amber-700"></i>
            </div>
            <div>
                <p class="text-[10px] text-amber-700 uppercase font-bold tracking-wider">Proses</p>
                <p class="text-xl font-bold text-amber-800" id="stat-proses">0</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-green-700"></i>
            </div>
            <div>
                <p class="text-[10px] text-green-700 uppercase font-bold tracking-wider">Selesai</p>
                <p class="text-xl font-bold text-green-800" id="stat-selesai">0</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="x-circle" class="w-5 h-5 text-red-700"></i>
            </div>
            <div>
                <p class="text-[10px] text-red-700 uppercase font-bold tracking-wider">Dibatalkan</p>
                <p class="text-xl font-bold text-red-800" id="stat-dibatalkan">0</p>
            </div>
        </div>
    </div>
</div>

{{-- BULAN NAVIGATOR --}}
<x-ui.card padding="p-4" class="mb-6">
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-2">
            <button type="button" onclick="prevMonth()"
                class="w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </button>
            <div class="px-4 min-w-[200px] text-center">
                <p class="text-base font-bold text-gray-800" id="current-month">Oktober 2026</p>
                <p class="text-[10px] text-gray-500">Bulan Aktif</p>
            </div>
            <button type="button" onclick="nextMonth()"
                class="w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="flex gap-2">
            <button type="button" onclick="goToToday()"
                class="text-xs font-semibold bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg transition">
                Bulan Ini
            </button>
            <select id="filter-status" onchange="filterByStatus(this.value)"
                class="text-xs px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="belum">Belum</option>
                <option value="proses">Proses</option>
                <option value="selesai">Selesai</option>
                <option value="dibatalkan">Dibatalkan</option>
            </select>
        </div>
    </div>
</x-ui.card>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- KALENDER --}}
    <div class="lg:col-span-2">
        <x-ui.card padding="p-0">
            <div class="p-4 border-b border-gray-100 flex items-center gap-2">
                <i data-lucide="calendar-days" class="w-5 h-5 text-brand-primary"></i>
                <h3 class="font-bold text-gray-800">Kalender Task</h3>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <div class="text-center text-[10px] font-bold text-gray-500 uppercase py-2">Sen</div>
                    <div class="text-center text-[10px] font-bold text-gray-500 uppercase py-2">Sel</div>
                    <div class="text-center text-[10px] font-bold text-gray-500 uppercase py-2">Rab</div>
                    <div class="text-center text-[10px] font-bold text-gray-500 uppercase py-2">Kam</div>
                    <div class="text-center text-[10px] font-bold text-gray-500 uppercase py-2">Jum</div>
                    <div class="text-center text-[10px] font-bold text-gray-500 uppercase py-2">Sab</div>
                    <div class="text-center text-[10px] font-bold text-gray-500 uppercase py-2">Min</div>
                </div>
                <div id="calendar-grid" class="grid grid-cols-7 gap-1">
                    <div class="col-span-7 text-center py-10 text-gray-400">Memuat...</div>
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- LIST TASK --}}
    <div class="lg:col-span-1">
        <x-ui.card padding="p-0">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i data-lucide="list-checks" class="w-5 h-5 text-brand-primary"></i>
                    <h3 class="font-bold text-gray-800">Daftar Task</h3>
                </div>
                <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full" id="total-tasks">0</span>
            </div>
            <div class="p-3 max-h-[600px] overflow-y-auto">
                <div id="task-list" class="space-y-2">
                    <p class="text-center py-6 text-gray-400 text-xs">Memuat...</p>
                </div>
            </div>
        </x-ui.card>
    </div>
</div>

<script>
window.TASKS_DATA = {
    initialBulan: @json($bulan),
    initialTahun: @json($tahun),
    routes: {
        data: @json(route('keuangan.tasks.data')),
        calendar: @json(route('keuangan.tasks.calendar')),
        store: @json(route('keuangan.tasks.store')),
        update: @json(route('keuangan.tasks.update', ['id' => '__ID__'])),
        updateStatus: @json(route('keuangan.tasks.update-status', ['id' => '__ID__'])),
        destroy: @json(route('keuangan.tasks.destroy', ['id' => '__ID__'])),
        assignableUsers: @json(route('keuangan.tasks.assignable-users')),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/tasks.js') }}?v={{ filemtime(public_path('js/pages/tasks.js')) }}"></script>
@endpush
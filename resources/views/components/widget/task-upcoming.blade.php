@props(['title' => 'Task Mendatang', 'limit' => 5])

@php
    $user = auth()->user();
    $role = $user?->role;

    $seeAllUrl = '#';
    if ($role === 'keuangan' && \Illuminate\Support\Facades\Route::has('keuangan.tasks.index')) {
        $seeAllUrl = route('keuangan.tasks.index');
    } elseif ($role === 'pimpinan' && \Illuminate\Support\Facades\Route::has('pimpinan.tasks.index')) {
        $seeAllUrl = route('pimpinan.tasks.index');
    }
@endphp

<div id="upcoming-tasks-widget" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-full flex flex-col">
    <div class="px-5 py-4 flex items-center justify-between gap-2 border-b border-gray-100">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-400 flex items-center justify-center shadow-sm flex-shrink-0">
                <i data-lucide="alarm-clock" class="w-4 h-4 text-white"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800 leading-tight">{{ $title }}</h3>
                <p class="text-[10px] text-gray-500">{{ $limit }} task terdekat</p>
            </div>
        </div>
        <a href="{{ $seeAllUrl }}"
            class="text-[10px] font-bold text-indigo-600 hover:underline flex items-center gap-1">
            Lihat Semua
            <i data-lucide="arrow-right" class="w-3 h-3"></i>
        </a>
    </div>

    <div id="upcoming-tasks-body" class="p-3 flex-1 space-y-1.5 overflow-y-auto max-h-[400px]">
        <div class="text-center py-8">
            <i data-lucide="loader" class="w-5 h-5 mx-auto text-gray-300 animate-spin"></i>
            <p class="text-[10px] text-gray-400 mt-2">Memuat...</p>
        </div>
    </div>
</div>
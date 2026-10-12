@extends('layouts.app')

@section('title', 'SIKAP - Dashboard Admin')

@section('content')
{{-- HEADER --}}
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <x-ui.page-title
        :title="'Selamat Datang, ' . auth()->user()->name"
        :subtitle="auth()->user()->role_label . ' · ' . now()->translatedFormat('l, d F Y')"
    />
    <div class="hidden md:flex items-center gap-2 text-caption bg-blue-50 text-brand-primary px-3 py-1.5 rounded-full font-medium">
        <i data-lucide="activity" class="w-3.5 h-3.5"></i>
        Sistem Aktif
    </div>
</div>

{{-- STAT CARDS --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    <a href="{{ route('admin.users') }}" class="block">
        <x-ui.card padding="p-5" class="hover:shadow-medium hover:border-blue-200 transition group cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <x-ui.label uppercase>Total User</x-ui.label>
                    <p class="text-h4 font-bold text-brand-primary mt-2">{{ $stats['total_user'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-50 text-brand-primary flex items-center justify-center group-hover:scale-110 transition">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
            </div>
        </x-ui.card>
    </a>

    <a href="{{ route('admin.master') }}" class="block">
        <x-ui.card padding="p-5" class="hover:shadow-medium hover:border-emerald-200 transition group cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <x-ui.label uppercase>Total Master Data</x-ui.label>
                    <p class="text-h4 font-bold text-green-700 mt-2">{{ $stats['total_master'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-50 text-green-700 flex items-center justify-center group-hover:scale-110 transition">
                    <i data-lucide="database" class="w-6 h-6"></i>
                </div>
            </div>
        </x-ui.card>
    </a>

    <a href="{{ route('admin.audit') }}" class="block">
        <x-ui.card padding="p-5" class="hover:shadow-medium hover:border-amber-200 transition group cursor-pointer">
            <div class="flex items-center justify-between">
                <div>
                    <x-ui.label uppercase>Total Log</x-ui.label>
                    <p class="text-h4 font-bold text-amber-700 mt-2">{{ $stats['total_log'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center group-hover:scale-110 transition">
                    <i data-lucide="scroll-text" class="w-6 h-6"></i>
                </div>
            </div>
        </x-ui.card>
    </a>
</div>

{{-- AKTIVITAS TERBARU --}}
<x-ui.card>
    <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
        <x-ui.section-title title="Aktivitas Terbaru" icon="activity" size="sm" />
        <a href="{{ route('admin.audit') }}"
            class="text-caption text-brand-primary hover:underline font-semibold flex items-center gap-1 transition-blueprint">
            Lihat Semua <i data-lucide="arrow-right" class="w-3 h-3"></i>
        </a>
    </div>

    <ul class="divide-y divide-gray-100">
        @forelse ($recentLogs as $log)
            @php
                $iconMap = [
                    'CREATE'  => 'plus-circle',
                    'UPDATE'  => 'edit-3',
                    'DELETE'  => 'trash-2',
                    'RESTORE' => 'rotate-ccw',
                ];
                $colorMap = [
                    'CREATE'  => 'text-green-700 bg-green-50',
                    'UPDATE'  => 'text-brand-primary bg-blue-50',
                    'DELETE'  => 'text-red-700 bg-red-50',
                    'RESTORE' => 'text-purple-700 bg-purple-50',
                ];
                $textColorMap = [
                    'CREATE'  => 'text-green-700',
                    'UPDATE'  => 'text-brand-primary',
                    'DELETE'  => 'text-red-700',
                    'RESTORE' => 'text-purple-700',
                ];
            @endphp
            <li class="flex items-center gap-3 py-3">
                <div class="w-9 h-9 rounded-lg {{ $colorMap[$log->action] ?? 'text-gray-600 bg-gray-50' }} flex items-center justify-center flex-shrink-0">
                    <i data-lucide="{{ $iconMap[$log->action] ?? 'circle' }}" class="w-4 h-4"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-label text-slate truncate">
                        <span class="font-semibold">{{ $log->user_name }}</span>
                        <span class="text-caption font-bold {{ $textColorMap[$log->action] ?? 'text-gray-700' }} mx-1">{{ $log->action }}</span>
                        <span class="text-muted font-normal">{{ $log->module }}</span>
                    </p>
                    <x-ui.caption variant="muted" class="mt-0.5">{{ $log->created_at->translatedFormat('d M Y H:i') }}</x-ui.caption>
                </div>
                <span class="text-caption font-mono text-muted hidden sm:inline">{{ $log->record_id }}</span>
            </li>
        @empty
            <li class="text-center py-8 text-muted text-label">
                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                Belum ada aktivitas
            </li>
        @endforelse
    </ul>
</x-ui.card>
@endsection

@push('scripts')
@endpush
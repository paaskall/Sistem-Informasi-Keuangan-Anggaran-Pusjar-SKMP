@extends('layouts.app')

@section('title', 'Changelog')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl shadow-lg p-6 sm:p-8 text-white">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                <i data-lucide="list" class="w-7 h-7"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold">Changelog SIKAP</h1>
                <p class="text-emerald-100 text-sm mt-1">Riwayat versi dan perubahan aplikasi</p>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="space-y-4">
        @foreach($releases as $release)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Header Versi --}}
            <div class="bg-gradient-to-r from-gray-50 to-blue-50/50 px-6 py-4 border-b border-gray-100">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-500 flex items-center justify-center flex-shrink-0 shadow-sm">
                            <i data-lucide="package" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-bold text-gray-800">Versi {{ $release['version'] }}</h3>
                                @php
                                    $typeColor = match($release['type']) {
                                        'major' => 'bg-red-100 text-red-700',
                                        'minor' => 'bg-blue-100 text-blue-700',
                                        'patch' => 'bg-gray-100 text-gray-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="text-[10px] font-bold {{ $typeColor }} px-2 py-0.5 rounded-full uppercase">{{ $release['type'] }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $release['title'] }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-gray-600 bg-gray-100 px-3 py-1 rounded-full flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3 h-3"></i>
                        {{ \Carbon\Carbon::parse($release['date'])->format('d M Y') }}
                    </span>
                </div>
            </div>

            {{-- Changes --}}
            <div class="p-6 space-y-4">
                {{-- New --}}
                @if(!empty($release['changes']['new']))
                <div>
                    <p class="text-xs font-bold text-green-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        Fitur Baru
                    </p>
                    <ul class="space-y-1.5">
                        @foreach($release['changes']['new'] as $change)
                        <li class="flex items-start gap-2 text-sm text-gray-700">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5"></i>
                            <span>{{ $change }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Fixed --}}
                @if(!empty($release['changes']['fixed']))
                <div>
                    <p class="text-xs font-bold text-blue-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                        Perbaikan
                    </p>
                    <ul class="space-y-1.5">
                        @foreach($release['changes']['fixed'] as $change)
                        <li class="flex items-start gap-2 text-sm text-gray-700">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5"></i>
                            <span>{{ $change }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Breaking --}}
                @if(!empty($release['changes']['breaking']))
                <div>
                    <p class="text-xs font-bold text-red-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                        Breaking Changes
                    </p>
                    <ul class="space-y-1.5">
                        @foreach($release['changes']['breaking'] as $change)
                        <li class="flex items-start gap-2 text-sm text-red-700 bg-red-50 p-2 rounded">
                            <i data-lucide="alert-octagon" class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5"></i>
                            <span>{{ $change }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
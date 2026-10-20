@extends('layouts.app')

@section('title', 'Dokumentasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg p-6 sm:p-8 text-white">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                <i data-lucide="book-open" class="w-7 h-7"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold">Dokumentasi SIKAP</h1>
                <p class="text-indigo-100 text-sm mt-1">Panduan lengkap penggunaan aplikasi</p>
            </div>
        </div>
    </div>

    {{-- Sections --}}
    @foreach($sections as $section)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="{{ $section['icon'] }}" class="w-5 h-5 text-indigo-600"></i>
            {{ $section['title'] }}
        </h2>

        <div class="space-y-3">
            @foreach($section['items'] as $item)
            <details class="group border border-gray-100 rounded-lg overflow-hidden">
                <summary class="flex items-center justify-between gap-3 p-4 cursor-pointer bg-gray-50 hover:bg-indigo-50 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 group-open:rotate-90 transition-transform flex-shrink-0"></i>
                        <span class="text-sm font-semibold text-gray-800">{{ $item['title'] }}</span>
                    </div>
                </summary>
                <div class="p-4 pt-0 bg-white">
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $item['content'] }}</p>
                </div>
            </details>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
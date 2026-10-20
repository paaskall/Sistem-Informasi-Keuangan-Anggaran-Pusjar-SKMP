@extends('layouts.app')

@section('title', 'Laporkan Bug')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-amber-600 to-orange-600 rounded-2xl shadow-lg p-6 sm:p-8 text-white">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="bug" class="w-7 h-7"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold">Laporkan Bug</h1>
                    <p class="text-amber-100 text-sm mt-1">Bantu kami tingkatkan kualitas aplikasi</p>
                </div>
            </div>
            <button type="button" onclick="openBugForm()"
                class="bg-white text-amber-700 hover:bg-amber-50 font-semibold px-4 py-2.5 rounded-lg shadow-sm flex items-center gap-2 transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Lapor Bug Baru
            </button>
        </div>
    </div>

    {{-- List Laporan --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i data-lucide="list" class="w-5 h-5 text-amber-600"></i>
                Laporan Saya
            </h3>
        </div>
        <div id="bug-report-list" class="divide-y divide-gray-100">
            <div class="text-center py-10">
                <i data-lucide="loader" class="w-5 h-5 mx-auto text-gray-300 animate-spin"></i>
                <p class="text-xs text-gray-400 mt-2">Memuat...</p>
            </div>
        </div>
    </div>
</div>

<script>
window.BUG_REPORT_DATA = {
    routes: {
        data: @json(route('bug-report.data')),
        store: @json(route('bug-report.store')),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/bug-report.js') }}?v={{ filemtime(public_path('js/pages/bug-report.js')) }}"></script>
@endpush
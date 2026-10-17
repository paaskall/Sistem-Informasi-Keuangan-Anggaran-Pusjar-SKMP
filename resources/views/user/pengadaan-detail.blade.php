@extends('layouts.app')

@section('title', 'SIKAP - Detail Pengadaan')

@section('content')
{{-- BACK LINK --}}
<div class="mb-6">
    <a href="{{ route('user.pengadaan') }}"
        class="inline-flex items-center gap-1.5 text-label text-muted hover:text-brand-primary transition-blueprint">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Kembali ke Daftar Pengadaan
    </a>
</div>

{{-- HEADER INFO --}}
<div id="pengadaan-header" class="mb-6">
    <div class="text-center py-10 text-muted text-label">Memuat...</div>
</div>

{{-- PROGRESS CARD --}}
<div id="progress-card" class="mb-6"></div>

{{-- DAFTAR TAHAPAN --}}
<x-ui.card padding="p-0">
    <div class="p-6 flex items-center justify-between border-b border-gray-100 gap-3 flex-wrap">
        <x-ui.section-title
            title="Tahapan Pengadaan"
            icon="list-checks"
            size="sm"
            subtitle="Pantau tahapan pengadaan - centang menandakan selesai"
        />
    </div>

    <div id="tahapan-list" class="p-6 space-y-2">
        <p class="text-center py-8 text-muted text-label">Memuat...</p>
    </div>
</x-ui.card>

<script>
window.PENGADAAN_DETAIL_DATA = {
    pengadaanId: @json($pengadaanId),
    routes: {
        data: @json(route('user.pengadaan.detail-data', ['id' => '__ID__'])),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/pengadaan.css') }}?v={{ filemtime(public_path('css/pages/pengadaan.css')) }}">
@endpush

@push('scripts')
<script src="{{ asset('js/pages/pengadaan-detail-user.js') }}?v={{ filemtime(public_path('js/pages/pengadaan-detail-user.js')) }}"></script>
@endpush
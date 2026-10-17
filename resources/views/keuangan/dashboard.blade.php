@extends('layouts.app')

@section('title', 'SIKAP - Dashboard Keuangan')

@section('content')

{{-- ========== HEADER: judul, tahun, satu aksi utama, menu lainnya ========== --}}
<div class="mb-5 flex flex-wrap items-center justify-between gap-4">
    <x-ui.page-title
        title="Dashboard Keuangan"
        subtitle="Ringkasan keuangan tahun {{ $tahun }}"
    />

    <div class="flex items-center gap-2">
        <label for="filter-tahun" class="sr-only">Tahun anggaran</label>
        <select id="filter-tahun" onchange="changeTahun(this.value)"
            class="h-11 px-3 rounded-md text-body border border-border bg-white text-slate focus:border-brand-secondary focus:ring-2 focus:ring-blue-100">
            @foreach ($tahunOptions as $y)
                <option value="{{ $y }}" {{ (int) $y === (int) $tahun ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>

        <a id="btn-input-realisasi" href="{{ route('keuangan.realisasi') }}"
           class="h-11 px-4 inline-flex items-center gap-2 rounded-md bg-brand-primary text-white text-label hover:bg-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary focus-visible:ring-offset-2">
            <i data-lucide="trending-up" class="w-4 h-4"></i>
            Input realisasi
        </a>
    </div>
</div>

{{-- Banner arsip (ditampilkan JS saat tahun yang dipilih bukan tahun berjalan) --}}
<div id="arsip-banner" class="hidden mb-5" role="status">
    <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
        <i data-lucide="lock" class="w-5 h-5 mt-0.5 flex-shrink-0"></i>
        <p class="text-sm">
            <span class="font-semibold">Hanya untuk dibaca.</span>
            Anda sedang melihat arsip tahun <span id="arsip-tahun">{{ $tahun }}</span>.
        </p>
    </div>
</div>

{{-- ========== TAB ========== --}}
<div role="tablist" aria-label="Bagian dashboard" class="mb-6 flex gap-1 overflow-x-auto border-b border-border">
    <button type="button" role="tab" id="tab-ringkasan" data-tab="ringkasan" aria-controls="panel-ringkasan" aria-selected="true"
        class="-mb-px whitespace-nowrap border-b-2 border-transparent px-4 py-3 text-label text-muted hover:text-slate aria-selected:border-brand-primary aria-selected:text-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary rounded-t-sm">
        Ringkasan
    </button>
    <button type="button" role="tab" id="tab-pembayaran" data-tab="pembayaran" aria-controls="panel-pembayaran" aria-selected="false" tabindex="-1"
        class="-mb-px whitespace-nowrap border-b-2 border-transparent px-4 py-3 text-label text-muted hover:text-slate aria-selected:border-brand-primary aria-selected:text-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary rounded-t-sm">
        Pembayaran dan risiko
    </button>
    <button type="button" role="tab" id="tab-pengadaan" data-tab="pengadaan" aria-controls="panel-pengadaan" aria-selected="false" tabindex="-1"
        class="-mb-px whitespace-nowrap border-b-2 border-transparent px-4 py-3 text-label text-muted hover:text-slate aria-selected:border-brand-primary aria-selected:text-brand-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary rounded-t-sm">
        Pengadaan
    </button>
</div>

{{-- ========== TAB 1: RINGKASAN ========== --}}
<div id="panel-ringkasan" role="tabpanel" aria-labelledby="tab-ringkasan">
    <div class="flex flex-col gap-6">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div id="summary-card" class="h-full">
                <div class="h-full min-h-[240px] rounded-lg bg-white border border-border p-6 animate-pulse motion-reduce:animate-none" aria-busy="true">
                    <div class="h-4 w-1/3 rounded bg-gray-100"></div>
                    <div class="h-14 w-1/4 rounded bg-gray-100 mt-8"></div>
                    <div class="h-3 w-full rounded-full bg-gray-100 mt-10"></div>
                </div>
            </div>
            <div id="pnbp-summary-card" class="h-full">
                <div class="h-full min-h-[240px] rounded-lg bg-white border border-border p-6 animate-pulse motion-reduce:animate-none" aria-busy="true">
                    <div class="h-4 w-1/3 rounded bg-gray-100"></div>
                    <div class="h-10 w-1/5 rounded bg-gray-100 mt-8"></div>
                    <div class="h-3 w-full rounded-full bg-gray-100 mt-8"></div>
                </div>
            </div>
        </div>

        <div id="bulan-ini-container" class="empty:hidden"></div>

        <section class="bg-white border border-border rounded-lg shadow-soft overflow-hidden">
                <header class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-border">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-md bg-info-bg text-brand-primary flex items-center justify-center flex-shrink-0">
                            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-semibold text-slate leading-tight">Target dan realisasi bulanan</h3>
                            <p class="text-caption text-muted mt-0.5">Perbandingan per bulan tahun <span id="chart-tahun">{{ $tahun }}</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-caption text-muted">
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-brand-secondary/50"></span>Target</span>
                        <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-brand-primary"></span>Realisasi</span>
                    </div>
                </header>
                <div class="p-5">
                    <div id="chart-container">
                        <div class="h-[300px] rounded-md bg-gray-50 animate-pulse motion-reduce:animate-none" aria-busy="true"></div>
                    </div>
                </div>
        </section>

        {{-- Pengingat input & task mendatang: satu baris --}}
        <div class="flex flex-wrap items-start gap-6">
            <div class="flex-1 min-w-[320px] has-[#reminder-widget.hidden]:hidden">
                <x-widget.reminder-widget />
            </div>
            <div class="flex-1 min-w-[320px]">
                <x-widget.task-upcoming title="Task mendatang" :limit="5" />
            </div>
        </div>

    </div>
</div>

{{-- ========== TAB 2: PEMBAYARAN DAN RISIKO ========== --}}
<div id="panel-pembayaran" role="tabpanel" aria-labelledby="tab-pembayaran" hidden>
    <div class="flex flex-col gap-6">
        <div id="rencana-bulan-ini-container" class="empty:hidden"></div>
        <div id="catatan-kendala-container" class="empty:hidden"></div>
        <div id="akselerasi-container" class="empty:hidden"></div>
    </div>
</div>

{{-- ========== TAB 3: PENGADAAN ========== --}}
<div id="panel-pengadaan" role="tabpanel" aria-labelledby="tab-pengadaan" hidden>
    <div class="flex flex-col gap-6">
        <div id="progress-pengadaan-container" class="empty:hidden"></div>
        <div id="rencana-pengadaan-container" class="empty:hidden"></div>
        <div id="kendala-pengadaan-container" class="empty:hidden"></div>
    </div>
</div>

<script>
window.DASHBOARD_DATA = {
    initialTahun: @json($tahun),
    tahunBerjalan: @json($tahunBerjalan),
    role: 'keuangan',
    showReminderOnLoad: @json($showReminder),
    routes: {
        data: @json(route('keuangan.dashboard.data')),
    },
    csrf: @json(csrf_token()),
};

// Tab + menu "Lainnya"
(function () {
    const tabs = Array.from(document.querySelectorAll('[role="tab"]'));

    function show(id, focus) {
        if (!tabs.some(t => t.dataset.tab === id)) id = 'ringkasan';
        tabs.forEach(t => {
            const on = t.dataset.tab === id;
            t.setAttribute('aria-selected', on ? 'true' : 'false');
            t.tabIndex = on ? 0 : -1;
            document.getElementById('panel-' + t.dataset.tab).hidden = !on;
            if (on && focus) t.focus();
        });
        history.replaceState(null, '', id === 'ringkasan' ? location.pathname + location.search : '#' + id);
    }

    tabs.forEach((t, i) => {
        t.addEventListener('click', () => show(t.dataset.tab));
        t.addEventListener('keydown', (e) => {
            if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
            e.preventDefault();
            const next = (i + (e.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
            show(tabs[next].dataset.tab, true);
        });
    });

    show(location.hash.replace('#', '') || 'ringkasan');

    const menu = document.getElementById('menu-lainnya');
    document.addEventListener('click', (e) => { if (menu && !menu.contains(e.target)) menu.removeAttribute('open'); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && menu) menu.removeAttribute('open'); });
})();
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/dashboard.js') }}?v={{ filemtime(public_path('js/pages/dashboard.js')) }}"></script>
@endpush
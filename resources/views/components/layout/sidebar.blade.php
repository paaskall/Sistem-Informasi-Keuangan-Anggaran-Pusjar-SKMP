@props(['active' => null])

@php
    $user = auth()->user();
    $role = $user?->role ?? 'guest';

    $menus = [
        'admin' => [
            ['section' => 'Dashboard', 'icon' => null, 'items' => [
                ['route' => 'admin.dashboard', 'url' => route('admin.dashboard'), 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
            ]],
            ['section' => 'Master Data', 'icon' => 'database', 'items' => [
                ['url' => route('admin.master', ['tab' => 'program']), 'icon' => 'folder-kanban', 'label' => 'Program'],
                ['url' => route('admin.master', ['tab' => 'subKegiatan']), 'icon' => 'git-branch', 'label' => 'Sub-Kegiatan'],
                ['url' => route('admin.master', ['tab' => 'komponenBelanja']), 'icon' => 'receipt', 'label' => 'Komponen Belanja'],
                ['url' => route('admin.master', ['tab' => 'unit']), 'icon' => 'building-2', 'label' => 'Unit'],
                ['url' => route('admin.master', ['tab' => 'jenisPengadaan']), 'icon' => 'tags', 'label' => 'Jenis Pengadaan'],
                ['url' => route('admin.master', ['tab' => 'tahapan']), 'icon' => 'list-ordered', 'label' => 'Tahapan'],
                ['url' => route('admin.master', ['tab' => 'status']), 'icon' => 'badge-check', 'label' => 'Status'],
                ['url' => route('admin.master', ['tab' => 'kategoriKendala']), 'icon' => 'alert-octagon', 'label' => 'Kategori Kendala'],
            ]],
            ['section' => 'Manajemen User', 'icon' => 'users', 'items' => [
                ['url' => route('admin.users'), 'icon' => 'user-cog', 'label' => 'Kelola User'],
                ['url' => route('admin.audit'), 'icon' => 'scroll-text', 'label' => 'Audit Trail'],
            ]],
        ],
        'keuangan' => [
            ['section' => 'Menu Utama', 'icon' => null, 'items' => [
                ['url' => route('keuangan.dashboard'), 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
            ]],
            ['section' => 'Perencanaan', 'icon' => 'clipboard-list', 'items' => [
                ['url' => route('keuangan.target'), 'icon' => 'target', 'label' => 'Target'],
                ['url' => route('keuangan.realisasi'), 'icon' => 'trending-up', 'label' => 'Realisasi'],
            ]],
            ['section' => 'Pelaksanaan', 'icon' => 'briefcase', 'items' => [
                ['url' => route('keuangan.pengadaan'), 'icon' => 'shopping-cart', 'label' => 'Pengadaan'],
                ['url' => route('keuangan.kendala'), 'icon' => 'alert-triangle', 'label' => 'Kendala'],
            ]],
            ['section' => 'Pelaporan', 'icon' => 'file-spreadsheet', 'items' => [
                ['url' => route('keuangan.pnbp'), 'icon' => 'receipt', 'label' => 'PNBP'],
                ['url' => route('keuangan.laporan'), 'icon' => 'file-text', 'label' => 'Laporan'],
            ]],
        ],
        'pimpinan' => [
            ['section' => 'Menu Utama', 'icon' => null, 'items' => [
                ['url' => route('pimpinan.dashboard'), 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
            ]],
            ['section' => 'Informasi', 'icon' => 'info', 'items' => [
                ['url' => route('pimpinan.laporan'), 'icon' => 'file-text', 'label' => 'Target & Realisasi'],
                ['url' => route('pimpinan.pengadaan'), 'icon' => 'shopping-cart', 'label' => 'Pengadaan'],
                ['url' => route('pimpinan.pnbp'), 'icon' => 'receipt', 'label' => 'PNBP'],
                ['url' => route('pimpinan.audit'), 'icon' => 'scroll-text', 'label' => 'Audit Trail'],
            ]],
        ],
        'viewer' => [
            ['section' => 'Menu Utama', 'icon' => null, 'items' => [
                ['url' => route('viewer.dashboard'), 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
            ]],
            ['section' => 'Data', 'icon' => 'database', 'items' => [
                ['url' => route('viewer.target'), 'icon' => 'target', 'label' => 'Target'],
                ['url' => route('viewer.realisasi'), 'icon' => 'trending-up', 'label' => 'Realisasi'],
                ['url' => route('viewer.pengadaan'), 'icon' => 'shopping-cart', 'label' => 'Pengadaan'],
                ['url' => route('viewer.pnbp'), 'icon' => 'receipt', 'label' => 'PNBP'],
            ]],
        ],
    ];

    $menu = $menus[$role] ?? [];
@endphp

{{-- Brand --}}
<div class="p-5 border-b border-gray-100 flex items-center gap-3 flex-shrink-0">
    <img src="{{ asset('assets/logo-lanri.png') }}" alt="SIKAP"
         class="w-10 h-10 object-contain rounded-lg flex-shrink-0" />
    <div class="min-w-0">
        <h3 class="text-body-lg font-bold text-brand-primary leading-tight tracking-wide">SIKAP</h3>
        <p class="text-caption text-muted tracking-wide leading-tight mt-0.5">
            Sistem Informasi Keuangan &amp; Anggaran<br/>Pusjar SKMP
        </p>
    </div>
</div>

{{-- Menu --}}
<nav class="flex-1 overflow-y-auto py-3">
    @foreach ($menu as $section)
        <p class="px-5 py-2 mt-2 text-caption font-bold text-muted tracking-widest uppercase flex items-center gap-1.5">
            @if ($section['icon'])
                <i data-lucide="{{ $section['icon'] }}" class="w-3 h-3"></i>
            @endif
            {{ $section['section'] }}
        </p>

        @foreach ($section['items'] as $item)
            @php
                // Fix: support query string (?tab=program) & tanpa query
                $itemUrl = $item['url'];
                $itemUrlNoQuery = strtok($itemUrl, '?');
                $isActive = request()->fullUrl() === $itemUrl
                         || request()->url() === $itemUrlNoQuery;
                $cls = $isActive
                    ? 'bg-info-bg text-brand-primary border-brand-primary font-semibold'
                    : 'text-slate border-transparent hover:bg-info-bg hover:text-brand-primary';
            @endphp
            <a href="{{ $item['url'] }}" onclick="closeMobileSidebar()"
               class="flex items-center gap-3 px-5 py-2.5 text-label border-l-4 transition-blueprint group {{ $cls }}">
                <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 flex-shrink-0"></i>
                <span class="truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    @endforeach
</nav>

{{-- User Card --}}
@if ($user)
    <div class="border-t border-gray-100 flex-shrink-0">
        <a href="{{ route('profile') }}" 
           class="flex items-center gap-3 p-4 hover:bg-info-bg transition-blueprint group">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-primary to-brand-secondary flex items-center justify-center text-white text-label font-bold flex-shrink-0 overflow-hidden">
                @if ($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover" />
                @else
                    {{ $user->initial }}
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-label font-semibold text-slate truncate">{{ $user->name }}</p>
                <p class="text-caption text-muted uppercase tracking-wider truncate group-hover:text-brand-primary transition-blueprint">
                    {{ $user->role_label }} · Profile
                </p>
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4 text-muted group-hover:text-brand-primary transition-blueprint"></i>
        </a>
    </div>
@endif
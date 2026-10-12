@php
    $user = auth()->user();
@endphp

<div class="flex items-center gap-3 min-w-0">
    <button class="md:hidden text-slate hover:text-brand-primary transition-blueprint flex-shrink-0"
            onclick="openMobileSidebar()" type="button">
        <i data-lucide="menu" class="w-5 h-5"></i>
    </button>
    <img src="{{ asset('assets/logo-lanri.png') }}" alt="SIKAP"
         class="w-7 h-7 object-contain rounded-md flex-shrink-0" />
    <span class="font-bold tracking-wider text-label text-brand-primary">SIKAP</span>
    <span class="hidden lg:inline text-caption text-muted border-l border-gray-200 pl-3 ml-1 truncate">
        Sistem Informasi Keuangan &amp; Anggaran Pusjar SKMP
    </span>
</div>

<div class="flex items-center gap-3 sm:gap-4 text-label flex-shrink-0">
    <a href="{{ route('profile') }}" 
    class="hidden sm:flex items-center gap-2 bg-info-bg hover:bg-blue-100 text-brand-primary px-3 py-1.5 rounded-full border border-blue-100 transition-blueprint">
        <i data-lucide="user-circle" class="w-4 h-4"></i>
        <span class="font-semibold max-w-[120px] truncate">{{ $user?->name }}</span>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
    </a>

    <form method="POST" action="{{ route('logout') }}" id="logout-form" class="hidden">
        @csrf
    </form>

    <button type="button" onclick="logoutConfirm()"
            class="flex items-center gap-1.5 text-muted hover:text-red-600 transition-blueprint font-semibold">
        <i data-lucide="log-out" class="w-4 h-4"></i>
        <span class="hidden sm:inline">Logout</span>
    </button>
</div>
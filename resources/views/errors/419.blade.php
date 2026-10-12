@extends('errors.layout')

@section('title', 'Sesi Berakhir')

@section('content')
<div class="p-8 text-center">
    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-orange-50 flex items-center justify-center relative">
        <i data-lucide="clock-alert" class="w-12 h-12 text-orange-600"></i>
        <div class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-orange-500 animate-ping"></div>
        <div class="absolute -top-1 -right-1 w-3 h-3 rounded-full bg-orange-500"></div>
    </div>

    <p class="text-6xl font-black text-orange-600 mb-2">419</p>
    <h1 class="text-2xl font-bold text-gray-800 mb-3">Sesi Anda Berakhir</h1>
    <p class="text-sm text-gray-500 mb-6 leading-relaxed">
        Sesi Anda sudah kedaluwarsa karena tidak aktif terlalu lama.<br/>
        Silakan login kembali untuk melanjutkan.
    </p>

    {{-- COUNTDOWN --}}
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 mb-6 inline-flex items-center gap-3 text-sm text-orange-800">
        <i data-lucide="loader" class="w-4 h-4 animate-spin"></i>
        <span>Redirect otomatis dalam <b id="countdown">5</b> detik...</span>
    </div>

    <div class="flex flex-wrap gap-2 justify-center">
        <a href="{{ route('login') }}"
           class="bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg shadow-md shadow-orange-200 flex items-center gap-2 transition">
            <i data-lucide="log-in" class="w-4 h-4"></i>
            Login Ulang Sekarang
        </a>
    </div>
</div>

@push('scripts')
<script>
    let count = 5;
    const el = document.getElementById('countdown');
    const interval = setInterval(() => {
        count--;
        if (el) el.textContent = count;
        if (count <= 0) {
            clearInterval(interval);
            window.location.href = "{{ route('login') }}";
        }
    }, 1000);
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'SIKAP - Profile Saya')

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 bg-gradient-to-b from-blue-700 to-blue-400 rounded-full"></div>
        <h2 class="text-2xl font-bold text-gray-800">Profile Saya</h2>
    </div>
    <p class="text-sm text-gray-500 ml-4">Kelola informasi akun pribadi Anda</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- KOLOM KIRI: Avatar + Info Singkat --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center">
            <div class="relative inline-block mb-4">
                <div id="avatar-preview" class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-700 to-blue-500 flex items-center justify-center text-white text-5xl font-bold mx-auto overflow-hidden">
                    <span id="avatar-initial">?</span>
                </div>
                <button type="button" onclick="triggerAvatarUpload()"
                    class="absolute bottom-1 right-1 w-9 h-9 rounded-full bg-blue-700 hover:bg-blue-800 text-white flex items-center justify-center shadow-lg transition"
                    title="Ganti Avatar">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                </button>
                <input type="file" id="avatar-input" class="hidden" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" onchange="uploadAvatar(this)" />
            </div>

            <h3 id="profile-name" class="text-xl font-bold text-gray-800 mb-1">-</h3>
            <p id="profile-username" class="text-sm text-gray-500 font-mono mb-3">@-</p>

            <div class="inline-flex items-center gap-1.5 text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1.5 rounded-full mb-2">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span id="profile-role">-</span>
            </div>

            <div class="text-xs text-gray-500 mt-3 space-y-1">
                <p><b>Unit:</b> <span id="profile-unit">-</span></p>
                <p><b>Bergabung:</b> <span id="profile-joined">-</span></p>
            </div>

            <p class="text-[10px] text-gray-400 mt-3 pt-3 border-t border-gray-100">
                Format: JPG, PNG, GIF, WEBP · Maks 2 MB
            </p>
        </div>

        {{-- INFO AKTIVITAS --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mt-6">
            <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                <i data-lucide="activity" class="w-4 h-4 text-blue-700"></i>
                Aktivitas Saya
            </h4>
            <ul id="activity-list" class="divide-y divide-gray-100 text-xs">
                <li class="text-center py-3 text-gray-400">Memuat...</li>
            </ul>
        </div>
    </div>

    {{-- KOLOM KANAN: Form Edit --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- FORM PROFILE --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="user-cog" class="w-5 h-5 text-blue-700"></i>
                Informasi Pribadi
            </h3>

            <form id="form-profile" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input id="f-name" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Username <span class="text-red-500">*</span></label>
                        <input id="f-username" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 font-mono" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Email</label>
                        <input id="f-email" type="email" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" placeholder="email@contoh.com" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">No. HP</label>
                        <input id="f-phone" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" placeholder="08xxxxxxxxxx" />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="submit" id="btn-save-profile"
                        class="bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium px-5 py-2.5 rounded-lg shadow-md shadow-blue-200 flex items-center gap-2 transition">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- FORM PASSWORD --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="lock" class="w-5 h-5 text-red-600"></i>
                Ubah Password
            </h3>

            <form id="form-password" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Password Saat Ini <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="f-current-password" type="password" class="w-full px-3 py-2.5 pr-10 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500" required autocomplete="current-password" />
                        <button type="button" onclick="toggleProfilePassword('f-current-password', 'eye-current-password')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <i id="eye-current-password" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Password Baru <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input id="f-new-password" type="password" class="w-full px-3 py-2.5 pr-10 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500" required autocomplete="new-password" />
                            <button type="button" onclick="toggleProfilePassword('f-new-password', 'eye-new-password')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i id="eye-new-password" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input id="f-new-password-confirm" type="password" class="w-full px-3 py-2.5 pr-10 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500" required autocomplete="new-password" />
                            <button type="button" onclick="toggleProfilePassword('f-new-password-confirm', 'eye-new-password-confirm')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i id="eye-new-password-confirm" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <p id="confirm-hint" class="hidden"></p>
                    </div>
                </div>

                {{-- LIVE CHECKLIST SYARAT PASSWORD --}}
                <div class="p-3 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[10px] font-bold text-blue-800 uppercase tracking-wider flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Syarat Password
                        </p>
                        <span id="pw-strength-label" class="text-[10px] font-bold text-gray-500">Belum diisi</span>
                    </div>

                    <div class="w-full bg-gray-200 rounded-full h-1.5 mb-3 overflow-hidden">
                        <div id="pw-strength-bar" class="h-full rounded-full transition-all duration-300 bg-gray-300" style="width: 0%"></div>
                    </div>

                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5">
                        <li id="rule-length" class="flex items-start gap-2 text-[11px] text-gray-400 transition-colors">
                            <span data-rule-icon class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-400">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            <span>Minimal <b>12 karakter</b></span>
                        </li>
                        <li id="rule-uppercase" class="flex items-start gap-2 text-[11px] text-gray-400 transition-colors">
                            <span data-rule-icon class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-400">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            <span>Huruf besar (<b>A-Z</b>)</span>
                        </li>
                        <li id="rule-lowercase" class="flex items-start gap-2 text-[11px] text-gray-400 transition-colors">
                            <span data-rule-icon class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-400">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            <span>Huruf kecil (<b>a-z</b>)</span>
                        </li>
                        <li id="rule-number" class="flex items-start gap-2 text-[11px] text-gray-400 transition-colors">
                            <span data-rule-icon class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-400">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            <span>Angka (<b>0-9</b>)</span>
                        </li>
                        <li id="rule-symbol" class="flex items-start gap-2 text-[11px] text-gray-400 transition-colors">
                            <span data-rule-icon class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-400">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                            <span>Simbol (<b>!@#$%^&*</b>)</span>
                        </li>
                    </ul>

                    <p class="text-[9px] text-blue-600 mt-2 pt-2 border-t border-blue-100 flex items-start gap-1">
                        <svg class="w-2.5 h-2.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Password juga dicek terhadap database kebocoran (haveibeenpwned.com)
                    </p>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 flex items-start gap-2 text-xs text-amber-800">
                    <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                    <span>Setelah ganti password, Anda akan tetap login. Disarankan logout dan login ulang untuk keamanan.</span>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="submit" id="btn-save-password"
                        class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg shadow-md shadow-red-200 flex items-center gap-2 transition">
                        <i data-lucide="key" class="w-4 h-4"></i> Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.PROFILE_DATA = {
    routes: {
        data: @json(route('profile.data')),
        update: @json(route('profile.update')),
        updatePassword: @json(route('profile.update-password')),
        uploadAvatar: @json(route('profile.upload-avatar')),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/profile.js') }}?v={{ filemtime(public_path('js/pages/profile.js')) }}"></script>
@endpush
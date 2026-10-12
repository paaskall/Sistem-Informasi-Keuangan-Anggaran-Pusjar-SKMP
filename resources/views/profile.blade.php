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
                    class="absolute bottom-1 right-1 w-9 h-9 rounded-full bg-blue-700 hover:bg-blue-800 text-white flex items-center justify-center shadow-lg transition">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                </button>
                <input type="file" id="avatar-input" class="hidden" accept="image/*" onchange="uploadAvatar(this)" />
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
                    <input id="f-current-password" type="password" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500" required />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Password Baru <span class="text-red-500">*</span></label>
                        <input id="f-new-password" type="password" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500" required />
                        <p class="text-[10px] text-gray-500 mt-1">Minimal 5 karakter</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Konfirmasi Password <span class="text-red-500">*</span></label>
                        <input id="f-new-password-confirm" type="password" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500" required />
                    </div>
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
<script src="{{ asset('js/pages/profile.js') }}"></script>
@endpush
// SIKAP - Profile Page

(function () {
    const DATA = window.PROFILE_DATA;

    async function apiCall(method, url, body, isFormData = false) {
        const opts = {
            method,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': DATA.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        };
        if (body) {
            if (isFormData) {
                opts.body = body;
            } else {
                opts.headers['Content-Type'] = 'application/json';
                opts.body = JSON.stringify(body);
            }
        }
        const res = await fetch(url, opts);
        const json = await res.json().catch(() => ({}));
        return { ok: res.ok, status: res.status, data: json };
    }

    // ============ LOAD DATA ============
    async function loadProfile() {
        const res = await apiCall('GET', DATA.routes.data);
        if (!res.ok) {
            toastError('Gagal memuat profile');
            return;
        }

        const u = res.data.user;

        // Update UI
        document.getElementById('profile-name').textContent = u.name;
        document.getElementById('profile-username').textContent = '@' + u.username;
        document.getElementById('profile-role').textContent = u.role_label;
        document.getElementById('profile-unit').textContent = u.unit || '-';
        document.getElementById('profile-joined').textContent = u.created_at || '-';

        // Avatar
        const avatarEl = document.getElementById('avatar-preview');
        const initialEl = document.getElementById('avatar-initial');

        if (u.avatar) {
            avatarEl.innerHTML = `<img src="/storage/${u.avatar}" class="w-full h-full object-cover" alt="Avatar" />`;
        } else {
            avatarEl.innerHTML = `<span id="avatar-initial">${u.initial}</span>`;
        }

        // Form fields
        document.getElementById('f-name').value = u.name;
        document.getElementById('f-username').value = u.username;
        document.getElementById('f-email').value = u.email || '';
        document.getElementById('f-phone').value = u.phone || '';

        // Activity logs
        renderActivity(res.data.logs || []);

        if (window.lucide) lucide.createIcons();
    }

    function renderActivity(logs) {
        const container = document.getElementById('activity-list');
        if (!logs || logs.length === 0) {
            container.innerHTML = `<li class="text-center py-3 text-gray-400">Belum ada aktivitas</li>`;
            return;
        }

        const actionColors = {
            CREATE: 'text-green-700 bg-green-50',
            UPDATE: 'text-blue-700 bg-blue-50',
            DELETE: 'text-red-700 bg-red-50',
            RESTORE: 'text-purple-700 bg-purple-50',
        };

        container.innerHTML = logs.map(l => {
            const cls = actionColors[l.action] || 'text-gray-700 bg-gray-50';
            const date = new Date(l.created_at);
            const formatted = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });

            return `
                <li class="py-2 flex items-start gap-2">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${cls}">${l.action}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-800 truncate">${l.module} <span class="text-gray-400 font-mono text-[10px]">${l.record_id}</span></p>
                        <p class="text-[10px] text-gray-400">${formatted}</p>
                    </div>
                </li>`;
        }).join('');
    }

    // ============ FORM PROFILE ============
    document.getElementById('form-profile').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('btn-save-profile');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Menyimpan...';
        if (window.lucide) lucide.createIcons();

        const data = {
            name: document.getElementById('f-name').value.trim(),
            username: document.getElementById('f-username').value.trim(),
            email: document.getElementById('f-email').value.trim() || null,
            phone: document.getElementById('f-phone').value.trim() || null,
        };

        const res = await apiCall('PUT', DATA.routes.update, data);

        if (res.ok) {
            toastSuccess(res.data.message);
            loadProfile();
        } else {
            const errors = res.data.errors || {};
            const first = Object.values(errors)[0];
            toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
        }

        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan';
        if (window.lucide) lucide.createIcons();
    });

    // ============ FORM PASSWORD ============
    document.getElementById('form-password').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('btn-save-password');
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Menyimpan...';
        if (window.lucide) lucide.createIcons();

        const data = {
            current_password: document.getElementById('f-current-password').value,
            new_password: document.getElementById('f-new-password').value,
            new_password_confirmation: document.getElementById('f-new-password-confirm').value,
        };

        const res = await apiCall('PUT', DATA.routes.updatePassword, data);

        if (res.ok) {
            toastSuccess(res.data.message);
            document.getElementById('form-password').reset();
        } else {
            const errors = res.data.errors || {};
            const first = Object.values(errors)[0];
            toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
        }

        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="key" class="w-4 h-4"></i> Ubah Password';
        if (window.lucide) lucide.createIcons();
    });

    // ============ AVATAR UPLOAD ============
    window.triggerAvatarUpload = function () {
        document.getElementById('avatar-input').click();
    };

    window.uploadAvatar = async function (input) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];
        const formData = new FormData();
        formData.append('avatar', file);

        const res = await apiCall('POST', DATA.routes.uploadAvatar, formData, true);

        if (res.ok) {
            toastSuccess(res.data.message);
            loadProfile();
        } else {
            const errors = res.data.errors || {};
            const first = Object.values(errors)[0];
            toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal upload avatar'));
        }

        input.value = ''; // reset
    };

    // ============ INIT ============
    document.addEventListener('DOMContentLoaded', loadProfile);
})();
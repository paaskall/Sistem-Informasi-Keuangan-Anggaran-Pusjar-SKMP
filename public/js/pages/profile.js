// ============================================================
// SIKAP - Profile Page
// Versi aman XSS + Live Password Rules Checklist + Avatar Preview
// ============================================================

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

    // ============================================================
    // TOGGLE SHOW/HIDE PASSWORD
    // ============================================================
    window.toggleProfilePassword = function (inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        if (window.lucide) lucide.createIcons();
    };

    // ============================================================
    // LIVE PASSWORD RULES CHECKER
    // ============================================================
    function checkPasswordRules(password) {
        return {
            length:    password.length >= 12,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number:    /[0-9]/.test(password),
            symbol:    /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(password),
        };
    }

    function getPasswordStrength(rules) {
        const passed = Object.values(rules).filter(Boolean).length;

        if (passed === 0) return { level: 'none',   label: 'Belum diisi', color: 'gray',  width: 0 };
        if (passed <= 2)  return { level: 'weak',   label: 'Lemah',       color: 'red',   width: 25 };
        if (passed <= 3)  return { level: 'fair',   label: 'Cukup',       color: 'amber', width: 50 };
        if (passed <= 4)  return { level: 'good',   label: 'Kuat',        color: 'blue',  width: 75 };
        return               { level: 'strong', label: 'Sangat Kuat', color: 'green', width: 100 };
    }

    function renderRuleItem(ruleId, passed) {
        const ruleEl = document.getElementById(ruleId);
        if (!ruleEl) return;

        const iconEl = ruleEl.querySelector('[data-rule-icon]');

        if (passed) {
            ruleEl.classList.remove('text-gray-400', 'text-gray-500');
            ruleEl.classList.add('text-green-700', 'font-semibold');

            if (iconEl) {
                iconEl.className = 'flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-green-500 text-white';
                iconEl.innerHTML = `
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                `;
            }
        } else {
            ruleEl.classList.remove('text-green-700', 'font-semibold');
            ruleEl.classList.add('text-gray-400');

            if (iconEl) {
                iconEl.className = 'flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-400';
                iconEl.innerHTML = `
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                `;
            }
        }
    }

    function updatePasswordUI(password) {
        const rules = checkPasswordRules(password);

        renderRuleItem('rule-length',    rules.length);
        renderRuleItem('rule-uppercase', rules.uppercase);
        renderRuleItem('rule-lowercase', rules.lowercase);
        renderRuleItem('rule-number',    rules.number);
        renderRuleItem('rule-symbol',    rules.symbol);

        // Strength bar
        const strength = getPasswordStrength(rules);
        const bar = document.getElementById('pw-strength-bar');
        const label = document.getElementById('pw-strength-label');
        const colorMap = {
            gray:  { bg: 'bg-gray-300',  text: 'text-gray-500' },
            red:   { bg: 'bg-red-500',   text: 'text-red-600' },
            amber: { bg: 'bg-amber-500', text: 'text-amber-600' },
            blue:  { bg: 'bg-blue-500',  text: 'text-blue-600' },
            green: { bg: 'bg-green-500', text: 'text-green-600' },
        };
        const cm = colorMap[strength.color];

        if (bar) {
            bar.className = `h-full rounded-full transition-all duration-300 ${cm.bg}`;
            bar.style.width = `${strength.width}%`;
        }
        if (label) {
            label.className = `text-[10px] font-bold ${cm.text}`;
            label.textContent = strength.label;
        }

        // Live check konfirmasi password
        const confirmInput = document.getElementById('f-new-password-confirm');
        const confirmHint = document.getElementById('confirm-hint');
        if (confirmInput && confirmHint) {
            const confirmValue = confirmInput.value;
            if (!confirmValue) {
                confirmHint.classList.add('hidden');
                confirmInput.classList.remove('border-red-500', 'border-green-500');
            } else if (confirmValue === password) {
                confirmHint.classList.remove('hidden');
                confirmHint.className = 'text-[10px] mt-1.5 flex items-center gap-1 text-green-600 font-semibold';
                confirmHint.innerHTML = `
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    Password cocok
                `;
                confirmInput.classList.remove('border-red-500');
                confirmInput.classList.add('border-green-500');
            } else {
                confirmHint.classList.remove('hidden');
                confirmHint.className = 'text-[10px] mt-1.5 flex items-center gap-1 text-red-600 font-semibold';
                confirmHint.innerHTML = `
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Password tidak cocok
                `;
                confirmInput.classList.remove('border-green-500');
                confirmInput.classList.add('border-red-500');
            }
        }
    }

    function setupPasswordListeners() {
        const newPwdInput = document.getElementById('f-new-password');
        const confirmInput = document.getElementById('f-new-password-confirm');

        if (newPwdInput) {
            newPwdInput.addEventListener('input', (e) => {
                updatePasswordUI(e.target.value);
            });
        }

        if (confirmInput) {
            confirmInput.addEventListener('input', () => {
                const pwd = document.getElementById('f-new-password');
                updatePasswordUI(pwd ? pwd.value : '');
            });
        }
    }

    // ============================================================
    // LOAD DATA PROFILE
    // ============================================================
    async function loadProfile() {
        const res = await apiCall('GET', DATA.routes.data);
        if (!res.ok) {
            toastError('Gagal memuat profile');
            return;
        }

        const u = res.data.user;

        // Update UI (pakai textContent — auto-escape, aman)
        document.getElementById('profile-name').textContent = u.name;
        document.getElementById('profile-username').textContent = '@' + u.username;
        document.getElementById('profile-role').textContent = u.role_label;
        document.getElementById('profile-unit').textContent = u.unit || '-';
        document.getElementById('profile-joined').textContent = u.created_at || '-';

        // Avatar — pakai createElement untuk aman
        const avatarEl = document.getElementById('avatar-preview');

        if (u.avatar) {
            const safePath = String(u.avatar).replace(/[^a-zA-Z0-9_\-./]/g, '');
            avatarEl.innerHTML = `<img src="/storage/${escapeHtml(safePath)}" class="w-full h-full object-cover" alt="Avatar" />`;
        } else {
            avatarEl.innerHTML = `<span id="avatar-initial">${escapeHtml(u.initial)}</span>`;
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
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${cls}">${escapeHtml(l.action)}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-800 truncate">${escapeHtml(l.module)} <span class="text-gray-400 font-mono text-[10px]">${escapeHtml(l.record_id)}</span></p>
                        <p class="text-[10px] text-gray-400">${escapeHtml(formatted)}</p>
                    </div>
                </li>`;
        }).join('');
    }

    // ============================================================
    // FORM PROFILE
    // ============================================================
    document.getElementById('form-profile').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('btn-save-profile');
        const originalHtml = btn.innerHTML;
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
        btn.innerHTML = originalHtml;
        if (window.lucide) lucide.createIcons();
    });

    // ============================================================
    // FORM PASSWORD — dengan Konfirmasi SweetAlert
    // ============================================================
    document.getElementById('form-password').addEventListener('submit', async (e) => {
        e.preventDefault();

        const currentPassword = document.getElementById('f-current-password').value;
        const newPassword = document.getElementById('f-new-password').value;
        const confirmPassword = document.getElementById('f-new-password-confirm').value;

        // Validasi client-side
        if (!currentPassword || !newPassword || !confirmPassword) {
            toastError('Semua field password wajib diisi');
            return;
        }

        if (newPassword !== confirmPassword) {
            toastError('Konfirmasi password tidak cocok');
            return;
        }

        // Cek rules password
        const rules = checkPasswordRules(newPassword);
        const failed = Object.entries(rules).filter(([_, v]) => !v).map(([k]) => k);
        if (failed.length > 0) {
            const labels = {
                length: 'Minimal 12 karakter',
                uppercase: 'Huruf besar (A-Z)',
                lowercase: 'Huruf kecil (a-z)',
                number: 'Angka (0-9)',
                symbol: 'Simbol (!@#$%^&*)',
            };
            toastError('Password belum memenuhi: ' + failed.map(f => labels[f]).join(', '));
            return;
        }

        // Konfirmasi via SweetAlert
        const confirm = await Swal.fire({
            title: 'Ubah Password?',
            html: `
                <div class="text-left">
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 flex items-start gap-2 text-xs text-amber-800 mb-3">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Setelah ganti password, Anda tetap login. Disarankan logout & login ulang untuk keamanan.</span>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ubah Password',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                actions: 'gap-2 mt-4',
            },
            buttonsStyling: false,
        });

        if (!confirm.isConfirmed) return;

        const btn = document.getElementById('btn-save-password');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Menyimpan...';
        if (window.lucide) lucide.createIcons();

        const data = {
            current_password: currentPassword,
            new_password: newPassword,
            new_password_confirmation: confirmPassword,
        };

        const res = await apiCall('PUT', DATA.routes.updatePassword, data);

        if (res.ok) {
            toastSuccess(res.data.message);
            document.getElementById('form-password').reset();
            updatePasswordUI('');
        } else {
            const errors = res.data.errors || {};
            const first = Object.values(errors)[0];
            toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
        }

        btn.disabled = false;
        btn.innerHTML = originalHtml;
        if (window.lucide) lucide.createIcons();
    });

    // ============================================================
    // AVATAR UPLOAD
    // ============================================================
    window.triggerAvatarUpload = function () {
        document.getElementById('avatar-input').click();
    };

    window.uploadAvatar = async function (input) {
        if (!input.files || !input.files[0]) return;

        const file = input.files[0];

        // Validasi client-side
        const MAX_SIZE = 2 * 1024 * 1024; // 2 MB
        const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];

        if (file.size > MAX_SIZE) {
            toastError('Ukuran file maksimal 2 MB');
            input.value = '';
            return;
        }

        if (!ALLOWED_TYPES.includes(file.type)) {
            toastError('Format file harus JPG, PNG, GIF, atau WEBP');
            input.value = '';
            return;
        }

        // Preview dulu
        const reader = new FileReader();
        reader.onload = (e) => {
            const avatarEl = document.getElementById('avatar-preview');
            avatarEl.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover" alt="Preview" />`;
        };
        reader.readAsDataURL(file);

        // Konfirmasi upload
        const confirm = await Swal.fire({
            title: 'Upload Avatar?',
            text: `File: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Upload',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-semibold px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                actions: 'gap-2 mt-4',
            },
            buttonsStyling: false,
        });

        if (!confirm.isConfirmed) {
            input.value = '';
            loadProfile(); // restore avatar lama
            return;
        }

        const formData = new FormData();
        formData.append('avatar', file);

        Swal.fire({
            title: 'Mengupload...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            customClass: { popup: 'rounded-2xl' },
        });

        const res = await apiCall('POST', DATA.routes.uploadAvatar, formData, true);

        Swal.close();

        if (res.ok) {
            toastSuccess(res.data.message);
            loadProfile();
        } else {
            const errors = res.data.errors || {};
            const first = Object.values(errors)[0];
            toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal upload avatar'));
            loadProfile(); // restore avatar lama
        }

        input.value = '';
    };

    // ============================================================
    // INIT
    // ============================================================
    document.addEventListener('DOMContentLoaded', () => {
        loadProfile();
        setupPasswordListeners();
    });
})();
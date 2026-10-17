// ============================================================
// SIKAP - User Management (with Admin Password Confirmation)
// Versi aman XSS + Live Password Rules Checklist
// ============================================================

(function () {
    const DATA = window.USER_DATA;
    let CURRENT_USER_ID = null;

    function buildRoute(template, id) {
        return template.replace('__ID__', id);
    }

    async function apiCall(method, url, body) {
        const opts = {
            method,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': DATA.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        };
        if (body) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        const res = await fetch(url, opts);
        const json = await res.json().catch(() => ({}));
        return { ok: res.ok, status: res.status, data: json };
    }

    // ============================================================
    // KONFIRMASI PASSWORD ADMIN
    // ============================================================
    async function confirmAdminPassword(message) {
        const { value: password } = await Swal.fire({
            title: '',
            html: `
                <div class="text-center mb-4">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-red-50 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Password</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">${escapeHtml(message)}</p>
                </div>
                <div class="text-left">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">
                        Password Anda
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input 
                            id="swal-confirm-password" 
                            type="password" 
                            placeholder="Masukkan password Anda"
                            class="w-full pl-10 pr-10 py-3 border-2 border-gray-200 rounded-lg text-sm focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-100 transition"
                            autocomplete="current-password"
                        />
                        <button 
                            type="button"
                            onclick="toggleSwalPassword()"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                        >
                            <svg id="swal-eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Aksi ini memerlukan verifikasi password untuk keamanan
                    </p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Konfirmasi',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl p-6',
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                actions: 'gap-2 mt-4',
            },
            buttonsStyling: false,
            didOpen: () => {
                const input = document.getElementById('swal-confirm-password');
                if (input) {
                    input.focus();
                    input.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') Swal.clickConfirm();
                    });
                }
            },
            preConfirm: () => {
                const input = document.getElementById('swal-confirm-password');
                const password = input ? input.value : '';
                if (!password) {
                    Swal.showValidationMessage('Password wajib diisi');
                    return false;
                }
                return password;
            }
        });

        if (!password) return false;

        Swal.fire({
            title: 'Memverifikasi...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            customClass: { popup: 'rounded-2xl' },
        });

        const res = await fetch('/confirm-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': DATA.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ password }),
        });

        const json = await res.json();
        Swal.close();

        if (json.ok) {
            toastSuccess('Konfirmasi berhasil');
            return true;
        } else {
            toastError(json.message || 'Password salah');
            return false;
        }
    }

    // ============================================================
    // TOGGLE SHOW/HIDE PASSWORD
    // ============================================================
    window.toggleSwalPassword = function() {
        const input = document.getElementById('swal-confirm-password');
        const icon = document.getElementById('swal-eye-icon');
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            `;
        } else {
            input.type = 'password';
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            `;
        }
    };

    window.toggleFormPassword = function (inputId, iconId) {
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

    // ⭐ Render satu rule item — pakai ruleId
    function renderRuleItem(ruleId, passed) {
        const ruleEl = document.getElementById(ruleId);
        if (!ruleEl) return;

        const iconEl = ruleEl.querySelector('[data-rule-icon]');
        const textEl = ruleEl.querySelector('[data-rule-text]');

        if (passed) {
            // Warna teks hijau
            ruleEl.classList.remove('text-gray-400', 'text-gray-500');
            ruleEl.classList.add('text-green-700', 'font-semibold');

            // Icon centang hijau
            if (iconEl) {
                iconEl.className = 'flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-green-500 text-white';
                iconEl.innerHTML = `
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                `;
            }
        } else {
            // Warna teks abu
            ruleEl.classList.remove('text-green-700', 'font-semibold');
            ruleEl.classList.add('text-gray-400');

            // Icon silang abu
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

        // Update setiap rule item — pakai ruleId
        renderRuleItem('rule-length',    rules.length);
        renderRuleItem('rule-uppercase', rules.uppercase);
        renderRuleItem('rule-lowercase', rules.lowercase);
        renderRuleItem('rule-number',    rules.number);
        renderRuleItem('rule-symbol',    rules.symbol);

        // Update strength bar
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
        const confirmInput = document.getElementById('f-password-confirmation');
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

    // ============================================================
    // ROLE BADGE
    // ============================================================
    function roleBadge(role, label) {
        const colors = {
            admin_keuangan: 'bg-blue-100 text-blue-700',
            keuangan:       'bg-emerald-100 text-emerald-700',
            pimpinan:       'bg-purple-100 text-purple-700',
            user:           'bg-gray-100 text-gray-700',
        };
        const cls = colors[role] || 'bg-gray-100 text-gray-700';
        return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${cls}">${escapeHtml(label)}</span>`;
    }

    // ============================================================
    // RENDER TABLE
    // ============================================================
    function renderTable(users) {
        const tbody = document.getElementById('user-table-body');

        if (!users || users.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-400">Belum ada user</td></tr>`;
            return;
        }

        tbody.innerHTML = users.map(u => {
            const isSelf = u.id === CURRENT_USER_ID;
            const statusBadge = u.active
                ? '<span class="text-green-700 bg-green-100 px-2 py-1 rounded-full text-xs font-semibold">Aktif</span>'
                : '<span class="text-gray-500 bg-gray-100 px-2 py-1 rounded-full text-xs font-semibold">Nonaktif</span>';

            const toggleIcon = u.active ? 'toggle-right' : 'toggle-left';
            const disableSelf = isSelf ? 'disabled opacity-50 cursor-not-allowed' : '';

            return `
                <tr class="hover:bg-blue-50/40 transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-700 to-blue-500 flex items-center justify-center text-white text-sm font-bold">
                                ${escapeHtml(u.initial)}
                            </div>
                            <span class="font-medium text-gray-800">${escapeHtml(u.name)}</span>
                            ${isSelf ? '<span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full font-bold ml-1">ANDA</span>' : ''}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 font-mono text-xs">${escapeHtml(u.username)}</td>
                    <td class="px-4 py-3">${roleBadge(u.role, u.role_label)}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">${escapeHtml(u.unit || '-')}</td>
                    <td class="px-4 py-3">${statusBadge}</td>
                    <td class="px-4 py-3 text-right">
                        <button type="button" onclick="toggleActive(${u.id})"
                            class="text-amber-600 hover:bg-amber-100 px-2 py-1 rounded transition ${disableSelf}"
                            title="Toggle Status" ${isSelf ? 'disabled' : ''}>
                            <i data-lucide="${toggleIcon}" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="editUser(${u.id})"
                            class="text-blue-700 hover:bg-blue-100 px-2 py-1 rounded transition ml-1" title="Edit">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="deleteUser(${u.id})"
                            class="text-red-600 hover:bg-red-100 px-2 py-1 rounded transition ml-1 ${disableSelf}"
                            title="Hapus" ${isSelf ? 'disabled' : ''}>
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>`;
        }).join('');

        if (window.lucide) lucide.createIcons();
    }

    async function reload() {
        const res = await apiCall('GET', DATA.routes.data);
        if (res.ok) {
            CURRENT_USER_ID = res.data.current_user_id;
            renderTable(res.data.users);
        } else {
            toastError('Gagal memuat data user');
        }
    }

    // ============================================================
    // BUILD FORM — dengan Live Checklist
    // ============================================================
    function buildForm(user) {
        const isEdit = !!user;
        const units = DATA.references.units || [];
        const currentUnit = user ? user.unit : '';

        // Rules checklist HTML — pakai data-attribute biar robust
        const rulesHtml = `
            <div class="mt-2.5 p-3 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-lg">
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

                <ul class="space-y-1.5">
                    <li id="rule-length" class="flex items-start gap-2 text-[11px] text-gray-400 transition-colors">
                        <span data-rule-icon class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-400">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </span>
                        <span data-rule-text>Minimal <b>12 karakter</b></span>
                    </li>
                    <li id="rule-uppercase" class="flex items-start gap-2 text-[11px] text-gray-400 transition-colors">
                        <span data-rule-icon class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-400">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </span>
                        <span data-rule-text>Huruf besar (<b>A-Z</b>)</span>
                    </li>
                    <li id="rule-lowercase" class="flex items-start gap-2 text-[11px] text-gray-400 transition-colors">
                        <span data-rule-icon class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-400">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </span>
                        <span data-rule-text>Huruf kecil (<b>a-z</b>)</span>
                    </li>
                    <li id="rule-number" class="flex items-start gap-2 text-[11px] text-gray-400 transition-colors">
                        <span data-rule-icon class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-400">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </span>
                        <span data-rule-text>Angka (<b>0-9</b>)</span>
                    </li>
                    <li id="rule-symbol" class="flex items-start gap-2 text-[11px] text-gray-400 transition-colors">
                        <span data-rule-icon class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-400">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </span>
                        <span data-rule-text>Simbol (<b>!@#$%^&*</b>)</span>
                    </li>
                </ul>
            </div>
        `;

        return `<div class="text-left space-y-3 text-sm mt-2">
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Nama Lengkap <span class="text-red-500">*</span></label>
                <input id="f-name" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    value="${user ? escapeHtml(user.name) : ''}" placeholder="Nama lengkap" autocomplete="off" />
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Username <span class="text-red-500">*</span></label>
                <input id="f-username" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono transition"
                    value="${user ? escapeHtml(user.username) : ''}" placeholder="username" autocomplete="off" />
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">
                    Password ${isEdit ? '<span class="text-gray-400 normal-case font-normal">(kosongkan jika tidak diubah)</span>' : '<span class="text-red-500">*</span>'}
                </label>
                <div class="relative">
                    <input id="f-password" type="password" class="w-full px-3 py-2.5 pr-10 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        value="" placeholder="Min 12 karakter" autocomplete="new-password" />
                    <button type="button" onclick="toggleFormPassword('f-password', 'eye-f-password')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i id="eye-f-password" data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">
                    Konfirmasi Password ${isEdit ? '' : '<span class="text-red-500">*</span>'}
                </label>
                <div class="relative">
                    <input id="f-password-confirmation" type="password" class="w-full px-3 py-2.5 pr-10 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        value="" placeholder="Ulangi password" autocomplete="new-password" />
                    <button type="button" onclick="toggleFormPassword('f-password-confirmation', 'eye-f-password-confirmation')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i id="eye-f-password-confirmation" data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
                <p id="confirm-hint" class="hidden"></p>
                ${rulesHtml}
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Role <span class="text-red-500">*</span></label>
                <select id="f-role" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition">
                    <option value="admin_keuangan" ${user && user.role === 'admin_keuangan' ? 'selected' : ''}>Admin Keuangan</option>
                    <option value="keuangan" ${user && user.role === 'keuangan' ? 'selected' : ''}>User Keuangan</option>
                    <option value="pimpinan" ${user && user.role === 'pimpinan' ? 'selected' : ''}>Pimpinan</option>
                    <option value="user" ${user && user.role === 'user' ? 'selected' : ''}>User</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Unit <span class="text-red-500">*</span></label>
                <select id="f-unit" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition">
                    <option value="">- Pilih Unit -</option>
                    ${units.map(u => `<option value="${escapeHtml(u)}" ${currentUnit === u ? 'selected' : ''}>${escapeHtml(u)}</option>`).join('')}
                </select>
            </div>
        </div>`;
    }

    // ============================================================
    // SETUP LIVE LISTENER — dipanggil setelah modal dibuka
    // ============================================================
    function setupPasswordListeners() {
        const passwordInput = document.getElementById('f-password');
        const confirmInput = document.getElementById('f-password-confirmation');

        if (passwordInput) {
            passwordInput.addEventListener('input', (e) => {
                updatePasswordUI(e.target.value);
            });
        }

        if (confirmInput) {
            confirmInput.addEventListener('input', () => {
                const pwd = document.getElementById('f-password');
                updatePasswordUI(pwd ? pwd.value : '');
            });
        }
    }

    function collectForm() {
        return {
            name: document.getElementById('f-name').value.trim(),
            username: document.getElementById('f-username').value.trim(),
            password: document.getElementById('f-password').value.trim(),
            password_confirmation: document.getElementById('f-password-confirmation').value.trim(),
            role: document.getElementById('f-role').value,
            unit: document.getElementById('f-unit').value,
        };
    }

    // ============================================================
    // PUBLIC ACTIONS
    // ============================================================
    window.openUserForm = async function () {
        await reload();
        Swal.fire({
            title: 'Tambah User',
            width: 580,
            html: buildForm(null),
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
            },
            buttonsStyling: false,
            didOpen: () => {
                if (window.lucide) lucide.createIcons();
                setupPasswordListeners();

                const firstInput = document.getElementById('f-name');
                if (firstInput) firstInput.focus();
            },
            preConfirm: () => {
                const data = collectForm();
                if (!data.name || !data.username || !data.password || !data.role || !data.unit) {
                    Swal.showValidationMessage('Semua field wajib diisi');
                    return false;
                }
                if (data.password !== data.password_confirmation) {
                    Swal.showValidationMessage('Konfirmasi password tidak cocok');
                    return false;
                }
                const rules = checkPasswordRules(data.password);
                const failed = Object.entries(rules).filter(([_, v]) => !v).map(([k]) => k);
                if (failed.length > 0) {
                    const labels = {
                        length: 'Minimal 12 karakter',
                        uppercase: 'Huruf besar (A-Z)',
                        lowercase: 'Huruf kecil (a-z)',
                        number: 'Angka (0-9)',
                        symbol: 'Simbol (!@#$%^&*)',
                    };
                    Swal.showValidationMessage('Password belum memenuhi: ' + failed.map(f => labels[f]).join(', '));
                    return false;
                }
                return data;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;
            const res = await apiCall('POST', DATA.routes.store, r.value);
            if (res.ok) {
                toastSuccess(res.data.message);
                reload();
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
            }
        });
    };

    window.editUser = async function (id) {
        const resData = await apiCall('GET', DATA.routes.data);
        if (!resData.ok) return;
        const user = resData.data.users.find(u => u.id === id);
        if (!user) return;

        Swal.fire({
            title: 'Edit User',
            width: 580,
            html: buildForm(user),
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
            },
            buttonsStyling: false,
            didOpen: () => {
                if (window.lucide) lucide.createIcons();
                setupPasswordListeners();

                const firstInput = document.getElementById('f-name');
                if (firstInput) firstInput.focus();
            },
            preConfirm: () => {
                const data = collectForm();
                if (!data.name || !data.username || !data.role || !data.unit) {
                    Swal.showValidationMessage('Nama, username, role, dan unit wajib diisi');
                    return false;
                }

                if (data.password) {
                    if (data.password !== data.password_confirmation) {
                        Swal.showValidationMessage('Konfirmasi password tidak cocok');
                        return false;
                    }
                    const rules = checkPasswordRules(data.password);
                    const failed = Object.entries(rules).filter(([_, v]) => !v).map(([k]) => k);
                    if (failed.length > 0) {
                        const labels = {
                            length: 'Minimal 12 karakter',
                            uppercase: 'Huruf besar (A-Z)',
                            lowercase: 'Huruf kecil (a-z)',
                            number: 'Angka (0-9)',
                            symbol: 'Simbol (!@#$%^&*)',
                        };
                        Swal.showValidationMessage('Password belum memenuhi: ' + failed.map(f => labels[f]).join(', '));
                        return false;
                    }
                } else {
                    delete data.password;
                    delete data.password_confirmation;
                }
                return data;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;
            const url = buildRoute(DATA.routes.update, id);
            const res = await apiCall('PUT', url, r.value);

            if (res.ok) {
                toastSuccess(res.data.message);
                reload();
                return;
            }

            if (res.status === 423 || res.data.require_confirmation) {
                const ok = await confirmAdminPassword(res.data.message || 'Perubahan password user lain memerlukan konfirmasi password Anda.');
                if (!ok) return;

                const retry = await apiCall('PUT', url, r.value);
                if (retry.ok) {
                    toastSuccess(retry.data.message);
                    reload();
                } else {
                    const errors = retry.data.errors || {};
                    const first = Object.values(errors)[0];
                    toastError(Array.isArray(first) ? first[0] : (retry.data.message || 'Gagal menyimpan'));
                }
                return;
            }

            const errors = res.data.errors || {};
            const first = Object.values(errors)[0];
            toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
        });
    };

    window.toggleActive = async function (id) {
        const url = buildRoute(DATA.routes.toggle, id);
        const res = await apiCall('PATCH', url);

        if (res.ok) {
            toastSuccess(res.data.message);
            reload();
            return;
        }

        if (res.status === 423 || res.data.require_confirmation) {
            const ok = await confirmAdminPassword(res.data.message || 'Aksi ini memerlukan konfirmasi password Anda.');
            if (!ok) return;

            const retry = await apiCall('PATCH', url);
            if (retry.ok) {
                toastSuccess(retry.data.message);
                reload();
            } else {
                toastError(retry.data.message || 'Gagal toggle status');
            }
            return;
        }

        toastError(res.data.message || 'Gagal toggle status');
    };

    window.deleteUser = async function (id) {
        const confirm = await Swal.fire({
            title: 'Hapus user ini?',
            text: 'User akan dihapus (soft delete).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
            },
            buttonsStyling: false,
        });

        if (!confirm.isConfirmed) return;

        const url = buildRoute(DATA.routes.destroy, id);
        const res = await apiCall('DELETE', url);

        if (res.ok) {
            toastSuccess(res.data.message);
            reload();
            return;
        }

        if (res.status === 423 || res.data.require_confirmation) {
            const ok = await confirmAdminPassword(res.data.message || 'Penghapusan user memerlukan konfirmasi password Anda.');
            if (!ok) return;

            const retry = await apiCall('DELETE', url);
            if (retry.ok) {
                toastSuccess(retry.data.message);
                reload();
            } else {
                toastError(retry.data.message || 'Gagal menghapus');
            }
            return;
        }

        toastError(res.data.message || 'Gagal menghapus');
    };

    // ============ INIT ============
    document.addEventListener('DOMContentLoaded', reload);
})();
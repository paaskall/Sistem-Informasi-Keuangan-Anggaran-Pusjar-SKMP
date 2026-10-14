// ============================================================
// SIKAP — App Helpers (Global)
// ============================================================

// SIKAP — Global Security Helpers

/**
 * Escape HTML entities untuk cegah XSS
 * Pakai untuk SEMUA data dari user/server sebelum masuk innerHTML
 */
window.escapeHtml = function (str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

/**
 * Escape untuk attribute value (sama dengan escapeHtml, bisa extend)
 */
window.escapeAttr = function (str) {
    return window.escapeHtml(str);
};

// ============ SWEETALERT THEME ============
const swalTheme = Swal.mixin({
    customClass: {
        popup: 'rounded-2xl p-8',
        title: 'text-xl font-bold text-slate-900',
        htmlContainer: 'text-sm text-gray-600',
        confirmButton: 'bg-brand-primary hover:bg-primary-dark text-white font-semibold px-5 py-2.5 rounded-lg mx-1 transition-blueprint',
        cancelButton: 'bg-white hover:bg-gray-50 text-brand-primary border border-brand-primary font-semibold px-5 py-2.5 rounded-lg mx-1 transition-blueprint',
        denyButton: 'bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-lg mx-1 transition-blueprint',
    },
    buttonsStyling: false,
});

// ============ TOAST (Blueprint) ============
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 6000,           // ⭐ 6 detik (Blueprint spec)
    timerProgressBar: true,
    width: '440px',        // ⭐ Blueprint spec
    padding: '16px',
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
    customClass: {
        popup: 'rounded-lg shadow-floating',
    },
});

// ⭐ Toast dengan error/warning — TIDAK auto-dismiss
function toastError(msg) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: msg,
        showConfirmButton: false,
        timer: undefined,  // ← tidak auto-close
        width: '440px',
        customClass: {
            popup: 'rounded-lg shadow-floating',
        },
    });
}

function toastWarning(msg) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'warning',
        title: msg,
        showConfirmButton: false,
        timer: undefined,
        width: '440px',
        customClass: {
            popup: 'rounded-lg shadow-floating',
        },
    });
}

function toastSuccess(msg) {
    Toast.fire({ icon: 'success', title: msg });
}

function toastInfo(msg) {
    Toast.fire({ icon: 'info', title: msg });
}

// ============ TOAST HELPERS ============
function toastSuccess(msg) {
    Toast.fire({ icon: 'success', title: msg });
}
function toastInfo(msg) {
    Toast.fire({ icon: 'info', title: msg });
}
function toastError(msg) {
    Toast.fire({ icon: 'error', title: msg });
}

// ============ LOGOUT ============
function logoutConfirm() {
    swalTheme.fire({
        title: 'Keluar dari sistem?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal'
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('logout-form').submit();
        }
    });
}

// ============ CONFIRM GENERIC ============
function confirmAction(title, text, onConfirm, opts = {}) {
    swalTheme.fire({
        title,
        text,
        icon: opts.icon || 'question',
        showCancelButton: true,
        confirmButtonText: opts.confirmText || 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        ...opts.extra
    }).then(r => {
        if (r.isConfirmed && typeof onConfirm === 'function') onConfirm();
    });
}

// ============ FORMAT ============
function formatRupiah(n) {
    if (typeof n === 'string') return n;
    if (n === null || n === undefined) return '-';
    return 'Rp' + Number(n).toLocaleString('id-ID');
}

function formatTanggal(s) {
    if (!s) return '-';
    const d = new Date(s);
    if (isNaN(d)) return s;
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function monthName(m) {
    return ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][m - 1] || '-';
}

function monthShort(m) {
    return ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'][m - 1] || '-';
}

// ============ NUMBER INPUT FORMAT ============
function formatNumberInput(value) {
    if (value === null || value === undefined || value === '') return '';
    const numeric = String(value).replace(/\D/g, '');
    if (numeric === '') return '';
    return Number(numeric).toLocaleString('id-ID');
}

function parseNumberInput(value) {
    if (value === null || value === undefined || value === '') return 0;
    const numeric = String(value).replace(/\D/g, '');
    return numeric === '' ? 0 : Number(numeric);
}

function attachNumberFormat(input) {
    if (!input) return;
    if (input.value && !input.value.includes('.')) {
        input.value = formatNumberInput(input.value);
    }
    if (input.dataset.formatBound === '1') return;
    input.dataset.formatBound = '1';

    input.addEventListener('input', (e) => {
        const cursorPos = e.target.selectionStart;
        const oldLength = e.target.value.length;
        e.target.value = formatNumberInput(e.target.value);
        const newLength = e.target.value.length;
        const newPos = cursorPos + (newLength - oldLength);
        try { e.target.setSelectionRange(newPos, newPos); } catch (_) {}
    });

    input.addEventListener('keypress', (e) => {
        if (!/[0-9]/.test(e.key) &&
            !['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Enter'].includes(e.key)) {
            e.preventDefault();
        }
    });

    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        const numeric = pasted.replace(/\D/g, '');
        e.target.value = formatNumberInput(numeric);
    });
}

// ============ BADGE (Blueprint) ============
function badge(text, type) {
    const map = {
        CREATE: 'bg-green-100 text-green-700',
        UPDATE: 'bg-blue-100 text-brand-primary',
        DELETE: 'bg-red-100 text-red-700',
        Selesai: 'bg-green-100 text-green-700',
        Proses: 'bg-blue-100 text-brand-primary',
        Draft: 'bg-gray-100 text-gray-700',
        Batal: 'bg-red-100 text-red-700',
        Aktif: 'bg-green-100 text-green-700',
        Nonaktif: 'bg-gray-100 text-gray-700',
    };
    const cls = map[type] || map[text] || 'bg-gray-100 text-gray-700';
    return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-caption font-semibold ${cls}">${text}</span>`;
}

// ============ STAT CARD (Blueprint) ============
function statCard({ label, value, icon, color = 'primary', onClick, subtitle }) {
    const colors = {
        primary: ['bg-blue-50', 'text-brand-primary', 'text-brand-primary'],
        secondary: ['bg-cyan-50', 'text-cyan-700', 'text-cyan-800'],
        accent: ['bg-amber-50', 'text-amber-700', 'text-amber-800'],
        success: ['bg-green-50', 'text-green-700', 'text-green-800'],
        warning: ['bg-amber-50', 'text-amber-700', 'text-amber-800'],
        error: ['bg-red-50', 'text-red-700', 'text-red-800'],
    }[color] || ['bg-blue-50', 'text-brand-primary', 'text-brand-primary'];

    const tag = onClick ? 'button' : 'div';
    const clickAttr = onClick ? `onclick="${onClick}" type="button"` : '';

    return `
        <${tag} ${clickAttr} class="bg-white rounded-lg border border-gray-100 shadow-soft p-6 transition-blueprint hover:shadow-medium group text-left w-full ${onClick ? 'cursor-pointer' : ''}">
            <div class="flex items-start justify-between gap-3 mb-3">
                <p class="text-caption text-gray-500 uppercase font-semibold tracking-wider">${label}</p>
                <div class="w-10 h-10 rounded-lg ${colors[0]} ${colors[1]} flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition">
                    <i data-lucide="${icon}" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-h5 font-bold ${colors[2]}">${value}</p>
            ${subtitle ? `<p class="text-caption text-muted mt-1">${subtitle}</p>` : ''}
        </${tag}>`;
}

// ============ GLOBAL FETCH INTERCEPTOR ============
// Handle 419 (CSRF expired) dan 401 (unauthorized) otomatis
(function() {
    const originalFetch = window.fetch;

    window.fetch = function(...args) {
        return originalFetch.apply(this, args).then(async (response) => {
            // 419: CSRF token expired → refresh halaman
            if (response.status === 419) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Sesi Berakhir',
                    text: 'Sesi Anda telah berakhir. Halaman akan dimuat ulang.',
                    timer: 2000,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'rounded-2xl',
                    },
                });
                window.location.reload();
                return Promise.reject(new Error('Session expired'));
            }

            // 401: Unauthorized → redirect ke login
            if (response.status === 401) {
                window.location.href = '/login';
                return Promise.reject(new Error('Unauthorized'));
            }

            return response;
        });
    };
})();

// ============ INIT LUCIDE ============
document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) lucide.createIcons();
});

// ============ RUPIAH HELPER ============
function rp(n) {
    if (n === null || n === undefined) return 'Rp 0';
    return 'Rp' + Number(n).toLocaleString('id-ID');
}

// ============ CONFIRM DELETE (Blueprint) ============
function confirmDelete(title, message, onConfirm) {
    Swal.fire({
        title: title || 'Hapus item ini?',
        html: `<p class="text-sm text-gray-600 text-left">${message || 'Tindakan ini tidak dapat dibatalkan.'}</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-2xl p-8',
            title: 'text-xl font-bold text-gray-900',
            htmlContainer: 'text-sm',
            confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-lg mx-1 transition-blueprint',
            cancelButton: 'bg-white hover:bg-gray-50 text-brand-primary border border-brand-primary font-semibold px-5 py-2.5 rounded-lg mx-1 transition-blueprint',
        },
        buttonsStyling: false,
        reverseButtons: true,
        focusCancel: true,
    }).then((r) => {
        if (r.isConfirmed && typeof onConfirm === 'function') onConfirm();
    });
}
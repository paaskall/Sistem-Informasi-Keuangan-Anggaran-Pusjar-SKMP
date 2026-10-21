// ============================================================
// SIKAP - Forgot Password (Reset via OTP)
// ============================================================

(function () {
    // ⬇️ DEFINISI TOAST (untuk jaga-jaga kalau app.js tidak ada)
    if (typeof window.toastError === 'undefined') {
        window.toastError = function (msg) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: msg,
                confirmButtonColor: '#0A669D',
                customClass: { popup: 'rounded-2xl' },
            });
        };
    }
    if (typeof window.toastSuccess === 'undefined') {
        window.toastSuccess = function (msg) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: msg,
                confirmButtonColor: '#0A669D',
                customClass: { popup: 'rounded-2xl' },
            });
        };
    }

    const DATA = window.FORGOT_PASSWORD_DATA;
    let currentEmail = '';

    async function apiCall(method, url, body) {
        const opts = {
            method,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': DATA.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        };
        if (body) opts.body = JSON.stringify(body);
        const res = await fetch(url, opts);
        const json = await res.json().catch(() => ({}));
        return { ok: res.ok, status: res.status, data: json };
    }

    // ============================================================
    // TOGGLE PASSWORD
    // ============================================================
    window.togglePassword = function (inputId, iconId) {
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
    // BACK TO EMAIL STEP
    // ============================================================
    window.backToEmail = function () {
        document.getElementById('step-reset').classList.add('hidden');
        document.getElementById('step-email').classList.remove('hidden');
        document.getElementById('form-reset').reset();
    };

    // ============================================================
    // STEP 1: KIRIM OTP
    // ============================================================
    document.getElementById('form-email').addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = document.getElementById('email').value.trim();
        if (!email) {
            toastError('Email wajib diisi');
            return;
        }

        const btn = document.getElementById('btn-send-otp');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Mengirim...';
        if (window.lucide) lucide.createIcons();

        const res = await apiCall('POST', DATA.routes.sendOtp, { email });

        btn.disabled = false;
        btn.innerHTML = originalHtml;
        if (window.lucide) lucide.createIcons();

        if (res.ok) {
            currentEmail = email;
            document.getElementById('display-email').textContent = email;
            document.getElementById('step-email').classList.add('hidden');
            document.getElementById('step-reset').classList.remove('hidden');

            toastSuccess(res.data.message || 'Kode OTP telah dikirim');
            if (window.lucide) lucide.createIcons();
        } else {
            const errors = res.data.errors || {};
            const first = Object.values(errors)[0];
            toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal mengirim OTP'));
        }
    });

    // ============================================================
    // STEP 2: RESET PASSWORD
    // ============================================================
    document.getElementById('form-reset').addEventListener('submit', async (e) => {
        e.preventDefault();

        const otp = document.getElementById('otp').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirmation').value;

        if (!otp || otp.length !== 6) {
            toastError('Kode OTP harus 6 digit');
            return;
        }
        if (!password || password.length < 12) {
            toastError('Password minimal 12 karakter');
            return;
        }
        if (password !== passwordConfirm) {
            toastError('Konfirmasi password tidak cocok');
            return;
        }

        const btn = document.getElementById('btn-reset');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Memproses...';
        if (window.lucide) lucide.createIcons();

        const res = await apiCall('POST', DATA.routes.reset, {
            email: currentEmail,
            otp,
            password,
            password_confirmation: passwordConfirm,
        });

        btn.disabled = false;
        btn.innerHTML = originalHtml;
        if (window.lucide) lucide.createIcons();

        if (res.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Password Direset',
                text: res.data.message || 'Password berhasil direset.',
                confirmButtonText: 'Login Sekarang',
                confirmButtonColor: '#0A669D',
                customClass: { popup: 'rounded-2xl' },
            }).then(() => {
                window.location.href = '/login';
            });
        } else {
            const errors = res.data.errors || {};
            const first = Object.values(errors)[0];
            toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal reset password'));
        }
    });

    // Auto-format OTP input (hanya angka)
    document.getElementById('otp').addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
    });
})();
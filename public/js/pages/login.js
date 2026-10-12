// ============================================================
// SIKAP - Login Page Scripts
// ============================================================

function togglePassword() {
    const inp = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');

    if (!inp) {
        console.warn('[Login] Element #password tidak ditemukan');
        return;
    }
    if (!icon) {
        console.warn('[Login] Element #eyeIcon tidak ditemukan');
        return;
    }

    if (inp.type === 'password') {
        inp.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        inp.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }

    if (window.lucide) lucide.createIcons();
}

function lupaPassword(e) {
    e.preventDefault();
    Swal.fire({
        icon: 'info',
        title: 'Lupa Password?',
        html: `<div class="text-sm text-left">
            <p class="mb-3 text-gray-600">Gunakan salah satu akun demo berikut:</p>
            <table class="w-full text-xs bg-gray-50 rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr><th class="text-left px-3 py-2">Username</th><th class="text-left px-3 py-2">Password</th></tr>
                </thead>
                <tbody>
                    <tr class="border-t border-gray-100"><td class="px-3 py-2 font-mono">admin</td><td class="px-3 py-2 font-mono">admin</td></tr>
                    <tr class="border-t border-gray-100"><td class="px-3 py-2 font-mono">keuangan</td><td class="px-3 py-2 font-mono">keuangan</td></tr>
                    <tr class="border-t border-gray-100"><td class="px-3 py-2 font-mono">budi</td><td class="px-3 py-2 font-mono">budi</td></tr>
                    <tr class="border-t border-gray-100"><td class="px-3 py-2 font-mono">pimpinan</td><td class="px-3 py-2 font-mono">pimpinan</td></tr>
                </tbody>
            </table>
        </div>`,
        confirmButtonText: 'Mengerti'
    });
}

function quickLogin(role) {
    const usernameMap = {
        admin: 'admin',
        keuangan: 'keuangan',
        pimpinan: 'pimpinan'
    };
    const passwordMap = {
        admin: 'admin',
        keuangan: 'keuangan',
        pimpinan: 'pimpinan'
    };

    const username = usernameMap[role];
    const password = passwordMap[role];

    if (!username) return;

    const usernameEl = document.getElementById('username');
    const passwordEl = document.getElementById('password');

    if (!usernameEl || !passwordEl) {
        console.warn('[Login] Element #username atau #password tidak ditemukan');
        return;
    }

    usernameEl.value = username;
    passwordEl.value = password;

    // Submit form
    document.querySelector('form').submit();
}
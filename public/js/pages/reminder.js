// ============================================================
// SIKAP - Pengingat Realisasi & PNBP (Versi Aman Anti-Hang)
// ============================================================

(function () {
    'use strict';

    // Guard: Jangan jalan kalau tidak ada elemen reminder di halaman
    const hasTopbar = !!document.getElementById('topbar-reminder');
    const hasWidget = !!document.getElementById('reminder-widget');
    if (!hasTopbar && !hasWidget) return;

    let isLoaded = false;
    let remindersData = null;

    function rp(n) {
        if (n === null || n === undefined) return 'Rp 0';
        return 'Rp' + Number(n).toLocaleString('id-ID');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function fetchReminders() {
        if (isLoaded) return remindersData;
        if (!window.REMINDER_DATA?.routes?.reminders) {
            console.warn('[Reminder] REMINDER_DATA tidak tersedia');
            return null;
        }
        try {
            const res = await fetch(window.REMINDER_DATA.routes.reminders, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            if (!res.ok) return null;
            const json = await res.json();
            isLoaded = true;
            return json.reminders || json;
        } catch (e) {
            console.error('[Reminder] Fetch error:', e);
            return null;
        }
    }

    // ============================================================
    // WIDGET DASHBOARD
    // ============================================================
    function renderWidget(data) {
        const widget = document.getElementById('reminder-widget');
        if (!widget) return;

        if (!data || data.total === 0) {
            widget.classList.add('hidden');
            return;
        }

        widget.classList.remove('hidden');

        const subtitle = document.getElementById('reminder-subtitle');
        const total = document.getElementById('reminder-total');
        const body = document.getElementById('reminder-body');

        if (subtitle) {
            const namaBulan = data.periode?.bulan_nama || 'Bulan Ini';
            subtitle.textContent = data.is_late_month
                ? `Akhir bulan ${namaBulan} - segera lengkapi`
                : `${namaBulan} ${data.periode?.tahun || ''}`;
        }
        if (total) total.textContent = data.total;

        const routes = window.REMINDER_DATA?.routes || {};
        let html = '';

        if (data.realisasi && data.realisasi.count > 0) {
            html += `
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-red-700"></i>
                            </div>
                            <p class="text-xs font-bold text-red-800">Realisasi Belum Lengkap</p>
                        </div>
                        <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-0.5 rounded-full">
                            ${data.realisasi.count} item
                        </span>
                    </div>
                    <div class="space-y-1.5">
                        ${data.realisasi.items.slice(0, 3).map(item => `
                            <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-red-50/50 border border-red-100">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[11px] font-semibold text-gray-800 truncate">${escapeHtml(item.nama)}</p>
                                    <p class="text-[10px] text-gray-500">${rp(item.realisasi)} / ${rp(item.target)}</p>
                                </div>
                                <span class="text-[10px] font-bold text-red-700 whitespace-nowrap">${item.capaian}%</span>
                            </div>
                        `).join('')}
                        ${data.realisasi.count > 3 ? `<p class="text-[10px] text-red-600 text-center pt-1 font-semibold">+ ${data.realisasi.count - 3} komponen lainnya</p>` : ''}
                    </div>
                    <a href="${routes.realisasiUrl || '#'}" class="mt-2 inline-flex items-center gap-1 text-[11px] font-bold text-red-700 hover:underline">
                        Input Sekarang <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            `;
        }

        if (data.pnbp && data.pnbp.count > 0) {
            html += `
                <div class="${data.realisasi && data.realisasi.count > 0 ? 'pt-3 border-t border-gray-100' : ''}">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 text-amber-700"></i>
                            </div>
                            <p class="text-xs font-bold text-amber-800">PNBP Belum Capai Target</p>
                        </div>
                        <span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">
                            ${data.pnbp.count} kategori
                        </span>
                    </div>
                    <div class="space-y-1.5">
                        ${data.pnbp.items.slice(0, 3).map(item => `
                            <div class="flex items-center justify-between gap-2 p-2 rounded-lg bg-amber-50/50 border border-amber-100">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[11px] font-semibold text-gray-800 truncate">${escapeHtml(item.nama)}</p>
                                    <p class="text-[10px] text-gray-500">${rp(item.realisasi)} / ${rp(item.target)}</p>
                                </div>
                                <span class="text-[10px] font-bold text-amber-700 whitespace-nowrap">${item.capaian}%</span>
                            </div>
                        `).join('')}
                        ${data.pnbp.count > 3 ? `<p class="text-[10px] text-amber-600 text-center pt-1 font-semibold">+ ${data.pnbp.count - 3} kategori lainnya</p>` : ''}
                    </div>
                    <a href="${routes.pnbpUrl || '#'}" class="mt-2 inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 hover:underline">
                        Kelola PNBP <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            `;
        }

        if (body) body.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // BADGE TOPBAR
    // ============================================================
    function renderTopbarBadge(data) {
        const badge = document.getElementById('topbar-reminder-badge');
        const dropdownBody = document.getElementById('reminder-dropdown-body');
        if (!badge) return;

        const routes = window.REMINDER_DATA?.routes || {};

        if (!data || data.total === 0) {
            badge.classList.add('hidden');
            if (dropdownBody) {
                dropdownBody.innerHTML = `
                    <div class="text-center py-6">
                        <i data-lucide="check-circle-2" class="w-10 h-10 mx-auto text-green-400 mb-2"></i>
                        <p class="text-xs text-gray-600 font-semibold">Semua beres!</p>
                        <p class="text-[10px] text-gray-400 mt-1">Tidak ada pengingat</p>
                    </div>`;
                if (window.lucide) lucide.createIcons();
            }
            return;
        }

        badge.classList.remove('hidden');
        badge.textContent = data.total;

        if (!dropdownBody) return;

        let html = '';
        if (data.realisasi && data.realisasi.count > 0) {
            html += `
                <a href="${routes.realisasiUrl || '#'}" class="flex items-start gap-2.5 p-2.5 rounded-lg hover:bg-red-50 transition">
                    <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-700"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-gray-800">${data.realisasi.count} komponen realisasi</p>
                        <p class="text-[10px] text-gray-500">Belum mencapai 100% target bulan ini</p>
                    </div>
                </a>
            `;
        }
        if (data.pnbp && data.pnbp.count > 0) {
            html += `
                <a href="${routes.pnbpUrl || '#'}" class="flex items-start gap-2.5 p-2.5 rounded-lg hover:bg-amber-50 transition">
                    <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="receipt" class="w-4 h-4 text-amber-700"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-gray-800">${data.pnbp.count} kategori PNBP</p>
                        <p class="text-[10px] text-gray-500">Belum mencapai target tahunan</p>
                    </div>
                </a>
            `;
        }

        dropdownBody.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    window.toggleReminderDropdown = function () {
        const dropdown = document.getElementById('reminder-dropdown');
        if (!dropdown) return;
        dropdown.classList.toggle('hidden');
        if (window.lucide) lucide.createIcons();
    };

    document.addEventListener('click', (e) => {
        const container = document.getElementById('topbar-reminder');
        const dropdown = document.getElementById('reminder-dropdown');
        if (!container || !dropdown) return;
        if (!container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    document.addEventListener('DOMContentLoaded', async () => {
        setTimeout(async () => {
            remindersData = await fetchReminders();
            if (remindersData) {
                renderWidget(remindersData);
                renderTopbarBadge(remindersData);
            }
        }, 500);
    });
})();
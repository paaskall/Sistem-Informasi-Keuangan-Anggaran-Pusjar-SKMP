// ============================================================
// SIKAP — Target Viewer (Read-Only)
// Versi defensif: tunggu window.TARGET_DATA siap
// ============================================================

(function () {
    'use strict';

    console.log('[Target Viewer] Script loaded.');

    // ============ FUNGSI INIT ============
    function initApp() {
        const DATA = window.TARGET_DATA;

        if (!DATA) {
            console.error('[Target Viewer] window.TARGET_DATA MASIH KOSONG setelah tunggu.');
            console.error('Cek: 1) View sudah define window.TARGET_DATA? 2) Nama variabel benar? 3) Ada di @push scripts?');
            return;
        }

        console.log('[Target Viewer] Data OK:', DATA);

        let currentTahun = DATA.initialTahun || new Date().getFullYear();
        let targets = [];
        let references = {};
        const openDetails = new Set();

        // ============ HELPERS ============
        function rp(n) {
            if (!n && n !== 0) return 'Rp 0';
            return 'Rp' + Number(n).toLocaleString('id-ID');
        }

        function monthName(m) {
            return ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][m - 1] || '-';
        }

        // ============ LOAD DATA ============
        async function loadData() {
            if (!DATA.routes || !DATA.routes.data) {
                console.error('[Target Viewer] DATA.routes.data tidak ada!');
                return;
            }

            const url = DATA.routes.data + '?tahun=' + currentTahun;

            try {
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const json = await res.json();

                if (!json.ok) {
                    console.error('[Target Viewer] Response error:', json);
                    toastError('Gagal memuat data target');
                    return;
                }

                targets = json.targets || [];
                references = json.references || {};

                renderStats(json.total_tahunan, targets.length);
                renderTable();
            } catch (err) {
                console.error('[Target Viewer] Fetch error:', err);
                toastError('Gagal koneksi ke server');
            }
        }

        // ============ RENDER STATS ============
        function renderStats(totalTahunan, jumlahBulan) {
            const container = document.getElementById('stat-cards');
            if (!container) return;

            const rata = jumlahBulan > 0 ? Math.round(totalTahunan / jumlahBulan) : 0;

            if (typeof statCard === 'function') {
                container.innerHTML = `
                    ${statCard({ label: 'Total Target ' + currentTahun, value: rp(totalTahunan), icon: 'target', color: 'blue' })}
                    ${statCard({ label: 'Jumlah Bulan Terdaftar', value: jumlahBulan + ' bulan', icon: 'calendar', color: 'emerald' })}
                    ${statCard({ label: 'Rata-rata per Bulan', value: rp(rata), icon: 'trending-up', color: 'amber' })}
                `;
            } else {
                container.innerHTML = `<div class="col-span-3 text-center text-muted text-label py-6">Total: ${rp(totalTahunan)}</div>`;
            }

            const infoEl = document.getElementById('filter-info');
            if (infoEl) {
                infoEl.innerHTML = `${jumlahBulan} bulan terdaftar · Total: <b class="text-blue-700">${rp(totalTahunan)}</b>`;
            }

            if (window.lucide) lucide.createIcons();
        }

        // ============ RENDER TABLE ============
        function renderTable() {
            const tbody = document.getElementById('target-tbody');
            const tfoot = document.getElementById('target-tfoot');
            if (!tbody) return;

            if (targets.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-gray-400">Belum ada target untuk tahun ${currentTahun}</td></tr>`;
                if (tfoot) tfoot.innerHTML = '';
                return;
            }

            tbody.innerHTML = targets.map((t, i) => {
                const isOpen = openDetails.has(t.id);
                const detailCount = t.details ? t.details.length : 0;

                const grouped = {};
                (t.details || []).forEach(d => {
                    const progId = d.program_id;
                    if (!grouped[progId]) grouped[progId] = { program: d.program, subs: {}, total: 0 };
                    const subId = d.sub_kegiatan_id || 0;
                    if (!grouped[progId].subs[subId]) grouped[progId].subs[subId] = { sub_kegiatan: d.sub_kegiatan, komponens: [], total: 0 };
                    grouped[progId].subs[subId].komponens.push(d);
                    grouped[progId].subs[subId].total += Number(d.nominal) || 0;
                    grouped[progId].total += Number(d.nominal) || 0;
                });

                const programBlocksHtml = Object.values(grouped).map(g => {
                    const prog = g.program;
                    const progTotal = g.total;
                    const progPct = t.total > 0 ? ((progTotal / t.total) * 100).toFixed(1) : 0;

                    const subBlocksHtml = Object.values(g.subs).map(s => {
                        const sub = s.sub_kegiatan;
                        const subTotal = s.total;
                        const subPct = t.total > 0 ? ((subTotal / t.total) * 100).toFixed(1) : 0;

                        const komponensHtml = s.komponens.map(d => {
                            const kom = d.komponen_belanja;
                            const pct = t.total > 0 ? ((Number(d.nominal) / t.total) * 100).toFixed(1) : 0;
                            return `
                                <tr class="border-b border-gray-100 last:border-0">
                                    <td class="py-2.5 pl-14 pr-3 text-left">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                                <i data-lucide="receipt" class="w-3 h-3"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-medium text-gray-800 truncate">${kom ? kom.nama : '-'}</p>
                                                <p class="text-[9px] text-gray-400 font-mono">${kom ? kom.kode : '-'}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-xs font-semibold text-emerald-700 whitespace-nowrap">${rp(d.nominal)}</td>
                                    <td class="py-2.5 pl-3 pr-4 text-right text-[10px] text-gray-500 w-16">${pct}%</td>
                                </tr>`;
                        }).join('');

                        return `
                            <div class="mb-3 last:mb-0">
                                <div class="flex items-center gap-2 px-3 py-2 bg-indigo-50/70 rounded-t-lg border border-indigo-100">
                                    <div class="w-6 h-6 rounded-md bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="git-branch" class="w-3 h-3"></i>
                                    </div>
                                    <span class="text-[10px] font-mono font-bold text-indigo-700 bg-white px-1.5 py-0.5 rounded">${sub ? sub.kode : '-'}</span>
                                    <span class="text-xs font-semibold text-gray-800 flex-1 truncate">${sub ? sub.nama : 'Tanpa Sub-Kegiatan'}</span>
                                    <span class="text-xs font-bold text-indigo-700 whitespace-nowrap">${rp(subTotal)}</span>
                                    <span class="text-[10px] text-gray-500 w-12 text-right">${subPct}%</span>
                                </div>
                                <div class="bg-white border border-t-0 border-indigo-100 rounded-b-lg overflow-hidden">
                                    <table class="w-full"><tbody>${komponensHtml}</tbody></table>
                                </div>
                            </div>`;
                    }).join('');

                    return `
                        <div class="mb-4 last:mb-0">
                            <div class="flex items-center gap-2.5 px-3 py-2.5 bg-blue-100/80 rounded-lg border border-blue-200 mb-2">
                                <div class="w-7 h-7 rounded-md bg-blue-700 text-white flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="folder-kanban" class="w-3.5 h-3.5"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-mono font-bold text-blue-700 bg-white px-1.5 py-0.5 rounded">${prog ? prog.kode : '???'}</span>
                                        <span class="text-xs font-bold text-blue-900 truncate">${prog ? prog.nama : 'Tidak diketahui'}</span>
                                    </div>
                                </div>
                                <span class="text-sm font-bold text-blue-800 whitespace-nowrap">${rp(progTotal)}</span>
                                <span class="text-[10px] text-blue-700 w-12 text-right">${progPct}%</span>
                            </div>
                            <div class="pl-3">${subBlocksHtml}</div>
                        </div>`;
                }).join('');

                return `
                    <tr class="hover:bg-blue-50/40 transition cursor-pointer" data-target-id="${t.id}" onclick="toggleDetail(${t.id})">
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 text-xs font-bold">${i + 1}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <span class="font-semibold text-gray-800">${monthName(t.bulan)}</span>
                                <i data-lucide="chevron-down" class="chevron-icon w-3.5 h-3.5 text-gray-400 flex-shrink-0 ${isOpen ? 'rotate' : ''}"></i>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center gap-1.5 text-xs bg-purple-50 text-purple-700 font-semibold px-3 py-1.5 rounded-full">
                                <i data-lucide="layers" class="w-3 h-3"></i> ${detailCount} item
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="font-bold text-blue-700 whitespace-nowrap">${rp(t.total)}</span>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <button type="button" onclick="event.stopPropagation(); toggleDetail(${t.id})"
                                class="text-blue-600 hover:bg-blue-100 p-2 rounded-lg transition" title="Lihat Detail">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </td>
                    </tr>

                    <tr data-expand-for="${t.id}" class="${isOpen ? '' : 'hidden'} bg-gradient-to-r from-blue-50/40 to-indigo-50/30">
                        <td colspan="5" class="p-0">
                            <div class="detail-expand ${isOpen ? 'open' : ''}">
                                <div class="px-6 py-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="layers" class="w-4 h-4 text-blue-700"></i>
                                            <span class="text-xs font-bold text-blue-900 uppercase tracking-wide">
                                                Rincian Target - ${monthName(t.bulan)} ${t.tahun}
                                            </span>
                                        </div>
                                        <span class="text-xs text-gray-500">Total: ${rp(t.total)}</span>
                                    </div>
                                    ${programBlocksHtml || '<p class="text-sm text-gray-400 text-center py-4">Tidak ada komponen</p>'}
                                </div>
                            </div>
                        </td>
                    </tr>`;
            }).join('');

            const totalTahunan = targets.reduce((s, t) => s + Number(t.total || 0), 0);
            if (tfoot) {
                tfoot.innerHTML = `
                    <tr class="bg-gradient-to-r from-blue-50 to-indigo-50 border-t-2 border-blue-100">
                        <td colspan="3" class="px-5 py-4 text-center font-bold text-blue-900 text-sm">
                            TOTAL TAHUNAN ${currentTahun}
                        </td>
                        <td class="px-5 py-4 text-center font-bold text-blue-800 text-base whitespace-nowrap">
                            ${rp(totalTahunan)}
                        </td>
                        <td></td>
                    </tr>`;
            }

            if (window.lucide) lucide.createIcons();
        }

        // ============ TOGGLE DETAIL ============
        window.toggleDetail = function (targetId) {
            const row = document.querySelector(`tr[data-target-id="${targetId}"]`);
            const expandRow = document.querySelector(`tr[data-expand-for="${targetId}"]`);
            if (!row || !expandRow) return;

            const container = expandRow.querySelector('.detail-expand');
            const chevron = row.querySelector('.chevron-icon');
            const isOpen = openDetails.has(targetId);

            if (isOpen) {
                openDetails.delete(targetId);
                if (container) container.classList.remove('open');
                if (chevron) chevron.classList.remove('rotate');
                setTimeout(() => {
                    if (!openDetails.has(targetId)) expandRow.classList.add('hidden');
                }, 350);
            } else {
                openDetails.add(targetId);
                expandRow.classList.remove('hidden');
                if (container) {
                    void container.offsetHeight;
                    container.classList.add('open');
                }
                if (chevron) chevron.classList.add('rotate');
            }
        };

        // ============ CHANGE TAHUN ============
        window.changeTahun = function (y) {
            currentTahun = Number(y);
            openDetails.clear();
            const url = new URL(window.location);
            url.searchParams.set('tahun', currentTahun);
            window.history.pushState({}, '', url);
            loadData();
        };

        // ============ JALANKAN ============
        console.log('[Target Viewer] Init app dengan tahun', currentTahun);
        loadData();
    }

    // ============ TUNGGU DOM + DATA ============
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            // Kasih delay 100ms supaya script inline lain sempat jalan
            setTimeout(initApp, 100);
        });
    } else {
        setTimeout(initApp, 100);
    }
})();
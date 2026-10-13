// ============================================================
// SIKAP - Realisasi Viewer (Read-Only)
// ============================================================

(function () {
    const DATA = window.REALISASI_DATA;
    let currentTahun = DATA.initialTahun;
    let realisasis = [];
    let totalTarget = 0;
    let totalRealisasi = 0;
    const openDetails = new Set();

    function rp(n) {
        if (n === null || n === undefined) return 'Rp 0';
        return 'Rp' + Number(n).toLocaleString('id-ID');
    }

    function monthName(m) {
        return ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][m - 1] || '-';
    }

    function formatTanggal(s) {
        if (!s) return '-';
        const d = new Date(s);
        if (isNaN(d)) return s;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    async function loadData() {
        const url = DATA.routes.data + '?tahun=' + currentTahun;
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const json = await res.json();

        if (!json.ok) {
            toastError('Gagal memuat data realisasi');
            return;
        }

        realisasis = json.realisasis || [];
        totalTarget = json.total_target || 0;
        totalRealisasi = json.total_realisasi || 0;

        renderStats();
        renderTable();
    }

    function renderStats() {
        const capaian = totalTarget > 0 ? Math.round((totalRealisasi / totalTarget) * 100) : 0;
        document.getElementById('stat-cards').innerHTML = `
            ${statCard({ label: 'Total Target ' + currentTahun, value: rp(totalTarget), icon: 'target', color: 'blue' })}
            ${statCard({ label: 'Total Realisasi', value: rp(totalRealisasi), icon: 'trending-up', color: 'emerald' })}
            ${statCard({ label: 'Capaian', value: capaian + '%', icon: 'percent', color: 'amber' })}
        `;
        document.getElementById('filter-info').innerHTML =
            `${realisasis.length} transaksi · Total: <b class="text-emerald-700">${rp(totalRealisasi)}</b>`;
        if (window.lucide) lucide.createIcons();
    }

    function renderTable() {
        const tbody = document.getElementById('realisasi-tbody');

        if (realisasis.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-10 text-gray-400">
                        <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                        <p class="text-sm">Belum ada realisasi untuk tahun ${currentTahun}</p>
                    </td>
                </tr>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        tbody.innerHTML = realisasis.map((r, i) => {
            const isOpen = openDetails.has(r.id);
            const detailCount = r.details.length;
            const kendalaCount = (r.kendalas || []).length;
            const cap = r.capaian;
            const capColor = cap >= 100 ? 'text-green-700 bg-green-50'
                            : cap >= 70 ? 'text-blue-700 bg-blue-50'
                            : cap >= 40 ? 'text-amber-700 bg-amber-50'
                            : 'text-red-700 bg-red-50';

            const grouped = {};
            r.details.forEach(d => {
                const progId = d.program_id;
                if (!grouped[progId]) grouped[progId] = { program: d.program, subs: {}, total: 0 };
                const subId = d.sub_kegiatan_id || 0;
                if (!grouped[progId].subs[subId]) grouped[progId].subs[subId] = { sub_kegiatan: d.sub_kegiatan, komponens: [], total: 0 };
                grouped[progId].subs[subId].komponens.push(d);
                grouped[progId].subs[subId].total += d.nominal;
                grouped[progId].total += d.nominal;
            });

            const programBlocksHtml = Object.values(grouped).map(g => {
                const prog = g.program;
                const progTotal = g.total;
                const progPct = r.nominal > 0 ? ((progTotal / r.nominal) * 100).toFixed(1) : 0;

                const subBlocksHtml = Object.values(g.subs).map(s => {
                    const sub = s.sub_kegiatan;
                    const subTotal = s.total;
                    const subPct = r.nominal > 0 ? ((subTotal / r.nominal) * 100).toFixed(1) : 0;

                    const komponensHtml = s.komponens.map(d => {
                        const kom = d.komponen_belanja;
                        const pct = r.nominal > 0 ? ((d.nominal / r.nominal) * 100).toFixed(1) : 0;
                        return `
                            <tr class="border-b border-gray-100 last:border-0">
                                <td class="py-2.5 pl-14 pr-3 text-left">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="receipt" class="w-3 h-3"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-gray-800 truncate">${kom ? kom.nama : '-'}</p>
                                            <p class="text-[9px] text-gray-400">${kom ? kom.kode : '-'}</p>
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

            const kendalaBlockHtml = kendalaCount > 0 ? `
                <div class="mt-4 border-t border-amber-200 pt-4">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                        <span class="text-xs font-bold text-amber-900 uppercase tracking-wide">
                            Kendala Terkait (${kendalaCount})
                        </span>
                    </div>
                    <div class="space-y-2">
                        ${(r.kendalas || []).map(k => {
                            const kStatusColor = k.status === 'Selesai' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700';
                            return `
                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                    <div class="flex items-start justify-between gap-2 mb-1.5">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-[10px] font-bold bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                                                ${k.kategori ? k.kategori.nama : '-'}
                                            </span>
                                            <span class="text-[10px] font-bold ${kStatusColor} px-2 py-0.5 rounded-full">
                                                ${k.status}
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-gray-500 whitespace-nowrap">${formatTanggal(k.tanggal)}</span>
                                    </div>
                                    <p class="text-xs text-gray-800">${k.deskripsi}</p>
                                </div>`;
                        }).join('')}
                    </div>
                </div>
            ` : '';

            return `
                <tr class="hover:bg-emerald-50/40 transition cursor-pointer" data-realisasi-id="${r.id}" onclick="toggleDetail(${r.id})">
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 text-xs font-bold">${i + 1}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <span class="font-semibold text-gray-800">${r.bulan_nama}</span>
                            <i data-lucide="chevron-down" class="chevron-icon w-3.5 h-3.5 text-gray-400 flex-shrink-0 ${isOpen ? 'rotate' : ''}"></i>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-center text-gray-600 text-xs whitespace-nowrap">${formatTanggal(r.tanggal)}</td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <span class="inline-flex items-center gap-1.5 text-xs bg-purple-50 text-purple-700 font-semibold px-3 py-1.5 rounded-full">
                                <i data-lucide="layers" class="w-3 h-3"></i> ${detailCount} item
                            </span>
                            ${kendalaCount > 0 ? `
                                <span class="inline-flex items-center gap-1.5 text-[10px] bg-amber-100 text-amber-700 font-bold px-2 py-0.5 rounded-full">
                                    <i data-lucide="alert-triangle" class="w-2.5 h-2.5"></i> ${kendalaCount} kendala
                                </span>
                            ` : ''}
                        </div>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div>
                            <span class="font-bold text-emerald-700 whitespace-nowrap">${rp(r.nominal)}</span>
                            <p class="text-[10px] text-gray-500 mt-0.5">Capaian: ${cap}%</p>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <button type="button" onclick="event.stopPropagation(); toggleDetail(${r.id})"
                            class="text-blue-600 hover:bg-blue-100 p-2 rounded-lg transition" title="Lihat Detail">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>

                <tr data-expand-for="${r.id}" class="${isOpen ? '' : 'hidden'} bg-gradient-to-r from-emerald-50/40 to-blue-50/30">
                    <td colspan="6" class="p-0">
                        <div class="detail-expand ${isOpen ? 'open' : ''}">
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="layers" class="w-4 h-4 text-emerald-700"></i>
                                        <span class="text-xs font-bold text-emerald-900 uppercase tracking-wide">
                                            Rincian Realisasi - ${r.bulan_nama} ${r.tahun}
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500">Total: ${rp(r.nominal)}</span>
                                </div>
                                ${programBlocksHtml || '<p class="text-sm text-gray-400 text-center py-4">Tidak ada komponen</p>'}
                                ${r.keterangan ? `
                                    <div class="mt-4 bg-blue-50 border-l-4 border-blue-500 p-3 rounded-r-lg text-xs text-blue-900 flex items-start gap-2">
                                        <i data-lucide="info" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5"></i>
                                        <span><b>Keterangan:</b> ${r.keterangan}</span>
                                    </div>
                                ` : ''}
                                ${kendalaBlockHtml}
                            </div>
                        </div>
                    </td>
                </tr>`;
        }).join('');

        if (window.lucide) lucide.createIcons();
    }

    window.toggleDetail = function (realisasiId) {
        const row = document.querySelector(`tr[data-realisasi-id="${realisasiId}"]`);
        const expandRow = document.querySelector(`tr[data-expand-for="${realisasiId}"]`);
        if (!row || !expandRow) return;

        const container = expandRow.querySelector('.detail-expand');
        const chevron = row.querySelector('.chevron-icon');
        const isOpen = openDetails.has(realisasiId);

        if (isOpen) {
            openDetails.delete(realisasiId);
            container.classList.remove('open');
            if (chevron) chevron.classList.remove('rotate');
            setTimeout(() => {
                if (!openDetails.has(realisasiId)) expandRow.classList.add('hidden');
            }, 350);
        } else {
            openDetails.add(realisasiId);
            expandRow.classList.remove('hidden');
            void container.offsetHeight;
            container.classList.add('open');
            if (chevron) chevron.classList.add('rotate');
        }
    };

    window.changeTahun = function (y) {
        currentTahun = Number(y);
        openDetails.clear();
        const url = new URL(window.location);
        url.searchParams.set('tahun', currentTahun);
        window.history.pushState({}, '', url);
        loadData();
    };

    document.addEventListener('DOMContentLoaded', loadData);
})();

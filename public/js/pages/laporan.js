// SIKAP - Laporan (Pimpinan)
// Versi aman XSS

(function () {
    const DATA = window.LAPORAN_DATA;
    let currentTahun = DATA.initialTahun;
    let bulanData = [];
    const openMonths = new Set();

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

    function rp(n) {
        if (n === null || n === undefined) return 'Rp 0';
        return 'Rp' + Number(n).toLocaleString('id-ID');
    }

    async function loadData() {
        const url = DATA.routes.data + '?tahun=' + currentTahun;
        const res = await apiCall('GET', url);

        if (!res.ok) {
            toastError('Gagal memuat laporan');
            return;
        }

        const d = res.data;
        bulanData = d.bulan_data || [];
        renderStats(d);
        renderTable();
    }

    function renderStats(d) {
        const container = document.getElementById('stat-cards');
        if (!container) return;
        container.innerHTML = `
            ${statCard({ label: 'Total Target ' + currentTahun, value: rp(d.total_target), icon: 'target', color: 'blue' })}
            ${statCard({ label: 'Total Realisasi', value: rp(d.total_realisasi), icon: 'trending-up', color: 'emerald' })}
            ${statCard({ label: 'Capaian', value: (d.capaian || 0) + '%', icon: 'percent', color: 'amber' })}
        `;
        document.getElementById('filter-info').innerHTML =
            `${bulanData.length} bulan · Total: <b class="text-blue-700">${rp(d.total_target)}</b>`;
        if (window.lucide) lucide.createIcons();
    }

    function renderTable() {
        const tbody = document.getElementById('laporan-tbody');
        const tfoot = document.getElementById('laporan-tfoot');

        if (bulanData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-gray-400">Belum ada data untuk tahun ${escapeHtml(currentTahun)}</td></tr>`;
            tfoot.innerHTML = '';
            return;
        }

        tbody.innerHTML = bulanData.map((m, i) => {
            const isOpen = openMonths.has(m.id);
            const capColor = m.capaian >= 100 ? 'text-green-700 bg-green-50'
                            : m.capaian >= 70 ? 'text-blue-700 bg-blue-50'
                            : m.capaian >= 40 ? 'text-amber-700 bg-amber-50'
                            : 'text-red-700 bg-red-50';
            const barColor = m.capaian >= 100 ? 'from-green-600 to-green-400'
                            : m.capaian >= 70 ? 'from-blue-700 to-blue-500'
                            : m.capaian >= 40 ? 'from-amber-500 to-amber-400'
                            : 'from-red-500 to-red-400';

            // Group by program
            const grouped = {};
            (m.details || []).forEach(d => {
                const progId = d.program_id;
                if (!grouped[progId]) {
                    grouped[progId] = { program: d.program, subs: {}, target: 0, realisasi: 0 };
                }
                const subId = d.sub_kegiatan_id || 0;
                if (!grouped[progId].subs[subId]) {
                    grouped[progId].subs[subId] = { sub_kegiatan: d.sub_kegiatan, komponens: [], target: 0, realisasi: 0 };
                }
                grouped[progId].subs[subId].komponens.push(d);
                grouped[progId].subs[subId].target += d.target;
                grouped[progId].subs[subId].realisasi += d.realisasi;
                grouped[progId].target += d.target;
                grouped[progId].realisasi += d.realisasi;
            });

            const programBlocksHtml = Object.values(grouped).map(g => {
                const prog = g.program;
                const progCap = g.target > 0 ? Math.round((g.realisasi / g.target) * 100) : 0;
                const progCapColor = progCap >= 100 ? 'text-green-700 bg-green-100'
                                    : progCap >= 70 ? 'text-blue-700 bg-blue-100'
                                    : progCap >= 40 ? 'text-amber-700 bg-amber-100'
                                    : 'text-red-700 bg-red-100';

                const subBlocksHtml = Object.values(g.subs).map(s => {
                    const sub = s.sub_kegiatan;
                    const subCap = s.target > 0 ? Math.round((s.realisasi / s.target) * 100) : 0;

                    const komponensHtml = s.komponens.map(d => {
                        const kom = d.komponen_belanja;
                        const capColor = d.capaian >= 100 ? 'text-green-700 bg-green-50'
                                        : d.capaian >= 70 ? 'text-blue-700 bg-blue-50'
                                        : d.capaian >= 40 ? 'text-amber-700 bg-amber-50'
                                        : 'text-red-700 bg-red-50';

                        return `
                            <tr class="border-b border-gray-100 last:border-0 hover:bg-blue-50/20">
                                <td class="py-2 pl-16 pr-3 text-left">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="receipt" class="w-3 h-3"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-gray-800 truncate">${escapeHtml(kom ? kom.nama : '-')}</p>
                                            <p class="text-[9px] text-gray-400 font-mono">${escapeHtml(kom ? kom.kode : '-')}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2 px-3 text-right text-xs text-blue-700 whitespace-nowrap">${rp(d.target)}</td>
                                <td class="py-2 px-3 text-right text-xs text-emerald-700 whitespace-nowrap">${rp(d.realisasi)}</td>
                                <td class="py-2 pl-3 pr-4 text-right w-20">
                                    <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded ${capColor}">${d.capaian}%</span>
                                </td>
                            </tr>`;
                    }).join('');

                    return `
                        <div class="mb-2 last:mb-0">
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50/70 rounded-t-md border border-indigo-100">
                                <div class="w-5 h-5 rounded bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="git-branch" class="w-2.5 h-2.5"></i>
                                </div>
                                <span class="text-[10px] font-mono font-bold text-indigo-700 bg-white px-1.5 py-0.5 rounded">${escapeHtml(sub ? sub.kode : '-')}</span>
                                <span class="text-xs font-semibold text-gray-800 flex-1 truncate">${escapeHtml(sub ? sub.nama : 'Tanpa Sub')}</span>
                                <span class="text-[10px] text-blue-700 font-semibold">T: ${rp(s.target)}</span>
                                <span class="text-[10px] text-emerald-700 font-semibold">R: ${rp(s.realisasi)}</span>
                                <span class="text-[10px] font-bold ${subCap >= 100 ? 'text-green-700' : subCap >= 70 ? 'text-blue-700' : subCap >= 40 ? 'text-amber-700' : 'text-red-700'} w-10 text-right">${subCap}%</span>
                            </div>
                            <div class="bg-white border border-t-0 border-indigo-100 rounded-b-md overflow-hidden">
                                <table class="w-full"><tbody>${komponensHtml}</tbody></table>
                            </div>
                        </div>`;
                }).join('');

                return `
                    <div class="mb-3 last:mb-0">
                        <div class="flex items-center gap-2.5 px-3 py-2 bg-blue-100/80 rounded-lg border border-blue-200 mb-2">
                            <div class="w-7 h-7 rounded-md bg-blue-700 text-white flex items-center justify-center flex-shrink-0">
                                <i data-lucide="folder-kanban" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-mono font-bold text-blue-700 bg-white px-1.5 py-0.5 rounded">${escapeHtml(prog ? prog.kode : '???')}</span>
                                    <span class="text-xs font-bold text-blue-900 truncate">${escapeHtml(prog ? prog.nama : 'Tidak diketahui')}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 text-[10px]">
                                <span class="text-blue-700 font-semibold">T: <b>${rp(g.target)}</b></span>
                                <span class="text-emerald-700 font-semibold">R: <b>${rp(g.realisasi)}</b></span>
                                <span class="font-bold px-1.5 py-0.5 rounded ${progCapColor}">${progCap}%</span>
                            </div>
                        </div>
                        <div class="pl-3">${subBlocksHtml}</div>
                    </div>`;
            }).join('');

            return `
                <tr class="hover:bg-blue-50/40 transition cursor-pointer" data-month-id="${m.id}" onclick="toggleMonth(${m.id})">
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 text-xs font-bold">${i + 1}</span>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-800">${escapeHtml(m.bulan_nama)}</span>
                            <i data-lucide="chevron-down" class="chevron-icon w-3.5 h-3.5 text-gray-400 ${isOpen ? 'rotate' : ''}"></i>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center gap-1.5 text-[10px] bg-purple-50 text-purple-700 font-semibold px-2.5 py-1 rounded-full">
                            <i data-lucide="folder-kanban" class="w-3 h-3"></i> ${Object.keys(grouped).length} program
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right text-blue-700 font-semibold whitespace-nowrap">${rp(m.target)}</td>
                    <td class="px-5 py-4 text-right text-emerald-700 font-semibold whitespace-nowrap">${rp(m.realisasi)}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-16 bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r ${barColor} h-full rounded-full" style="width:${Math.min(m.capaian, 100)}%"></div>
                            </div>
                            <span class="inline-block text-[10px] font-bold px-2 py-1 rounded ${capColor} whitespace-nowrap">${m.capaian}%</span>
                        </div>
                    </td>
                </tr>

                <tr data-expand-month="${m.id}" class="${isOpen ? '' : 'hidden'} bg-gradient-to-r from-blue-50/40 to-indigo-50/20">
                    <td colspan="6" class="p-0">
                        <div class="detail-expand ${isOpen ? 'open' : ''}">
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="layers" class="w-4 h-4 text-blue-700"></i>
                                        <span class="text-xs font-bold text-blue-900 uppercase tracking-wide">
                                            Breakdown ${escapeHtml(m.bulan_nama)} ${escapeHtml(currentTahun)}
                                        </span>
                                    </div>
                                </div>
                                ${programBlocksHtml || '<p class="text-sm text-gray-400 text-center py-4">Tidak ada data</p>'}
                            </div>
                        </div>
                    </td>
                </tr>`;
        }).join('');

        // Footer
        const totalT = bulanData.reduce((s, m) => s + m.target, 0);
        const totalR = bulanData.reduce((s, m) => s + m.realisasi, 0);
        const capTotal = totalT > 0 ? Math.round((totalR / totalT) * 100) : 0;
        tfoot.innerHTML = `
            <tr class="bg-gradient-to-r from-blue-50 to-indigo-50 border-t-2 border-blue-200">
                <td colspan="3" class="px-5 py-4 font-bold text-blue-900 text-sm">TOTAL TAHUNAN ${escapeHtml(currentTahun)}</td>
                <td class="px-5 py-4 text-right font-bold text-blue-800 text-sm whitespace-nowrap">${rp(totalT)}</td>
                <td class="px-5 py-4 text-right font-bold text-emerald-700 text-sm whitespace-nowrap">${rp(totalR)}</td>
                <td class="px-5 py-4 text-center">
                    <span class="inline-block text-xs font-bold px-3 py-1.5 rounded-full bg-amber-100 text-amber-700">${capTotal}%</span>
                </td>
            </tr>`;

        if (window.lucide) lucide.createIcons();
    }

    window.toggleMonth = function (id) {
        const row = document.querySelector(`tr[data-month-id="${id}"]`);
        const expandRow = document.querySelector(`tr[data-expand-month="${id}"]`);
        if (!row || !expandRow) return;

        const container = expandRow.querySelector('.detail-expand');
        const chevron = row.querySelector('.chevron-icon');
        const isOpen = openMonths.has(id);

        if (isOpen) {
            openMonths.delete(id);
            container.classList.remove('open');
            if (chevron) chevron.classList.remove('rotate');
            setTimeout(() => {
                if (!openMonths.has(id)) expandRow.classList.add('hidden');
            }, 350);
        } else {
            openMonths.add(id);
            expandRow.classList.remove('hidden');
            void container.offsetHeight;
            container.classList.add('open');
            if (chevron) chevron.classList.add('rotate');
        }
    };

    window.changeTahun = function (y) {
        currentTahun = Number(y);
        openMonths.clear();
        const url = new URL(window.location);
        url.searchParams.set('tahun', currentTahun);
        window.history.pushState({}, '', url);
        loadData();
    };

    window.doExport = function (format) {
        if (bulanData.length === 0) {
            toastError('Tidak ada data untuk diexport');
            return;
        }

        const url = format === 'PDF'
            ? `${window.LAPORAN_DATA.routes.exportPdf}?tahun=${currentTahun}`
            : `${window.LAPORAN_DATA.routes.exportCsv}?tahun=${currentTahun}`;

        const Toast2 = Swal.fire({
            title: `Menyiapkan ${format}...`,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            customClass: {
                popup: 'rounded-2xl',
            },
        });

        window.location.href = url;

        setTimeout(() => {
            Swal.close();
            toastSuccess(`${format} berhasil diunduh`);
        }, 1500);
    };

    document.addEventListener('DOMContentLoaded', loadData);
})();
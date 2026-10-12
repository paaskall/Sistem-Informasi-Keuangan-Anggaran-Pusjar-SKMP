// ============================================================
// SIKAP - Realisasi Page (with Kendala + Tahun Lock)
// ============================================================

(function () {
    const DATA = window.REALISASI_DATA;
    let currentTahun = DATA.initialTahun;
    let realisasis = [];
    let availableTargets = [];
    let kategoris = [];
    let komponens = [];
    let totalTarget = 0;
    let totalRealisasi = 0;
    const openDetails = new Set();

    // ============ HELPERS ============
    function buildRoute(template, id) {
        return template.replace('__ID__', id);
    }

    function buildTargetDetailsRoute(targetId) {
        return DATA.routes.targetDetails.replace('__TID__', targetId);
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

    // ============ ARSIP HELPER ============
    function isTahunArsip() {
        const tahunBerjalan = DATA.tahunBerjalan || new Date().getFullYear();
        return Number(currentTahun) !== Number(tahunBerjalan);
    }

    function updateArsipBanner() {
        const banner = document.getElementById('arsip-banner');
        const tahunEl = document.getElementById('arsip-tahun');
        const btnAdd = document.getElementById('btn-add-realisasi');
        if (!banner) return;

        const isArsip = isTahunArsip();

        if (isArsip) {
            banner.classList.remove('hidden');
            if (tahunEl) tahunEl.textContent = currentTahun;
            if (btnAdd) btnAdd.style.display = 'none';
        } else {
            banner.classList.add('hidden');
            if (btnAdd) btnAdd.style.display = '';
        }
        if (window.lucide) lucide.createIcons();
    }

    // ============ LOAD DATA ============
    async function loadData() {
        const url = DATA.routes.data + '?tahun=' + currentTahun;
        const res = await apiCall('GET', url);

        if (!res.ok) {
            toastError('Gagal memuat data realisasi');
            return;
        }

        realisasis = res.data.realisasis || [];
        availableTargets = res.data.available_targets || [];
        totalTarget = res.data.total_target || 0;
        totalRealisasi = res.data.total_realisasi || 0;
        kategoris = res.data.kategoris || [];
        komponens = (window.REALISASI_DATA.references?.komponens || []);

        updateArsipBanner();
        renderStats();
        renderTable();
    }

    // ============ RENDER STATS ============
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

    // ============ RENDER TABLE ============
    function renderTable() {
        const tbody = document.getElementById('realisasi-tbody');

        if (realisasis.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-10 text-gray-400">
                        <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                        <p class="text-sm">Belum ada realisasi untuk tahun ${currentTahun}</p>
                        <p class="text-xs mt-1">Klik "Input Realisasi" untuk memulai</p>
                    </td>
                </tr>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        const arsip = isTahunArsip();

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
                if (!grouped[progId]) {
                    grouped[progId] = { program: d.program, subs: {}, total: 0 };
                }
                const subId = d.sub_kegiatan_id || 0;
                if (!grouped[progId].subs[subId]) {
                    grouped[progId].subs[subId] = { sub_kegiatan: d.sub_kegiatan, komponens: [], total: 0 };
                }
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
                        const nominalR = d.nominal;
                        const pct = r.nominal > 0 ? ((nominalR / r.nominal) * 100).toFixed(1) : 0;

                        return `
                            <tr class="border-b border-gray-100 last:border-0 hover:bg-emerald-50/30">
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
                                <td class="py-2.5 px-3 text-right text-xs font-semibold text-emerald-700 whitespace-nowrap">${rp(nominalR)}</td>
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
                                            ${k.komponen_belanja ? `
                                                <span class="text-[10px] font-mono bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                                                    ${k.komponen_belanja.kode}
                                                </span>
                                            ` : ''}
                                            <span class="text-[10px] font-bold ${kStatusColor} px-2 py-0.5 rounded-full">
                                                ${k.status}
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-gray-500 whitespace-nowrap">${formatTanggal(k.tanggal)}</span>
                                    </div>
                                    <p class="text-xs text-gray-800 mb-1">${k.deskripsi}</p>
                                    ${k.dampak ? `<p class="text-[10px] text-red-600"><b>Dampak:</b> ${k.dampak}</p>` : ''}
                                    ${k.tindak_lanjut ? `<p class="text-[10px] text-blue-600"><b>Tindak Lanjut:</b> ${k.tindak_lanjut}</p>` : ''}
                                </div>`;
                        }).join('')}
                    </div>
                </div>
            ` : '';

            return `
                <tr class="hover:bg-emerald-50/40 transition" data-realisasi-id="${r.id}" onclick="toggleDetail(${r.id})">
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
                    <td class="px-5 py-4" onclick="event.stopPropagation()">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="toggleDetail(${r.id})"
                                class="text-purple-600 hover:bg-purple-100 p-2 rounded-lg transition" title="Lihat Detail">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            ${!arsip ? `
                                <button type="button" onclick="openRealisasiForm(${r.id})"
                                    class="text-blue-700 hover:bg-blue-100 p-2 rounded-lg transition" title="Edit">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <button type="button" onclick="deleteRealisasi(${r.id})"
                                    class="text-red-600 hover:bg-red-100 p-2 rounded-lg transition" title="Hapus">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            ` : `
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded" title="Tahun arsip - read only">
                                    <i data-lucide="lock" class="w-3 h-3"></i> ARSIP
                                </span>
                            `}
                        </div>
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

    // ============ TOGGLE DETAIL ============
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

    // ============ CHANGE TAHUN ============
    window.changeTahun = function (y) {
        currentTahun = Number(y);
        openDetails.clear();
        const url = new URL(window.location);
        url.searchParams.set('tahun', currentTahun);
        window.history.pushState({}, '', url);
        loadData();
    };

    // ============ FORM BUILDER ============
    function buildHeaderForm(realisasi, availableTargetsList) {
        const today = new Date().toISOString().substring(0, 10);
        const selectedTargetId = realisasi ? realisasi.target_id : (availableTargetsList[0]?.id || '');

        return `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">
                        Target Bulan <span class="text-red-500">*</span>
                    </label>
                    <select id="f-target" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 bg-white"
                        ${realisasi ? 'disabled' : ''}>
                        ${realisasi
                            ? `<option value="${realisasi.target_id}" selected>${realisasi.bulan_nama} ${realisasi.tahun}</option>`
                            : availableTargetsList.map(t => `
                                <option value="${t.id}" ${selectedTargetId == t.id ? 'selected' : ''}>
                                    ${t.bulan_nama} ${t.tahun} - Target ${rp(t.total)}
                                </option>
                            `).join('')}
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">
                        Tanggal Realisasi <span class="text-red-500">*</span>
                    </label>
                    <input id="f-tanggal" type="date" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                        value="${realisasi ? realisasi.tanggal : today}" />
                </div>
            </div>
        `;
    }

    function buildRealisasiRow(detail, idx) {
        const prog = detail.program;
        const sub = detail.sub_kegiatan;
        const kom = detail.komponen_belanja;
        const nominalR = detail.realisasi_nominal || 0;
        const targetNominal = detail.nominal || 0;

        return `
            <div class="realisasi-row detail-item border border-gray-200 rounded-lg p-3 bg-white"
                 data-idx="${idx}"
                 data-program-id="${detail.program_id}"
                 data-sub-kegiatan-id="${detail.sub_kegiatan_id || ''}"
                 data-komponen-id="${detail.komponen_belanja_id}"
                 data-target-nominal="${targetNominal}">
                <div class="grid grid-cols-3 gap-2 mb-2">
                    <div class="flex items-center gap-2 bg-blue-50 rounded-md px-2 py-1.5">
                        <div class="w-5 h-5 rounded bg-blue-700 text-white flex items-center justify-center text-[8px] flex-shrink-0">
                            <i data-lucide="folder" class="w-2.5 h-2.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-mono text-blue-600 truncate">${prog ? prog.kode : '-'}</p>
                            <p class="text-[10px] text-gray-700 truncate">${prog ? prog.nama : 'Tidak diketahui'}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-indigo-50 rounded-md px-2 py-1.5">
                        <div class="w-5 h-5 rounded bg-indigo-700 text-white flex items-center justify-center text-[8px] flex-shrink-0">
                            <i data-lucide="git-branch" class="w-2.5 h-2.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-mono text-indigo-600 truncate">${sub ? sub.kode : '-'}</p>
                            <p class="text-[10px] text-gray-700 truncate">${sub ? sub.nama : 'Tanpa Sub'}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 bg-emerald-50 rounded-md px-2 py-1.5">
                        <div class="w-5 h-5 rounded bg-emerald-700 text-white flex items-center justify-center text-[8px] flex-shrink-0">
                            <i data-lucide="receipt" class="w-2.5 h-2.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[9px] font-mono text-emerald-600 truncate">${kom ? kom.kode : '-'}</p>
                            <p class="text-[10px] text-gray-700 truncate">${kom ? kom.nama : 'Tidak diketahui'}</p>
                        </div>
                    </div>
                </div>
                <div class="grid gap-2" style="grid-template-columns: 1fr 200px; align-items: center;">
                    <div class="text-[10px] text-gray-500">
                        Target komponen: <b class="font-semibold text-gray-700">${rp(targetNominal)}</b>
                    </div>
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] font-semibold pointer-events-none">Rp</span>
                        <input type="text" inputmode="numeric"
                            class="realisasi-nominal input-nominal w-full pl-7 pr-2 py-2 border border-gray-200 rounded-lg text-xs text-right font-semibold focus:ring-2 focus:ring-emerald-500"
                            value="${nominalR ? Number(nominalR).toLocaleString('id-ID') : ''}" placeholder="0" />
                    </div>
                </div>
            </div>`;
    }

    // ============ KENDALA SECTION ============
    function buildKendalaRow(kendala, idx, targetKomponens) {
        const today = new Date().toISOString().substring(0, 10);
        const k = kendala || {
            tanggal: today,
            kategori_kendala_id: '',
            komponen_belanja_id: '',
            deskripsi: '',
            dampak: '',
            tindak_lanjut: '',
            status: 'Proses',
        };

        const selectedKomponenId = k.komponen_belanja_id || '';

        let komponenList = targetKomponens;
        if (!komponenList || komponenList.length === 0) {
            komponenList = (komponens || []).map(k => ({
                komponen_belanja_id: k.id,
                komponen_belanja: k,
            }));
        }

        return `
            <div class="kendala-row border border-amber-200 bg-amber-50/40 rounded-lg p-3" data-idx="${idx}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wide flex items-center gap-1.5">
                        <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                        Kendala #${idx + 1}
                    </span>
                    <button type="button" onclick="removeKendalaRow(this)"
                        class="text-red-500 hover:bg-red-100 p-1.5 rounded-lg transition" title="Hapus kendala">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-semibold block mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" class="k-tanggal w-full px-2.5 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500"
                            value="${k.tanggal}" />
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-semibold block mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select class="k-kategori w-full px-2.5 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                            <option value="">- Pilih Kategori -</option>
                            ${kategoris.map(x => `<option value="${x.id}" ${k.kategori_kendala_id == x.id ? 'selected' : ''}>${x.nama}</option>`).join('')}
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="text-[10px] text-gray-500 uppercase font-semibold block mb-1">
                        Terkait Komponen (opsional)
                    </label>
                    <select class="k-komponen w-full px-2.5 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                        <option value="">- Tidak terkait komponen spesifik -</option>
                        ${komponenList.map(tk => {
                            const kom = tk.komponen_belanja;
                            return `<option value="${tk.komponen_belanja_id}" ${selectedKomponenId == tk.komponen_belanja_id ? 'selected' : ''}>${kom ? kom.kode + ' - ' + kom.nama : '-'}</option>`;
                        }).join('')}
                    </select>
                </div>

                <div class="mb-2">
                    <label class="text-[10px] text-gray-500 uppercase font-semibold block mb-1">Deskripsi <span class="text-red-500">*</span></label>
                    <textarea class="k-deskripsi w-full px-2.5 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500" rows="2"
                        placeholder="Jelaskan kendala yang dihadapi...">${k.deskripsi}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-semibold block mb-1">Dampak</label>
                        <input type="text" class="k-dampak w-full px-2.5 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500"
                            value="${k.dampak || ''}" placeholder="Contoh: Menghambat pencairan" />
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-semibold block mb-1">Tindak Lanjut</label>
                        <input type="text" class="k-tindak w-full px-2.5 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500"
                            value="${k.tindak_lanjut || ''}" placeholder="Contoh: Rapat koordinasi" />
                    </div>
                </div>

                <div>
                    <label class="text-[10px] text-gray-500 uppercase font-semibold block mb-1">Status <span class="text-red-500">*</span></label>
                    <select class="k-status w-full px-2.5 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                        <option value="Proses" ${k.status === 'Proses' ? 'selected' : ''}>Proses</option>
                        <option value="Selesai" ${k.status === 'Selesai' ? 'selected' : ''}>Selesai</option>
                    </select>
                </div>
            </div>`;
    }

    function buildKendalaSection(kendalas, targetDetails) {
        const list = kendalas || [];
        return `
            <div class="border-t border-gray-200 pt-4 mt-4">
                <div class="flex justify-end mb-2">
                    <button type="button" onclick="addKendalaRow()"
                        class="text-xs bg-amber-100 hover:bg-amber-200 text-amber-800 font-semibold px-3 py-1.5 rounded-md transition flex items-center gap-1">
                        <i data-lucide="plus" class="w-3 h-3"></i> Tambah Kendala
                    </button>
                </div>
                <div id="kendala-container" class="space-y-2 max-h-80 overflow-y-auto pr-1">
                    ${list.map((k, i) => buildKendalaRow(k, i, targetDetails)).join('')}
                </div>
            </div>`;
    }

    window.addKendalaRow = function () {
        const container = document.getElementById('kendala-container');
        if (!container) return;

        const targetId = Number(document.getElementById('f-target').value);
        const targetDetails = availableTargets.find(t => t.id === targetId)?.details || [];

        const idx = container.querySelectorAll('.kendala-row').length;
        container.insertAdjacentHTML('beforeend', buildKendalaRow(null, idx, targetDetails));

        if (window.lucide) lucide.createIcons();
    };

    window.removeKendalaRow = function (btn) {
        const row = btn.closest('.kendala-row');
        if (!row) return;
        row.remove();

        document.querySelectorAll('#kendala-container .kendala-row').forEach((r, i) => {
            const title = r.querySelector('span');
            if (title && title.textContent.includes('Kendala')) {
                title.innerHTML = `<i data-lucide="alert-triangle" class="w-3 h-3"></i> Kendala #${i + 1}`;
            }
        });
        if (window.lucide) lucide.createIcons();
    };

    function buildForm(realisasi, targetData) {
        const details = (targetData?.details || []).map(d => {
            let realisasiNominal = 0;
            if (realisasi) {
                const existing = realisasi.details.find(rd =>
                    rd.program_id === d.program_id &&
                    (rd.sub_kegiatan_id || null) === (d.sub_kegiatan_id || null) &&
                    rd.komponen_belanja_id === d.komponen_belanja_id
                );
                if (existing) realisasiNominal = existing.nominal;
            }
            return { ...d, realisasi_nominal: realisasiNominal };
        });

        return `
            <div class="text-left space-y-4 text-sm mt-2">
                ${buildHeaderForm(realisasi, availableTargets)}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block font-semibold text-gray-700 text-xs uppercase tracking-wide">
                            Realisasi per Komponen <span class="text-gray-400 normal-case font-normal">(${details.length} item)</span>
                        </label>
                        <button type="button" onclick="fillAllFromTarget()"
                            class="text-[11px] bg-gray-100 hover:bg-emerald-100 text-gray-600 hover:text-emerald-700 font-semibold px-2.5 py-1 rounded-md transition flex items-center gap-1">
                            <i data-lucide="wand-2" class="w-3 h-3"></i> Isi sesuai target
                        </button>
                    </div>
                    <div id="form-komponen-container" class="space-y-2 max-h-96 overflow-y-auto pr-1">
                        ${details.map((d, i) => buildRealisasiRow(d, i)).join('') || '<p class="text-center text-gray-400 text-xs py-4">Tidak ada komponen di target ini</p>'}
                    </div>
                </div>
                <div class="bg-gradient-to-r from-emerald-50 to-blue-50 border border-emerald-100 rounded-lg p-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-emerald-900">Total Realisasi</span>
                    <div class="text-right">
                        <p id="f-total" class="text-lg font-bold text-emerald-800">Rp 0</p>
                        <p id="f-capaian" class="text-[10px] text-gray-500">Capaian: 0%</p>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Keterangan (opsional)</label>
                    <textarea id="f-keterangan" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                        placeholder="Catatan tambahan...">${realisasi ? (realisasi.keterangan || '') : ''}</textarea>
                </div>
                ${buildKendalaSection(realisasi ? realisasi.kendalas : [], targetData ? targetData.details : [])}
            </div>`;
    }

    function attachFormEvents() {
        document.querySelectorAll('.realisasi-nominal').forEach(inp => {
            attachNumberFormat(inp);
            if (!inp.dataset.bound) {
                inp.addEventListener('input', updateFormTotal);
                inp.dataset.bound = '1';
            }
        });
        updateFormTotal();
        if (window.lucide) lucide.createIcons();
    }

    function updateFormTotal() {
        const totalEl = document.getElementById('f-total');
        const capEl = document.getElementById('f-capaian');
        if (!totalEl) return;

        let total = 0;
        let totalTarget = 0;
        document.querySelectorAll('.realisasi-row').forEach(row => {
            const inp = row.querySelector('.realisasi-nominal');
            if (!inp) return;
            total += Number(inp.value.replace(/\D/g, '')) || 0;
            totalTarget += Number(row.dataset.targetNominal) || 0;
        });

        totalEl.textContent = rp(total);
        if (capEl) {
            const cap = totalTarget > 0 ? Math.round((total / totalTarget) * 100) : 0;
            capEl.textContent = `Capaian: ${cap}% dari target ${rp(totalTarget)}`;
        }
    }

    window.fillAllFromTarget = function () {
        document.querySelectorAll('.realisasi-row').forEach(row => {
            const inp = row.querySelector('.realisasi-nominal');
            if (!inp) return;
            const t = Number(row.dataset.targetNominal) || 0;
            inp.value = t ? Number(t).toLocaleString('id-ID') : '';
        });
        updateFormTotal();
        toastInfo('Semua input diisi sesuai target');
    };

    function collectForm() {
        const targetId = Number(document.getElementById('f-target').value);
        const tanggal = document.getElementById('f-tanggal').value;
        const keterangan = document.getElementById('f-keterangan').value.trim();
        const details = [];

        document.querySelectorAll('#form-komponen-container .realisasi-row').forEach(row => {
            const inp = row.querySelector('.realisasi-nominal');
            const nominal = Number(inp.value.replace(/\D/g, '')) || 0;
            if (nominal > 0) {
                details.push({
                    program_id: Number(row.dataset.programId),
                    sub_kegiatan_id: Number(row.dataset.subKegiatanId) || null,
                    komponen_belanja_id: Number(row.dataset.komponenId),
                    nominal,
                });
            }
        });

        const kendalas = [];
        document.querySelectorAll('#kendala-container .kendala-row').forEach(row => {
            const kategoriId = Number(row.querySelector('.k-kategori').value) || null;
            const komponenId = Number(row.querySelector('.k-komponen').value) || null;
            const deskripsi = row.querySelector('.k-deskripsi').value.trim();
            const dampak = row.querySelector('.k-dampak').value.trim();
            const tindakLanjut = row.querySelector('.k-tindak').value.trim();
            const status = row.querySelector('.k-status').value;
            const kendalaTanggal = row.querySelector('.k-tanggal').value;

            if (!deskripsi) return;

            if (!kategoriId) {
                Swal.showValidationMessage('Kategori kendala wajib dipilih untuk setiap kendala');
                return;
            }

            kendalas.push({
                tanggal: kendalaTanggal || tanggal,
                kategori_kendala_id: kategoriId,
                komponen_belanja_id: komponenId,
                deskripsi,
                dampak: dampak || null,
                tindak_lanjut: tindakLanjut || null,
                status,
            });
        });

        return { target_id: targetId, tanggal, keterangan, details, kendalas };
    }

    // ============ OPEN FORM ============
    window.openRealisasiForm = async function (id) {
        if (isTahunArsip()) {
            toastError('Tahun ' + currentTahun + ' sudah dikunci (arsip). Tidak bisa tambah/edit.');
            return;
        }

        const realisasi = id ? realisasis.find(r => r.id === id) : null;

        if (!realisasi && availableTargets.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Semua Sudah Diinput',
                text: 'Semua target bulanan sudah memiliki realisasi untuk tahun ini.',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg',
                },
                buttonsStyling: false,
            });
            return;
        }

        let targetData = null;

        if (realisasi) {
            const res = await apiCall('GET', buildTargetDetailsRoute(realisasi.target_id));
            if (res.ok && res.data.target && res.data.target.details) {
                targetData = res.data.target;
            } else {
                targetData = {
                    details: realisasi.details.map(d => ({
                        program_id: d.program_id,
                        program: d.program,
                        sub_kegiatan_id: d.sub_kegiatan_id,
                        sub_kegiatan: d.sub_kegiatan,
                        komponen_belanja_id: d.komponen_belanja_id,
                        komponen_belanja: d.komponen_belanja,
                        nominal: 0,
                    })),
                };
            }
        } else {
            if (!availableTargets[0]) {
                toastError('Tidak ada target tersedia');
                return;
            }

            if (!availableTargets[0].details || availableTargets[0].details.length === 0) {
                const res = await apiCall('GET', buildTargetDetailsRoute(availableTargets[0].id));
                if (res.ok && res.data.target) {
                    targetData = res.data.target;
                } else {
                    targetData = availableTargets[0];
                }
            } else {
                targetData = availableTargets[0];
            }
        }

        Swal.fire({
            title: realisasi ? 'Edit Realisasi' : 'Input Realisasi',
            width: 920,
            html: buildForm(realisasi, targetData),
            showCancelButton: true,
            confirmButtonText: realisasi ? 'Update' : 'Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => {
                attachFormEvents();

                const tSel = document.getElementById('f-target');
                if (tSel && !realisasi) {
                    tSel.addEventListener('change', async () => {
                        const tid = Number(tSel.value);
                        const res = await apiCall('GET', buildTargetDetailsRoute(tid));
                        if (res.ok) {
                            const td = res.data.target;
                            const container = document.getElementById('form-komponen-container');
                            const details = td.details.map(d => ({ ...d, realisasi_nominal: 0 }));
                            container.innerHTML = details.map((d, i) => buildRealisasiRow(d, i)).join('');
                            attachFormEvents();

                            const kendalaContainer = document.getElementById('kendala-container');
                            if (kendalaContainer) {
                                kendalaContainer.querySelectorAll('.k-komponen').forEach(sel => {
                                    const currentVal = sel.value;
                                    sel.innerHTML = '<option value="">- Tidak terkait komponen spesifik -</option>' +
                                        td.details.map(d => {
                                            const kom = d.komponen_belanja;
                                            return `<option value="${d.komponen_belanja_id}">${kom ? kom.kode + ' - ' + kom.nama : '-'}</option>`;
                                        }).join('');
                                    sel.value = currentVal;
                                });
                            }
                        }
                    });
                }
            },
            preConfirm: () => {
                const data = collectForm();
                if (!data.target_id || !data.tanggal) {
                    Swal.showValidationMessage('Target dan tanggal wajib diisi');
                    return false;
                }
                if (data.details.length === 0) {
                    Swal.showValidationMessage('Minimal isi 1 komponen dengan nominal > 0');
                    return false;
                }
                return data;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;

            const url = realisasi ? buildRoute(DATA.routes.update, realisasi.id) : DATA.routes.store;
            const method = realisasi ? 'PUT' : 'POST';
            const res = await apiCall(method, url, r.value);

            if (res.ok) {
                toastSuccess(res.data.message);
                if (!realisasi && res.data.realisasi) openDetails.add(res.data.realisasi.id);
                loadData();
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
            }
        });
    };

    // ============ DELETE ============
    window.deleteRealisasi = function (id) {
        if (isTahunArsip()) {
            toastError('Tahun ' + currentTahun + ' sudah dikunci (arsip). Tidak bisa hapus.');
            return;
        }

        const r = realisasis.find(x => x.id === id);
        if (!r) return;

        Swal.fire({
            title: 'Hapus Realisasi?',
            html: `<div class="text-sm text-gray-600">
                <p>Realisasi <b>${r.bulan_nama} ${r.tahun}</b> akan dihapus.</p>
                <div class="bg-gray-50 rounded-lg p-3 text-xs mt-3">
                    <div class="flex justify-between"><span>Tanggal:</span><b>${formatTanggal(r.tanggal)}</b></div>
                    <div class="flex justify-between"><span>Total:</span><b class="text-emerald-700">${rp(r.nominal)}</b></div>
                    <div class="flex justify-between"><span>Komponen:</span><b>${r.details.length} item</b></div>
                </div>
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
            },
            buttonsStyling: false,
        }).then(async (res) => {
            if (!res.isConfirmed) return;
            const url = buildRoute(DATA.routes.destroy, id);
            const response = await apiCall('DELETE', url);
            if (response.ok) {
                toastSuccess(response.data.message);
                loadData();
            } else {
                toastError(response.data.message || 'Gagal menghapus');
            }
        });
    };

    // ============ INIT ============
    document.addEventListener('DOMContentLoaded', loadData);
})();
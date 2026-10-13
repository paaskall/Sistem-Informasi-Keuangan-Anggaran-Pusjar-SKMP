// ============================================================
// SIKAP - Dashboard (Keuangan & Pimpinan & Viewer)
// ============================================================

(function () {
    const DATA = window.DASHBOARD_DATA;
    let currentTahun = DATA.initialTahun;
    let chartData = [];

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

    function formatNilaiShort(n) {
        if (!n && n !== 0) return 'Rp 0';
        if (n >= 1_000_000_000) return (n / 1_000_000_000).toFixed(1) + ' M';
        if (n >= 1_000_000) return (n / 1_000_000).toFixed(0) + ' Jt';
        if (n >= 1_000) return (n / 1_000).toFixed(0) + 'K';
        return n.toString();
    }

    function updateArsipBanner() {
        const banner = document.getElementById('arsip-banner');
        const tahunEl = document.getElementById('arsip-tahun');
        const btnInput = document.getElementById('btn-input-realisasi');
        if (!banner) return;

        const tahunBerjalan = DATA.tahunBerjalan || new Date().getFullYear();
        const isArsip = Number(currentTahun) !== Number(tahunBerjalan);

        if (isArsip) {
            banner.classList.remove('hidden');
            if (tahunEl) tahunEl.textContent = currentTahun;
            if (btnInput) btnInput.style.display = 'none';
        } else {
            banner.classList.add('hidden');
            if (btnInput) btnInput.style.display = '';
        }
        if (window.lucide) lucide.createIcons();
    }

    async function loadData() {
        const url = DATA.routes.data + '?tahun=' + currentTahun;
        const res = await apiCall('GET', url);

        if (!res.ok) {
            toastError('Gagal memuat dashboard');
            return;
        }

        const d = res.data;
        renderSummaryCard(d.stats);
        renderPnbpSummary(d.pnbp_summary);
        renderChart(d.per_bulan);
        renderProgressPengadaan(d.progress_pengadaan);
        renderRencanaBulanIni(d.rencana_bulan_ini);

        // 3 SECTION BARU
        renderCatatanKendala(d.catatan_kendala);
        renderAkselerasi(d.akselerasi);
        renderKendalaPengadaan(d.kendala_pengadaan);
        renderRencanaPengadaan(d.rencana_pengadaan);

        renderBulanIni(d.bulan_ini);
        if (d.ringkasan_modul) renderRingkasanModul(d.ringkasan_modul);
        updateArsipBanner();

        const labelEl = document.getElementById('label-tahun');
        if (labelEl) labelEl.textContent = currentTahun;
        const chTahun = document.getElementById('chart-tahun');
        if (chTahun) chTahun.textContent = currentTahun;
    }

    // ============================================================
    // SUMMARY CARD (ANGGARAN)
    // ============================================================
    function renderSummaryCard(s) {
        const container = document.getElementById('summary-card');
        if (!container) return;

        const totalTarget = s.total_target || 0;
        const totalRealisasi = s.total_realisasi || 0;
        const capaian = s.capaian || 0;
        const sisa = Math.max(0, totalTarget - totalRealisasi);
        const sisaPct = totalTarget > 0 ? (100 - capaian) : 0;

        const barColor = capaian >= 100 ? 'from-green-500 to-green-400'
                        : capaian >= 70 ? 'from-blue-600 to-blue-400'
                        : capaian >= 40 ? 'from-amber-500 to-amber-400'
                        : 'from-red-500 to-red-400';
        const capTextColor = capaian >= 100 ? 'text-green-700'
                            : capaian >= 70 ? 'text-blue-700'
                            : capaian >= 40 ? 'text-amber-700'
                            : 'text-red-700';

        container.innerHTML = `
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-blue-700 to-blue-600 px-6 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 text-white"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-white font-bold text-base">Target dan Realisasi Anggaran Tahun ${currentTahun}</h3>
                        <p class="text-blue-100 text-xs mt-0.5">Ringkasan eksekutif pencapaian anggaran</p>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center">
                                    <i data-lucide="target" class="w-4 h-4 text-white"></i>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Target</p>
                                <p class="text-xl sm:text-2xl font-bold text-blue-800 break-all">${rp(totalTarget)}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 md:border-l md:border-gray-100 md:pl-6">
                            <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center">
                                    <i data-lucide="trending-up" class="w-4 h-4 text-white"></i>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Realisasi</p>
                                <p class="text-xl sm:text-2xl font-bold text-emerald-700 break-all">${rp(totalRealisasi)}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Progress Pencapaian</span>
                            <span class="text-2xl font-bold ${capTextColor}">${capaian}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-6 overflow-hidden shadow-inner">
                            <div class="bg-gradient-to-r ${barColor} h-full rounded-full transition-all duration-1000 ease-out flex items-center justify-end pr-3"
                                 style="width: ${Math.min(capaian, 100)}%">
                                ${capaian >= 15 ? `<span class="text-white text-[10px] font-bold">${capaian}%</span>` : ''}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-100 rounded-xl px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                <i data-lucide="wallet" class="w-5 h-5 text-amber-700"></i>
                            </div>
                            <div>
                                <p class="text-xs text-amber-800 uppercase font-bold tracking-wider">Sisa Anggaran</p>
                                <p class="text-base font-bold text-amber-900">${rp(sisa)}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-amber-700 uppercase font-bold tracking-wider">Persentase</p>
                            <p class="text-base font-bold text-amber-800">(${sisaPct.toFixed(1)}%)</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // DONUT SVG BUILDER
    // ============================================================
    function buildDonutSvg(progress, size = 130) {
        const strokeWidth = 14;
        const radius = (size - strokeWidth) / 2;
        const circumference = 2 * Math.PI * radius;
        const offset = circumference - (progress / 100) * circumference;

        let strokeColor = '#dc2626';
        let trackColor = '#fee2e2';
        if (progress >= 100) {
            strokeColor = '#059669';
            trackColor = '#d1fae5';
        } else if (progress >= 70) {
            strokeColor = '#1d4ed8';
            trackColor = '#dbeafe';
        } else if (progress >= 40) {
            strokeColor = '#d97706';
            trackColor = '#fef3c7';
        }

        return `
            <svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" style="transform: rotate(-90deg);">
                <circle cx="${size/2}" cy="${size/2}" r="${radius}" fill="none" stroke="${trackColor}" stroke-width="${strokeWidth}" />
                <circle cx="${size/2}" cy="${size/2}" r="${radius}" fill="none" stroke="${strokeColor}" stroke-width="${strokeWidth}" stroke-linecap="round" stroke-dasharray="${circumference}" stroke-dashoffset="${offset}" style="transition: stroke-dashoffset 1s ease-out;" />
            </svg>
        `;
    }

    // ============================================================
    // PROGRESS PENGADAAN (DONUT)
    // ============================================================
    function renderProgressPengadaan(items) {
        const container = document.getElementById('progress-pengadaan-container');
        if (!container) return;

        if (!items || items.length === 0) {
            container.innerHTML = `
                <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-2">
                        <i data-lucide="pie-chart" class="w-5 h-5 text-blue-700"></i>
                        Progress Pengadaan / Kontrak
                    </h3>
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada data pengadaan</p>
                </div>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        const donutsHtml = items.map(item => {
            const donut = buildDonutSvg(item.progress, 130);
            const capText = item.progress === 100 ? 'text-green-700'
                            : item.progress >= 70 ? 'text-blue-700'
                            : item.progress >= 40 ? 'text-amber-700'
                            : 'text-red-700';
            const statusClass = item.progress === 100 ? 'text-green-700 bg-green-50'
                                : item.progress > 0 ? 'text-blue-700 bg-blue-50'
                                : 'text-gray-500 bg-gray-50';

            return `
                <div class="flex flex-col items-center text-center">
                    <div class="relative mb-3">
                        ${donut}
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-2xl font-bold ${capText}">${item.progress}%</span>
                        </div>
                    </div>
                    <p class="text-sm font-semibold text-gray-800 leading-tight mb-1 max-w-[180px]">${item.nama}</p>
                    <p class="text-[11px] text-gray-500 mb-2">(${formatNilaiShort(item.nilai)})</p>
                    <span class="inline-block text-[10px] font-bold ${statusClass} px-2.5 py-1 rounded-full">
                        ${item.keterangan}
                    </span>
                </div>`;
        }).join('');

        container.innerHTML = `
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-blue-50/50 border-b border-gray-100 px-6 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-500 flex items-center justify-center shadow-md shadow-blue-200">
                        <i data-lucide="pie-chart" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Progress Pengadaan / Kontrak</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Status penyelesaian paket pengadaan</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 justify-items-center">
                        ${donutsHtml}
                    </div>
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // PNBP SUMMARY CARD
    // ============================================================
    function renderPnbpSummary(data) {
        const container = document.getElementById('pnbp-summary-card');
        if (!container) return;

        if (!data || !data.exists) {
            container.innerHTML = `
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-full">
                    <div class="bg-gradient-to-r from-emerald-700 to-emerald-600 px-6 py-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="receipt" class="w-5 h-5 text-white"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-white font-bold text-base">Realisasi Penerimaan PNBP</h3>
                            <p class="text-emerald-100 text-xs mt-0.5">Belum ada data tahun ini</p>
                        </div>
                    </div>
                    <div class="p-6 text-center">
                        <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                            <i data-lucide="receipt" class="w-8 h-8 text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-500 mb-3">Belum ada data PNBP untuk tahun ${currentTahun}</p>
                        <a href="/keuangan/pnbp?tahun=${currentTahun}"
                        class="inline-flex items-center gap-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-lg transition">
                            <i data-lucide="plus" class="w-3 h-3"></i> Buat Data PNBP
                        </a>
                    </div>
                </div>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        const capaian = data.capaian || 0;
        const capColor = capaian >= 100 ? 'text-green-700'
                        : capaian >= 70 ? 'text-blue-700'
                        : capaian >= 40 ? 'text-amber-700'
                        : 'text-red-700';
        const barColor = capaian >= 100 ? 'from-green-500 to-green-400'
                        : capaian >= 70 ? 'from-blue-600 to-blue-400'
                        : capaian >= 40 ? 'from-amber-500 to-amber-400'
                        : 'from-red-500 to-red-400';

        const kategorisHtml = (data.kategoris || []).map(k => {
            const kCap = k.capaian;
            const kColor = kCap >= 100 ? 'text-green-700'
                        : kCap >= 70 ? 'text-blue-700'
                        : kCap >= 40 ? 'text-amber-700'
                        : 'text-red-700';
            const kBarColor = kCap >= 100 ? 'from-green-600 to-green-400'
                            : kCap >= 70 ? 'from-blue-600 to-blue-400'
                            : kCap >= 40 ? 'from-amber-500 to-amber-400'
                            : 'from-red-500 to-red-400';

            return `
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-gray-700 truncate">${k.nama}</p>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden mt-1">
                            <div class="bg-gradient-to-r ${kBarColor} h-full rounded-full transition-all duration-700"
                                style="width: ${Math.min(kCap, 100)}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-gray-500 mt-0.5">
                            <span class="text-emerald-700 font-semibold">${rp(k.realisasi)}</span>
                            <span class="text-blue-700 font-semibold">${rp(k.target)}</span>
                        </div>
                    </div>
                    <span class="text-xs font-bold ${kColor} w-10 text-right whitespace-nowrap">${kCap}%</span>
                </div>`;
        }).join('');

        container.innerHTML = `
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-full flex flex-col">
                <div class="bg-gradient-to-r from-emerald-700 to-emerald-600 px-6 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="target" class="w-5 h-5 text-white"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-white font-bold text-base">Realisasi Penerimaan PNBP</h3>
                        <p class="text-emerald-100 text-xs mt-0.5">Tahun ${data.tahun} · ${data.unit || '-'}</p>
                    </div>
                </div>

                <div class="p-6 flex-1">
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="target" class="w-4 h-4 text-blue-700"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Target</p>
                                <p class="text-sm font-bold text-blue-800 break-all">${rp(data.total_target)}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="trending-up" class="w-4 h-4 text-emerald-700"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Realisasi</p>
                                <p class="text-sm font-bold text-emerald-700 break-all">${rp(data.total_realisasi)}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-bold text-gray-600 uppercase tracking-wider">Capaian</span>
                            <span class="text-lg font-bold ${capColor}">${capaian}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden shadow-inner">
                            <div class="bg-gradient-to-r ${barColor} h-full rounded-full transition-all duration-1000"
                                style="width: ${Math.min(capaian, 100)}%"></div>
                        </div>
                    </div>

                    ${kategorisHtml ? `
                        <div class="pt-4 border-t border-gray-100">
                            <div class="space-y-2.5">${kategorisHtml}</div>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // CHART BULANAN
    // ============================================================
    function renderChart(perBulan) {
        chartData = perBulan;
        const container = document.getElementById('chart-container');
        if (!container) return;

        if (!perBulan || perBulan.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i data-lucide="bar-chart-3" class="w-12 h-12 mx-auto mb-3 text-gray-200"></i>
                    <p class="text-sm text-gray-400">Belum ada data untuk tahun ini</p>
                </div>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        const maxValue = Math.max(...perBulan.map(x => Math.max(x.target, x.realisasi)), 1);
        const chartHeight = 320;

        const fmtShort = (n) => {
            if (n >= 1_000_000_000) return (n / 1_000_000_000).toFixed(1) + 'M';
            if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
            if (n >= 1_000) return (n / 1_000).toFixed(0) + 'K';
            return n.toFixed(0);
        };

        const fmtFull = (n) => 'Rp' + Number(n).toLocaleString('id-ID');

        const ySteps = 6;
        const yLabels = [];
        for (let i = ySteps - 1; i >= 0; i--) {
            const val = (maxValue / (ySteps - 1)) * i;
            yLabels.push({ value: val, label: fmtShort(val) });
        }

        const totalTarget = perBulan.reduce((s, b) => s + b.target, 0);
        const totalRealisasi = perBulan.reduce((s, b) => s + b.realisasi, 0);
        const totalCapaian = totalTarget > 0 ? Math.round((totalRealisasi / totalTarget) * 100) : 0;

        const bulanHtml = perBulan.map(b => {
            const targetPct = Math.max((b.target / maxValue) * 100, 1);
            const realPct = Math.max((b.realisasi / maxValue) * 100, 1);
            const capClass = b.capaian >= 100 ? 'text-green-700 bg-green-100 border-green-200'
                            : b.capaian >= 70 ? 'text-blue-700 bg-blue-100 border-blue-200'
                            : b.capaian >= 40 ? 'text-amber-700 bg-amber-100 border-amber-200'
                            : 'text-red-700 bg-red-100 border-red-200';

            return `
                <div class="flex flex-col items-center group">
                    <div class="flex items-end justify-center gap-1.5 w-full" style="height: ${chartHeight}px;">
                        <div class="flex-1 flex flex-col items-center justify-end h-full relative">
                            <span class="text-[10px] font-bold text-blue-700 mb-1 whitespace-nowrap">${fmtShort(b.target)}</span>
                            <div class="w-full max-w-[24px] bg-gradient-to-t from-blue-700 via-blue-600 to-blue-400 rounded-t-md transition-all duration-300 group-hover:shadow-lg group-hover:shadow-blue-300/50 group-hover:scale-x-110 cursor-pointer"
                                 style="height: ${targetPct}%;"
                                 title="Target ${b.bulan_nama}: ${fmtFull(b.target)}"></div>
                        </div>
                        <div class="flex-1 flex flex-col items-center justify-end h-full relative">
                            <span class="text-[10px] font-bold text-emerald-700 mb-1 whitespace-nowrap">${fmtShort(b.realisasi)}</span>
                            <div class="w-full max-w-[24px] bg-gradient-to-t from-emerald-700 via-emerald-600 to-emerald-400 rounded-t-md transition-all duration-300 group-hover:shadow-lg group-hover:shadow-emerald-300/50 group-hover:scale-x-110 cursor-pointer"
                                 style="height: ${realPct}%;"
                                 title="Realisasi ${b.bulan_nama}: ${fmtFull(b.realisasi)}"></div>
                        </div>
                    </div>
                    <div class="mt-2 flex flex-col items-center gap-1">
                        <span class="text-[11px] font-bold text-gray-700 whitespace-nowrap">${b.bulan_short}</span>
                        <span class="text-[9px] font-bold ${capClass} border px-1.5 py-0.5 rounded-full whitespace-nowrap">${b.capaian}%</span>
                    </div>
                </div>`;
        }).join('');

        container.innerHTML = `
            <div class="w-full overflow-x-auto pb-2">
                <div class="min-w-[750px]">
                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100/50 border border-blue-100 rounded-xl p-3">
                            <p class="text-[10px] text-blue-700 uppercase font-bold tracking-wider mb-1">Total Target</p>
                            <p class="text-sm sm:text-base font-bold text-blue-800">${fmtFull(totalTarget)}</p>
                        </div>
                        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 border border-emerald-100 rounded-xl p-3">
                            <p class="text-[10px] text-emerald-700 uppercase font-bold tracking-wider mb-1">Total Realisasi</p>
                            <p class="text-sm sm:text-base font-bold text-emerald-800">${fmtFull(totalRealisasi)}</p>
                        </div>
                        <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 border border-amber-100 rounded-xl p-3">
                            <p class="text-[10px] text-amber-700 uppercase font-bold tracking-wider mb-1">Capaian</p>
                            <div class="flex items-center gap-2">
                                <p class="text-sm sm:text-base font-bold text-amber-800">${totalCapaian}%</p>
                                <div class="flex-1 bg-amber-200 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-amber-600 to-amber-400 h-full rounded-full" style="width: ${Math.min(totalCapaian, 100)}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="flex flex-col justify-between flex-shrink-0" style="height: ${chartHeight}px; padding-bottom: 60px;">
                            ${yLabels.map(y => `<span class="text-[10px] text-gray-400 whitespace-nowrap text-right">Rp${y.label}</span>`).join('')}
                        </div>

                        <div class="flex-1 relative" style="min-width: 0;">
                            <div class="absolute inset-x-0 top-0 flex flex-col justify-between pointer-events-none" style="height: ${chartHeight}px;">
                                ${yLabels.map((_, i) => `<div class="border-t ${i === yLabels.length - 1 ? 'border-gray-300' : 'border-dashed border-gray-200'}"></div>`).join('')}
                            </div>

                            <div class="relative grid gap-3" style="grid-template-columns: repeat(${perBulan.length}, minmax(0, 1fr));">
                                ${bulanHtml}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // RENCANA REALISASI BULAN INI
    // ============================================================
    function renderRencanaBulanIni(data) {
        const container = document.getElementById('rencana-bulan-ini-container');
        if (!container) return;

        if (!data || !data.items || data.items.length === 0) {
            container.innerHTML = `
                <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-2">
                        <i data-lucide="clipboard-list" class="w-5 h-5 text-blue-700"></i>
                        Rencana Realisasi Bulan Ini
                    </h3>
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada data rencana pembayaran bulan ini</p>
                </div>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        const capaian = data.total_target > 0
            ? Math.round((data.total_realisasi / data.total_target) * 100)
            : 0;

        const statusConfig = {
            siap: { label: 'Siap Dicairkan', class: 'bg-green-100 text-green-700', icon: 'check-circle' },
            menunggu: { label: 'Masih Menunggu', class: 'bg-amber-100 text-amber-700', icon: 'clock' },
            belum: { label: 'Belum Diinput', class: 'bg-gray-100 text-gray-600', icon: 'circle-dashed' },
        };

        const itemsHtml = data.items.map((item, idx) => {
            const cfg = statusConfig[item.status];
            return `
                <tr class="border-b border-gray-100 last:border-0 hover:bg-blue-50/30">
                    <td class="px-4 py-3 text-center text-xs text-gray-500 font-semibold w-12">${idx + 1}</td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-medium text-gray-800">${item.nama}</p>
                        <p class="text-[10px] text-gray-400">${item.kode}</p>
                    </td>
                    <td class="px-4 py-3 text-right text-xs text-blue-700 font-semibold whitespace-nowrap">${rp(item.target)}</td>
                    <td class="px-4 py-3 text-right text-xs text-emerald-700 font-semibold whitespace-nowrap">
                        ${item.realisasi > 0 ? rp(item.realisasi) : '<span class="text-gray-300">-</span>'}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-xs font-bold ${item.capaian >= 100 ? 'text-green-700' : item.capaian >= 50 ? 'text-amber-700' : 'text-red-700'}">${item.capaian}%</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold ${cfg.class} whitespace-nowrap">
                            <i data-lucide="${cfg.icon}" class="w-3 h-3"></i>
                            ${cfg.label}
                        </span>
                    </td>
                </tr>`;
        }).join('');

        const summaryBadges = `
            <div class="flex flex-wrap items-center gap-3 text-[10px]">
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 text-green-700 font-bold">
                    <i data-lucide="check-circle" class="w-3 h-3"></i> ${data.status_summary.siap} Siap
                </span>
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 font-bold">
                    <i data-lucide="clock" class="w-3 h-3"></i> ${data.status_summary.menunggu} Menunggu
                </span>
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 font-bold">
                    <i data-lucide="circle-dashed" class="w-3 h-3"></i> ${data.status_summary.belum} Belum
                </span>
            </div>`;

        container.innerHTML = `
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100">
                    <div>
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <i data-lucide="clipboard-list" class="w-5 h-5 text-blue-700"></i>
                            Rencana Realisasi ${data.bulan_nama} ${data.tahun}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Daftar pembayaran yang perlu dicairkan bulan ini</p>
                    </div>
                    ${summaryBadges}
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-5 border-b border-gray-100">
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-[10px] text-blue-700 uppercase font-bold tracking-wider mb-1">Total Anggaran</p>
                        <p class="text-lg font-bold text-blue-800">${rp(data.total_target)}</p>
                    </div>
                    <div class="bg-emerald-50 rounded-lg p-3">
                        <p class="text-[10px] text-emerald-700 uppercase font-bold tracking-wider mb-1">Total Realisasi</p>
                        <p class="text-lg font-bold text-emerald-800">${rp(data.total_realisasi)}</p>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-3">
                        <p class="text-[10px] text-amber-700 uppercase font-bold tracking-wider mb-1">Capaian</p>
                        <div class="flex items-center gap-2">
                            <p class="text-lg font-bold text-amber-800">${capaian}%</p>
                            <div class="flex-1 bg-amber-200 rounded-full h-2 overflow-hidden">
                                <div class="bg-amber-600 h-full rounded-full transition-all duration-700" style="width: ${Math.min(capaian, 100)}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-center w-12">No</th>
                                <th class="px-4 py-3 font-semibold text-left">Jenis Pembayaran</th>
                                <th class="px-4 py-3 font-semibold text-right">Anggaran</th>
                                <th class="px-4 py-3 font-semibold text-right">Realisasi</th>
                                <th class="px-4 py-3 font-semibold text-right w-16">Cap</th>
                                <th class="px-4 py-3 font-semibold text-center w-40">Status</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                        <tfoot class="bg-gradient-to-r from-blue-50 to-indigo-50 border-t-2 border-blue-100">
                            <tr>
                                <td colspan="2" class="px-4 py-3 font-bold text-blue-900 text-xs">TOTAL</td>
                                <td class="px-4 py-3 text-right font-bold text-blue-800 text-xs whitespace-nowrap">${rp(data.total_target)}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-800 text-xs whitespace-nowrap">${rp(data.total_realisasi)}</td>
                                <td class="px-4 py-3 text-right font-bold text-amber-800 text-xs">${capaian}%</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>`;

        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // MATRIKS MANAJEMEN RISIKO REALISASI ANGGARAN (3 KOLOM)
    // ============================================================
    function renderCatatanKendala(data) {
        const container = document.getElementById('catatan-kendala-container');
        if (!container) return;

        if (!data || !data.items || data.items.length === 0) {
            container.innerHTML = '';
            return;
        }

        const rowsHtml = data.items.map((item, idx) => {
            // Warna badge per kategori
            const katLower = (item.kategori || '').toLowerCase();
            let katClass = 'bg-gray-100 text-gray-700';
            if (katLower.includes('administrasi')) {
                katClass = 'bg-purple-100 text-purple-700';
            } else if (katLower.includes('pengadaan')) {
                katClass = 'bg-blue-100 text-blue-700';
            } else if (katLower.includes('keuangan') || katLower.includes('pembayaran')) {
                katClass = 'bg-amber-100 text-amber-700';
            } else if (katLower.includes('teknis')) {
                katClass = 'bg-emerald-100 text-emerald-700';
            }

            return `
                <tr class="border-b border-gray-100 last:border-0 hover:bg-red-50/30 transition">
                    <td class="px-4 py-4 text-center align-top w-12">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-red-100 text-red-700 text-xs font-bold">
                            ${idx + 1}
                        </span>
                    </td>
                    <td class="px-4 py-4 align-top" style="width: 40%;">
                        <p class="text-xs font-semibold text-gray-800 leading-snug mb-1.5">
                            ${item.kendala}
                        </p>
                        <span class="inline-block text-[10px] font-bold ${katClass} px-2 py-0.5 rounded-full">
                            ${item.kategori}
                        </span>
                    </td>
                    <td class="px-4 py-4 align-top" style="width: 30%;">
                        ${item.dampak ? `
                            <p class="text-xs text-gray-700 leading-relaxed">${item.dampak}</p>
                        ` : `
                            <p class="text-xs text-gray-400 italic">Belum diisi</p>
                        `}
                    </td>
                    <td class="px-4 py-4 align-top" style="width: 30%;">
                        ${item.tindak_lanjut ? `
                            <p class="text-xs text-gray-700 leading-relaxed">${item.tindak_lanjut}</p>
                        ` : `
                            <p class="text-xs text-gray-400 italic">Belum diisi</p>
                        `}
                    </td>
                </tr>
            `;
        }).join('');

        container.innerHTML = `
            <div class="bg-white rounded-2xl border-2 border-red-200 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-red-50 to-pink-50 px-6 py-4 flex items-center gap-3 border-b border-red-200/60">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-red-900">
                            ${data.title || 'Matriks Manajemen Risiko Realisasi Anggaran'}
                        </h3>
                        <p class="text-[11px] text-red-700 mt-0.5">
                            ${data.subtitle || 'Identifikasi kendala/risiko, dampak, dan mitigasi'}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-red-50/60 text-red-900 text-[10px] uppercase">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-center w-12">No</th>
                                <th class="px-4 py-3 font-semibold text-left" style="width: 40%;">
                                    Kendala / Risiko
                                </th>
                                <th class="px-4 py-3 font-semibold text-left" style="width: 30%;">
                                    Dampak Potensial
                                </th>
                                <th class="px-4 py-3 font-semibold text-left" style="width: 30%;">
                                    Tindak Lanjut / Mitigasi
                                </th>
                            </tr>
                        </thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // AKSELERASI PENYERAPAN ANGGARAN
    // ============================================================
    function renderAkselerasi(data) {
        const container = document.getElementById('akselerasi-container');
        if (!container) return;

        if (!data || !data.items || data.items.length === 0) {
            container.innerHTML = '';
            return;
        }

        const itemsHtml = data.items.map((item, idx) => {
            const pct = Math.min(Math.max(item.progress, 0), 100);
            const barColor = pct >= 80 ? 'from-green-600 to-green-400'
                            : pct >= 60 ? 'from-blue-600 to-blue-400'
                            : pct >= 40 ? 'from-amber-500 to-amber-400'
                            : 'from-red-500 to-red-400';
            const pctColor = pct >= 80 ? 'text-green-700'
                            : pct >= 60 ? 'text-blue-700'
                            : pct >= 40 ? 'text-amber-700'
                            : 'text-red-700';
            const badgeBg = pct >= 80 ? 'bg-green-100'
                            : pct >= 60 ? 'bg-blue-100'
                            : pct >= 40 ? 'bg-amber-100'
                            : 'bg-red-100';

            return `
                <div class="flex items-start gap-4 p-4 rounded-xl border border-gray-100 bg-white hover:bg-blue-50/30 transition-blueprint">
                    <div class="w-10 h-10 rounded-xl ${badgeBg} flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-bold ${pctColor}">${idx + 1}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-800 leading-snug">${item.kegiatan}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    ${item.jangka_waktu}
                                </p>
                            </div>
                            <span class="text-lg font-bold ${pctColor} whitespace-nowrap">${pct}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden mb-2">
                            <div class="bg-gradient-to-r ${barColor} h-full rounded-full transition-all duration-700"
                                style="width: ${pct}%"></div>
                        </div>
                        ${item.keterangan ? `
                            <div class="flex items-start gap-1.5 text-[11px] text-gray-600">
                                <i data-lucide="info" class="w-3 h-3 flex-shrink-0 mt-0.5 text-blue-500"></i>
                                <span>${item.keterangan}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = `
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 flex items-center justify-between gap-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-amber-400 flex items-center justify-center flex-shrink-0 shadow-md shadow-amber-200">
                            <i data-lucide="zap" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">
                                ${data.title || 'Akselerasi Penyerapan Anggaran'}
                            </h3>
                            <p class="text-[11px] text-gray-500 mt-0.5">Kegiatan percepatan pencapaian target anggaran</p>
                        </div>
                    </div>
                    <a href="/keuangan/akselerasi"
                        class="text-[11px] text-blue-700 hover:underline font-semibold flex items-center gap-1">
                        Kelola <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>

                <div class="p-4 space-y-2">
                    ${itemsHtml}
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // RENCANA PENGADAAN
    // ============================================================
    function renderRencanaPengadaan(data) {
        const container = document.getElementById('rencana-pengadaan-container');
        if (!container) return;

        if (!data || !data.items || data.items.length === 0) {
            container.innerHTML = '';
            return;
        }

        const itemsHtml = data.items.map((item, idx) => {
            const progressColor = item.progress >= 100 ? 'text-green-700 bg-green-100'
                                : item.progress >= 70 ? 'text-blue-700 bg-blue-100'
                                : item.progress >= 40 ? 'text-amber-700 bg-amber-100'
                                : 'text-red-700 bg-red-100';

            return `
                <tr class="border-b border-gray-100 last:border-0 hover:bg-blue-50/30">
                    <td class="px-3 py-2.5 text-center text-xs text-gray-500 font-semibold w-10">${idx + 1}</td>
                    <td class="px-3 py-2.5">
                        <p class="text-xs font-medium text-gray-800">${item.nama}</p>
                        <p class="text-[10px] text-gray-400">${item.kode || ''}</p>
                    </td>
                    <td class="px-3 py-2.5 text-right text-xs text-gray-800 font-semibold whitespace-nowrap">${rp(item.anggaran)}</td>
                    <td class="px-3 py-2.5 text-center text-[11px] text-gray-600 whitespace-nowrap">${item.waktu || '-'}</td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="inline-block text-[10px] font-bold px-2 py-1 rounded ${progressColor} whitespace-nowrap">${item.progress}%</span>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="text-[10px] text-gray-600">${item.keterangan || '-'}</span>
                    </td>
                </tr>`;
        }).join('');

        container.innerHTML = `
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 flex items-center justify-between gap-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="shopping-cart" class="w-5 h-5 text-blue-700"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Rencana Pengadaan</h3>
                            <p class="text-[11px] text-gray-500 mt-0.5">Rencana & progress paket pengadaan</p>
                        </div>
                    </div>
                    <a href="/keuangan/pengadaan"
                        class="text-[11px] text-blue-700 hover:underline font-semibold flex items-center gap-1">
                        Lihat Semua <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-blue-50/60 text-blue-900 text-[10px] uppercase">
                            <tr>
                                <th class="px-3 py-2.5 font-semibold text-center w-10">No</th>
                                <th class="px-3 py-2.5 font-semibold text-left">Jenis Pengadaan / Kontrak</th>
                                <th class="px-3 py-2.5 font-semibold text-right w-28">Anggaran</th>
                                <th class="px-3 py-2.5 font-semibold text-center w-32">Waktu Pelaksanaan</th>
                                <th class="px-3 py-2.5 font-semibold text-center w-20">Progress</th>
                                <th class="px-3 py-2.5 font-semibold text-center w-28">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                    </table>
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // BULAN INI (HIGHLIGHT)
    // ============================================================
    function renderBulanIni(b) {
        const container = document.getElementById('bulan-ini-container');
        if (!container) return;
        if (!b) {
            container.innerHTML = '';
            return;
        }
        container.innerHTML = `
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-5 mb-6 text-white shadow-lg shadow-blue-200/50">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                            <i data-lucide="calendar-days" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-xs text-blue-100 uppercase tracking-wider font-semibold">Bulan Ini - ${b.bulan_nama}</p>
                            <p class="text-2xl font-bold">${b.capaian}% Tercapai</p>
                        </div>
                    </div>
                    <div class="flex gap-6 text-sm">
                        <div>
                            <p class="text-blue-100 text-xs mb-1">Target</p>
                            <p class="font-bold">${rp(b.target)}</p>
                        </div>
                        <div>
                            <p class="text-blue-100 text-xs mb-1">Realisasi</p>
                            <p class="font-bold">${rp(b.realisasi)}</p>
                        </div>
                    </div>
                </div>
            </div>`;
        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // RINGKASAN MODUL
    // ============================================================
    function renderRingkasanModul(r) {
        const container = document.getElementById('ringkasan-container');
        if (!container) return;

        container.innerHTML = `
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="list-checks" class="w-5 h-5 text-blue-700"></i>
                Ringkasan Per Modul
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-3 text-sm">
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-500">Program</span>
                    <span class="font-semibold text-blue-700">: ${r.total_program} program</span>
                </div>
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-500">Sub-Kegiatan</span>
                    <span class="font-semibold text-indigo-700">: ${r.total_sub_kegiatan} sub</span>
                </div>
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-500">Komponen Belanja</span>
                    <span class="font-semibold text-emerald-700">: ${r.total_komponen} komponen</span>
                </div>
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-500">Target Bulanan</span>
                    <span class="font-semibold">: ${r.total_target_bulanan} bulan</span>
                </div>
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-500">Realisasi</span>
                    <span class="font-semibold">: ${r.total_realisasi} transaksi</span>
                </div>
                <div class="flex justify-between py-2 border-b border-dashed border-gray-200">
                    <span class="text-gray-500">Kendala</span>
                    <span class="font-semibold text-${r.kendala_aktif > 0 ? 'red' : 'green'}-700">: ${r.kendala_aktif} aktif</span>
                </div>
            </div>`;
        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // KENDALA DALAM PENGADAAN (3 KOLOM)
    // ============================================================
    function renderKendalaPengadaan(data) {
        const container = document.getElementById('kendala-pengadaan-container');
        if (!container) return;

        if (!data || !data.items || data.items.length === 0) {
            container.innerHTML = '';
            return;
        }

        const rowsHtml = data.items.map((item, idx) => `
            <tr class="border-b border-gray-100 last:border-0 hover:bg-red-50/30 transition">
                <td class="px-4 py-4 text-center align-top w-12">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-red-100 text-red-700 text-xs font-bold">
                        ${idx + 1}
                    </span>
                </td>
                <td class="px-4 py-4 align-top" style="width: 40%;">
                    <p class="text-xs font-semibold text-gray-800 leading-snug mb-1.5">
                        ${item.kendala}
                    </p>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="inline-block text-[10px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                            ${item.kategori}
                        </span>
                        ${item.pengadaan_kode && item.pengadaan_kode !== '-' ? `
                            <span class="inline-block text-[10px] font-mono font-bold bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">
                                ${item.pengadaan_kode}
                            </span>
                        ` : ''}
                    </div>
                </td>
                <td class="px-4 py-4 align-top" style="width: 30%;">
                    ${item.dampak ? `
                        <p class="text-xs text-gray-700 leading-relaxed">${item.dampak}</p>
                    ` : `
                        <p class="text-xs text-gray-400 italic">Belum diisi</p>
                    `}
                </td>
                <td class="px-4 py-4 align-top" style="width: 30%;">
                    ${item.tindak_lanjut ? `
                        <p class="text-xs text-gray-700 leading-relaxed">${item.tindak_lanjut}</p>
                    ` : `
                        <p class="text-xs text-gray-400 italic">Belum diisi</p>
                    `}
                </td>
            </tr>
        `).join('');

        container.innerHTML = `
            <div class="bg-white rounded-2xl border-2 border-red-200 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-red-50 to-pink-50 px-6 py-4 flex items-center gap-3 border-b border-red-200/60">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold text-red-900">
                            ${data.title || 'Kendala dalam Pengadaan'}
                        </h3>
                        <p class="text-[11px] text-red-700 mt-0.5">
                            ${data.subtitle || 'Kendala tahapan pengadaan, dampak, dan tindak lanjut'}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-red-50/60 text-red-900 text-[10px] uppercase">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-center w-12">No</th>
                                <th class="px-4 py-3 font-semibold text-left" style="width: 40%;">
                                    Kendala / Risiko
                                </th>
                                <th class="px-4 py-3 font-semibold text-left" style="width: 30%;">
                                    Dampak Potensial
                                </th>
                                <th class="px-4 py-3 font-semibold text-left" style="width: 30%;">
                                    Tindak Lanjut / Mitigasi
                                </th>
                            </tr>
                        </thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // CHANGE TAHUN
    // ============================================================
    window.changeTahun = function (y) {
        currentTahun = Number(y);
        const url = new URL(window.location);
        url.searchParams.set('tahun', currentTahun);
        window.history.pushState({}, '', url);
        loadData();
    };

    // ============================================================
    // EXPORT LAPORAN
    // ============================================================
    window.exportLaporan = function (format) {
        if (!DATA.routes.exportPdf || !DATA.routes.exportCsv) {
            toastError('Route export belum tersedia');
            console.error('[Dashboard] Route export tidak ditemukan di window.DASHBOARD_DATA.routes');
            return;
        }

        const url = format === 'PDF'
            ? `${DATA.routes.exportPdf}?tahun=${currentTahun}`
            : `${DATA.routes.exportCsv}?tahun=${currentTahun}`;

        Swal.fire({
            title: `Menyiapkan ${format}...`,
            html: `<p class="text-sm text-gray-500">Mohon tunggu, file sedang dibuat.</p>`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
            customClass: { popup: 'rounded-2xl' },
        });

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': format === 'PDF' ? 'application/pdf' : 'text/csv',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(async (res) => {
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }

            const disposition = res.headers.get('Content-Disposition') || '';
            let filename = `laporan-${currentTahun}.${format.toLowerCase()}`;
            const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
            if (match && match[1]) {
                filename = match[1].replace(/['"]/g, '');
            }

            const blob = await res.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(downloadUrl);

            Swal.close();
            toastSuccess(`${format} berhasil diunduh`);
        })
        .catch((err) => {
            Swal.close();
            console.error('[Export]', err);
            toastError(`Gagal export ${format}. Cek console untuk detail.`);
        });
    };

    // ============================================================
    // INIT
    // ============================================================
    document.addEventListener('DOMContentLoaded', loadData);
})();
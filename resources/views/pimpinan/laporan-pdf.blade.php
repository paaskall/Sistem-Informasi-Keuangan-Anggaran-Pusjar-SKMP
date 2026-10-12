<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Realisasi Keuangan {{ $tahun }}</title>
    <style>
        @page { margin: 12mm 10mm; }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        h1 {
            color: #1e40af;
            font-size: 16px;
            margin: 0 0 4px 0;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 2px;
        }
        .meta {
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            margin-bottom: 14px;
        }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .summary .card {
            display: table-cell;
            padding: 8px 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            text-align: center;
            width: 33%;
        }
        .summary .label {
            font-size: 7px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .summary .value {
            font-size: 12px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.data thead th {
            background: #1e40af;
            color: white;
            padding: 6px 5px;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.data thead th.right { text-align: right; }
        table.data thead th.center { text-align: center; }
        .month-header {
            background: #dbeafe;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 10px;
            color: #1e3a8a;
            border: 1px solid #bfdbfe;
            margin-top: 12px;
            page-break-after: avoid;
        }
        .month-summary {
            font-size: 8px;
            color: #374151;
            padding: 4px 8px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-top: none;
            margin-bottom: 4px;
        }
        .program-header {
            background: #eff6ff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 9px;
            color: #1e3a8a;
            border-left: 3px solid #1e40af;
            margin-top: 4px;
            page-break-after: avoid;
        }
        .sub-header {
            background: #eef2ff;
            padding: 3px 8px 3px 16px;
            font-size: 8px;
            color: #4338ca;
            font-weight: bold;
            border-left: 2px solid #4338ca;
            margin-top: 2px;
            page-break-after: avoid;
        }
        table.data tbody td {
            padding: 4px 5px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 8px;
            vertical-align: top;
        }
        table.data tbody td.right {
            text-align: right;
            font-family: 'Courier New', monospace;
            white-space: nowrap;
        }
        table.data tbody td.center {
            text-align: center;
            white-space: nowrap;
        }
        .komponen-row {
            padding-left: 28px !important;
        }
        .cap-100 { color: #059669; font-weight: bold; }
        .cap-70 { color: #1e40af; font-weight: bold; }
        .cap-40 { color: #d97706; font-weight: bold; }
        .cap-low { color: #dc2626; font-weight: bold; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 7px;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
        }
    </style>
</head>
<body>

<h1>Laporan Realisasi Keuangan Tahun Anggaran {{ $tahun }}</h1>
<div class="subtitle">Sistem Informasi Keuangan &amp; Anggaran Pusjar SKMP</div>
<div class="meta">Dicetak: {{ $printed_at }}</div>

{{-- SUMMARY --}}
<table class="summary">
    <tr>
        <td class="card">
            <div class="label">Total Target</div>
            <div class="value" style="color:#1e40af;">Rp{{ number_format($total_target, 0, ',', '.') }}</div>
        </td>
        <td style="width: 8px;"></td>
        <td class="card">
            <div class="label">Total Realisasi</div>
            <div class="value" style="color:#047857;">Rp{{ number_format($total_realisasi, 0, ',', '.') }}</div>
        </td>
        <td style="width: 8px;"></td>
        <td class="card">
            <div class="label">Capaian</div>
            <div class="value" style="color:#d97706;">{{ $capaian }}%</div>
        </td>
    </tr>
</table>

{{-- DATA PER BULAN --}}
@php
    $capClass = function ($c) {
        if ($c >= 100) return 'cap-100';
        if ($c >= 70) return 'cap-70';
        if ($c >= 40) return 'cap-40';
        return 'cap-low';
    };
@endphp

@forelse ($bulan_data as $bulan)
    <div class="month-header">
        {{ $bulan['bulan_nama'] }} {{ $tahun }}
    </div>
    <div class="month-summary">
        Target: <strong style="color:#1e40af;">Rp{{ number_format($bulan['target'], 0, ',', '.') }}</strong> &nbsp;|&nbsp;
        Realisasi: <strong style="color:#047857;">Rp{{ number_format($bulan['realisasi'], 0, ',', '.') }}</strong> &nbsp;|&nbsp;
        Capaian: <strong class="{{ $capClass($bulan['capaian']) }}">{{ $bulan['capaian'] }}%</strong>
    </div>

    @php
        // Group by program
        $grouped = [];
        foreach ($bulan['details'] as $d) {
            $progId = $d['program_id'];
            if (!isset($grouped[$progId])) {
                $grouped[$progId] = ['program' => $d['program'], 'subs' => [], 'target' => 0, 'realisasi' => 0];
            }
            $subId = $d['sub_kegiatan_id'] ?? 0;
            if (!isset($grouped[$progId]['subs'][$subId])) {
                $grouped[$progId]['subs'][$subId] = ['sub_kegiatan' => $d['sub_kegiatan'], 'komponens' => [], 'target' => 0, 'realisasi' => 0];
            }
            $grouped[$progId]['subs'][$subId]['komponens'][] = $d;
            $grouped[$progId]['subs'][$subId]['target'] += $d['target'];
            $grouped[$progId]['subs'][$subId]['realisasi'] += $d['realisasi'];
            $grouped[$progId]['target'] += $d['target'];
            $grouped[$progId]['realisasi'] += $d['realisasi'];
        }
    @endphp

    @foreach ($grouped as $g)
        @php
            $progCap = $g['target'] > 0 ? (int) round(($g['realisasi'] / $g['target']) * 100) : 0;
        @endphp
        <div class="program-header">
            [{{ $g['program']['kode'] ?? '???' }}] {{ $g['program']['nama'] ?? 'Tidak diketahui' }}
            &nbsp;&mdash;&nbsp; T: Rp{{ number_format($g['target'], 0, ',', '.') }}
            &nbsp;|&nbsp; R: Rp{{ number_format($g['realisasi'], 0, ',', '.') }}
            &nbsp;|&nbsp; Cap: <span class="{{ $capClass($progCap) }}">{{ $progCap }}%</span>
        </div>

        @foreach ($g['subs'] as $s)
            @php
                $subCap = $s['target'] > 0 ? (int) round(($s['realisasi'] / $s['target']) * 100) : 0;
            @endphp
            <div class="sub-header">
                [{{ $s['sub_kegiatan']['kode'] ?? '-' }}] {{ $s['sub_kegiatan']['nama'] ?? 'Tanpa Sub-Kegiatan' }}
                &nbsp;&mdash;&nbsp; T: Rp{{ number_format($s['target'], 0, ',', '.') }}
                &nbsp;|&nbsp; R: Rp{{ number_format($s['realisasi'], 0, ',', '.') }}
                &nbsp;|&nbsp; Cap: <span class="{{ $capClass($subCap) }}">{{ $subCap }}%</span>
            </div>

            <table class="data">
                <thead>
                    <tr>
                        <th>Komponen Belanja</th>
                        <th class="right" style="width: 22%;">Target</th>
                        <th class="right" style="width: 22%;">Realisasi</th>
                        <th class="center" style="width: 12%;">Cap</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($s['komponens'] as $d)
                        <tr>
                            <td class="komponen-row">
                                <span style="color:#059669; font-family:monospace; font-weight:bold;">{{ $d['komponen_belanja']['kode'] ?? '-' }}</span>
                                &nbsp;{{ $d['komponen_belanja']['nama'] ?? '-' }}
                            </td>
                            <td class="right" style="color:#1e40af;">Rp{{ number_format($d['target'], 0, ',', '.') }}</td>
                            <td class="right" style="color:#047857;">Rp{{ number_format($d['realisasi'], 0, ',', '.') }}</td>
                            <td class="center {{ $capClass($d['capaian']) }}">{{ $d['capaian'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @endforeach
@empty
    <p style="text-align:center; color:#9ca3af; padding:30px;">Tidak ada data untuk tahun {{ $tahun }}</p>
@endforelse

<div class="footer">
    Dokumen ini digenerate otomatis oleh SIKAP - Sistem Informasi Keuangan &amp; Anggaran Pusjar SKMP
</div>

</body>
</html>
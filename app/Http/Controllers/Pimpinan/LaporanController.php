<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Realisasi;
use App\Models\Target;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        if ($tahun < 2020 || $tahun > 2099) {
            $tahun = (int) date('Y');
        }

        return view('pimpinan.laporan', [
            'tahun' => $tahun,
            'tahunOptions' => [2026, 2025, 2024],
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        $data = $this->buildData($tahun);

        return response()->json([
            'ok' => true,
            'tahun' => $tahun,
            'total_target' => $data['total_target'],
            'total_realisasi' => $data['total_realisasi'],
            'capaian' => $data['capaian'],
            'bulan_data' => $data['bulan_data'],
        ]);
    }

    /**
     * Export PDF.
     */
    public function exportPdf(Request $request): Response
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        $data = $this->buildData($tahun);

        $pdf = Pdf::loadView('pimpinan.laporan-pdf', [
            'tahun' => $tahun,
            'total_target' => $data['total_target'],
            'total_realisasi' => $data['total_realisasi'],
            'capaian' => $data['capaian'],
            'bulan_data' => $data['bulan_data'],
            'printed_at' => now()->translatedFormat('d F Y H:i'),
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        $filename = "laporan-realisasi-{$tahun}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export CSV.
     */
    public function exportCsv(Request $request): Response|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $tahun = (int) $request->get('tahun', date('Y'));
        $data = $this->buildData($tahun);

        $filename = "laporan-realisasi-{$tahun}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data, $tahun) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 agar Excel bisa baca karakter Indonesia
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header info
            fputcsv($out, ["Laporan Realisasi Keuangan Tahun {$tahun}"]);
            fputcsv($out, ["Dicetak: " . now()->translatedFormat('d F Y H:i')]);
            fputcsv($out, []);

            // Summary
            fputcsv($out, ['Total Target', $data['total_target']]);
            fputcsv($out, ['Total Realisasi', $data['total_realisasi']]);
            fputcsv($out, ['Capaian', $data['capaian'] . '%']);
            fputcsv($out, []);

            // Header tabel bulanan
            fputcsv($out, ['Bulan', 'Program', 'Sub-Kegiatan', 'Komponen Belanja', 'Target', 'Realisasi', 'Capaian (%)']);

            // Rows
            foreach ($data['bulan_data'] as $bulan) {
                $grouped = [];
                foreach ($bulan['details'] as $d) {
                    $progId = $d['program_id'];
                    if (!isset($grouped[$progId])) {
                        $grouped[$progId] = [
                            'program' => $d['program'],
                            'subs' => [],
                        ];
                    }
                    $subId = $d['sub_kegiatan_id'] ?? 0;
                    if (!isset($grouped[$progId]['subs'][$subId])) {
                        $grouped[$progId]['subs'][$subId] = [
                            'sub_kegiatan' => $d['sub_kegiatan'],
                            'komponens' => [],
                        ];
                    }
                    $grouped[$progId]['subs'][$subId]['komponens'][] = $d;
                }

                foreach ($grouped as $g) {
                    foreach ($g['subs'] as $s) {
                        foreach ($s['komponens'] as $d) {
                            fputcsv($out, [
                                $bulan['bulan_nama'],
                                $g['program']['nama'] ?? '-',
                                $s['sub_kegiatan']['nama'] ?? '-',
                                $d['komponen_belanja']['nama'] ?? '-',
                                $d['target'],
                                $d['realisasi'],
                                $d['capaian'],
                            ]);
                        }
                    }
                }
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build data (dipakai untuk data, PDF, CSV).
     */
    protected function buildData(int $tahun): array
    {
        $targets = Target::with([
            'details.program:id,kode,nama',
            'details.subKegiatan:id,kode,nama',
            'details.komponenBelanja:id,kode,nama',
        ])
            ->where('tahun', $tahun)
            ->orderBy('bulan')
            ->get();

        $realisasis = Realisasi::with('details')
            ->where('tahun', $tahun)
            ->get()
            ->keyBy('target_id');

        $totalTarget = $targets->sum(fn ($t) => $t->total_nominal);
        $totalRealisasi = $realisasis->sum('nominal');
        $capaian = $totalTarget > 0 ? (int) round(($totalRealisasi / $totalTarget) * 100) : 0;

        $bulanData = $targets->map(function ($t) use ($realisasis) {
            $realisasi = $realisasis->get($t->id);

            $realMap = [];
            if ($realisasi) {
                foreach ($realisasi->details as $d) {
                    $key = $d->program_id . '-' . ($d->sub_kegiatan_id ?? 0) . '-' . $d->komponen_belanja_id;
                    $realMap[$key] = ($realMap[$key] ?? 0) + (float) $d->nominal;
                }
            }

            $details = $t->details->map(function ($d) use ($realMap) {
                $key = $d->program_id . '-' . ($d->sub_kegiatan_id ?? 0) . '-' . $d->komponen_belanja_id;
                $realNominal = $realMap[$key] ?? 0;
                return [
                    'program_id' => $d->program_id,
                    'program' => $d->program ? ['kode' => $d->program->kode, 'nama' => $d->program->nama] : null,
                    'sub_kegiatan_id' => $d->sub_kegiatan_id,
                    'sub_kegiatan' => $d->subKegiatan ? ['kode' => $d->subKegiatan->kode, 'nama' => $d->subKegiatan->nama] : null,
                    'komponen_belanja_id' => $d->komponen_belanja_id,
                    'komponen_belanja' => $d->komponenBelanja ? ['kode' => $d->komponenBelanja->kode, 'nama' => $d->komponenBelanja->nama] : null,
                    'target' => (float) $d->nominal,
                    'realisasi' => $realNominal,
                    'capaian' => $d->nominal > 0 ? (int) round(($realNominal / $d->nominal) * 100) : 0,
                ];
            });

            $totalR = (float) ($realisasi->nominal ?? 0);
            $totalT = (float) $t->total_nominal;

            return [
                'id' => $t->id,
                'bulan' => $t->bulan,
                'bulan_nama' => $t->bulan_nama,
                'target' => $totalT,
                'realisasi' => $totalR,
                'capaian' => $totalT > 0 ? (int) round(($totalR / $totalT) * 100) : 0,
                'details' => $details,
            ];
        })->toArray();

        return [
            'total_target' => (float) $totalTarget,
            'total_realisasi' => (float) $totalRealisasi,
            'capaian' => $capaian,
            'bulan_data' => $bulanData,
        ];
    }
}
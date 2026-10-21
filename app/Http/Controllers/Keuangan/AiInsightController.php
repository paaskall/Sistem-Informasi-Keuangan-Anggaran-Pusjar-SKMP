<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use App\Services\ReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AiInsightController extends Controller
{
    public function analyze(
        Request $request,
        ReminderService $reminderService,
        GeminiService $geminiService
    ): JsonResponse {
        try {
            $reminders = $reminderService->getReminders();

            if (empty($reminders['realisasi']['items'])) {
                return response()->json([
                    'ok' => true, 
                    'insight' => null, 
                    'message' => 'Tidak ada komponen yang perlu dianalisa.'
                ]);
            }

            // ⭐ Jika user minta refresh, hapus cache
            if ($request->boolean('refresh')) {
                $tahun = $reminders['periode']['tahun'] ?? date('Y');
                $bulan = $reminders['periode']['bulan'] ?? date('n');
                \Illuminate\Support\Facades\Cache::forget("gemini_analysis_{$tahun}_{$bulan}");
            }

            $insight = $geminiService->analyzeRealisasi($reminders);

            return response()->json([
                'ok' => true,
                'insight' => $insight,
                'message' => $insight ? 'Analisa AI berhasil.' : 'Analisa AI gagal diproses oleh Gemini.',
            ]);
        } catch (\Throwable $e) {
            \Log::error('[AI Insight] Controller error: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use App\Services\ReminderService;
use Illuminate\Http\JsonResponse;

class AiInsightController extends Controller
{
    public function analyze(
        ReminderService $reminderService,
        GeminiService $geminiService
    ): JsonResponse {
        $reminders = $reminderService->getReminders();

        if (empty($reminders['realisasi']['items'])) {
            return response()->json([
                'ok' => true,
                'insight' => null,
                'message' => 'Tidak ada komponen yang perlu dianalisa.',
            ]);
        }

        $insight = $geminiService->analyzeRealisasi($reminders);

        return response()->json([
            'ok' => true,
            'insight' => $insight,
            'message' => $insight
                ? 'Analisa AI berhasil.'
                : 'Analisa AI tidak tersedia. Cek konfigurasi API key.',
        ]);
    }
}
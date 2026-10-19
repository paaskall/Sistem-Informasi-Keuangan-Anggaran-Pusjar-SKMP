<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function analyzeRealisasi(array $reminders): ?array
    {
        $items = $reminders['realisasi']['items'] ?? [];

        if (empty($items)) {
            return null;
        }

        $cacheKey = 'gemini_analysis_' . md5(json_encode($reminders));
        return Cache::remember($cacheKey, config('gemini.cache_ttl', 3600), function () use ($reminders) {
            return $this->callGemini($reminders);
        });
    }

    protected function callGemini(array $reminders): ?array
    {
        $apiKey = config('gemini.api_key');
        if (empty($apiKey)) {
            Log::warning('[Gemini] API key tidak di-set');
            return null;
        }

        $prompt = $this->buildPrompt($reminders);
        $model = config('gemini.model', 'gemini-3.8-flash');
        $url = config('gemini.base_url') . "/models/{$model}:generateContent?key={$apiKey}";

        try {
            $response = Http::timeout(config('gemini.timeout', 30))
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 3000,   // ⬅️ NAIKKAN dari 800 → 3000
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('[Gemini] API error', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                ]);
                return null;
            }

            $json = $response->json();
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($text)) {
                Log::warning('[Gemini] Response kosong');
                return null;
            }

            // Log raw response untuk debug
            Log::info('[Gemini] Raw response', ['text' => substr($text, 0, 500)]);

            // ⬇️ Ekstrak JSON dari text (robust)
            $parsed = $this->extractJson($text);

            if (!$parsed || !isset($parsed['prioritas_utama'])) {
                Log::warning('[Gemini] Response tidak valid', ['text' => substr($text, 0, 500)]);
                return null;
            }

            return $parsed;
        } catch (\Exception $e) {
            Log::error('[Gemini] Exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Ekstrak JSON dari teks — robust terhadap markdown, prefix, suffix.
     */
    protected function extractJson(string $text): ?array
    {
        // Hapus markdown code block
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        // Coba langsung decode
        $decoded = json_decode($text, true);
        if (is_array($decoded)) return $decoded;

        // Kalau gagal, cari kurung kurawal pertama dan terakhir
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $jsonPart = substr($text, $start, $end - $start + 1);
            $decoded = json_decode($jsonPart, true);
            if (is_array($decoded)) return $decoded;
        }

        return null;
    }

    /**
     * Bangun prompt — lebih singkat & jelas.
     */
    protected function buildPrompt(array $reminders): string
    {
        $bulan = (string) ($reminders['periode']['bulan_nama'] ?? 'bulan ini');
        $tahun = (string) ($reminders['periode']['tahun'] ?? date('Y'));
        $hariIni = (int) date('j');
        $hariTerakhir = (int) date('t');
        $sisaHari = $hariTerakhir - $hariIni;

        $rawItems = $reminders['realisasi']['items'] ?? [];
        if ($rawItems instanceof \Illuminate\Support\Collection) {
            $rawItems = $rawItems->toArray();
        }

        $lines = [];
        $i = 1;
        foreach ($rawItems as $item) {
            if (is_object($item)) $item = (array) $item;
            if (!is_array($item)) continue;

            $nama = isset($item['nama']) && is_string($item['nama']) ? $item['nama'] : ('Komponen ' . $i);
            $target = isset($item['target']) && is_numeric($item['target']) ? (float) $item['target'] : 0;
            $realisasi = isset($item['realisasi']) && is_numeric($item['realisasi']) ? (float) $item['realisasi'] : 0;
            $capaian = isset($item['capaian']) && is_numeric($item['capaian']) ? (int) $item['capaian'] : 0;

            $sisa = $target - $realisasi;

            $lines[] = sprintf(
                "%d. %s | Target: Rp%s | Realisasi: Rp%s | Capaian: %d%% | Sisa: Rp%s",
                $i,
                $nama,
                number_format($target, 0, ',', '.'),
                number_format($realisasi, 0, ',', '.'),
                $capaian,
                number_format($sisa, 0, ',', '.')
            );
            $i++;
        }

        $itemsText = implode("\n", $lines);
        $totalItems = $i - 1;

        return <<<PROMPT
Anda analis keuangan. Analisa komponen mana yang PALING MUNGKIN direalisasikan sebelum akhir bulan.

KONTEKS: Periode {$bulan} {$tahun}, hari ini tanggal {$hariIni}, sisa {$sisaHari} hari, total {$totalItems} komponen belum 100%.

DATA:
{$itemsText}

OUTPUT HARUS JSON VALID, SINGKAT, TANPA markdown:
{"prioritas_utama":"nama","alasan":"1 kalimat singkat","urutan_prioritas":["nama1","nama2"],"strategi":"1 kalimat saran","tingkat_keyakinan":"tinggi","estimasi_selesai":"10 hari"}

ATURAN: Capaian >70% = prioritas. Sisa hari <7 = pilih sisa kecil.
PROMPT;
    }
}
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
        if (empty($items)) return null;

        $tahun = $reminders['periode']['tahun'] ?? date('Y');
        $bulan = $reminders['periode']['bulan'] ?? date('n');
        $cacheKey = "gemini_analysis_{$tahun}_{$bulan}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->callGemini($reminders);

        if ($result !== null) {
            Cache::put($cacheKey, $result, now()->addDays(30));
        }

        return $result;
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
            // ⭐ KONFIGURASI cURL YANG LEBIH TOLERAN
            $response = Http::withoutVerifying()
                ->withOptions([
                    'connect_timeout' => 15,
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Paksa IPv4
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => false,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, // Paksa HTTP/1.1
                        CURLOPT_FORBID_REUSE => true,
                        CURLOPT_FRESH_CONNECT => true,
                    ],
                ])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Connection' => 'close',
                ])
                ->timeout(120) // Naikkan timeout total ke 120 detik
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
                        'maxOutputTokens' => 3000,
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

    protected function extractJson(string $text): ?array
    {
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/^```\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        $text = trim($text);

        $decoded = json_decode($text, true);
        if (is_array($decoded)) return $decoded;

        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $jsonPart = substr($text, $start, $end - $start + 1);
            $decoded = json_decode($jsonPart, true);
            if (is_array($decoded)) return $decoded;
        }

        return null;
    }

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
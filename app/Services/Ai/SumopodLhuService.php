<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SumopodLhuService
{
    public function generateKesimpulanSaran(array $context): array
    {
        $apiKey = trim((string) config('services.sumopod.api_key'));
        $baseUrl = rtrim((string) config('services.sumopod.base_url'), '/');
        $model = trim((string) config('services.sumopod.model', 'gpt-4o-mini'));
        $timeout = (int) config('services.sumopod.timeout', 30);

        if ($apiKey === '') {
            throw new RuntimeException('Konfigurasi SUMOPOD_API_KEY belum diatur.');
        }
        if ($baseUrl === '') {
            throw new RuntimeException('Konfigurasi SUMOPOD_BASE_URL belum diatur.');
        }

        $messages = [
            [
                'role' => 'system',
                'content' => 'Anda analis K3 yang menyusun narasi LHU resmi dalam Bahasa Indonesia. '
                    . 'Tugas Anda adalah membuat kesimpulan dan saran yang rapi, formal, berbasis data uji, dan mudah dibaca di textarea multi-baris. '
                    . 'Gunakan NAB yang tercantum pada tabel bila tersedia. Jika kolom NAB terisi, prioritaskan nilai itu sebagai pembanding utama. '
                    . 'Gunakan NAB nasional yang relevan hanya jika kolom NAB tidak tersedia atau kosong, lalu bandingkan langsung dengan hasil uji. '
                    . 'Wajib output JSON valid dengan dua properti string: kesimpulan dan saran. '
                    . 'Aturan format kesimpulan: '
                    . '1) Gunakan line break nyata (\n) agar setiap butir tampil di baris terpisah. '
                    . '2) Jika parameter lebih dari satu, buat list bernomor, satu parameter per baris. '
                    . '3) Pada setiap butir, tuliskan minimal: nama parameter, hasil uji + satuan, NAB + satuan atau keterangan "NAB tidak tersedia", lalu narasi statusnya seperti "di bawah NAB", "mendekati NAB", atau "melebihi NAB". '
                    . '4) Setelah daftar parameter, tambahkan 1 paragraf ringkas yang merangkum kondisi lokasi secara umum. '
                    . '5) Jangan menulis kalimat generik seperti "perlu perhatian lebih lanjut" tanpa menyebut alasannya. '
                    . '6) Mode ini untuk testing internal. Jika NAB tidak tersedia atau Anda tidak yakin, Anda boleh membuat estimasi NAB dari pengetahuan model agar narasi tetap terbentuk. '
                    . '7) Jika menggunakan estimasi, wajib tulis secara eksplisit dengan format seperti "Estimasi NAB (AI, non-resmi): ...". Jangan menulisnya seolah-olah NAB resmi. '
                    . '8) Status seperti "di bawah NAB", "mendekati NAB", atau "melebihi NAB" boleh digunakan terhadap estimasi tersebut, tetapi narasinya juga harus menyebut bahwa pembanding yang dipakai adalah estimasi AI non-resmi. '
                    . '9) Pada paragraf ringkasan akhir, jika pembanding yang dipakai adalah estimasi, tegaskan bahwa evaluasi terhadap NAB masih bersifat simulasi/testing dan bukan rujukan regulasi resmi. '
                    . '10) Jangan gunakan markdown, tabel, atau bullet selain penomoran biasa. '
                    . 'Aturan format saran: buat singkat, spesifik, dan relevan dengan parameter yang paling perlu tindak lanjut.',
            ],
            [
                'role' => 'user',
                'content' => "Buat kesimpulan dan saran dari data LHU berikut.\n"
                    . "Fokuskan kesimpulan pada kondisi lokasi, lalu uraikan per parameter secara terpisah dengan format multi-baris yang rapi.\n"
                    . "Setiap parameter harus memuat: hasil uji, NAB, dan interpretasi naratif terhadap NAB.\n"
                    . "Jika pada tabel sudah ada kolom NAB yang terisi user, gunakan nilai itu dan jangan diganti estimasi AI.\n"
                    . "Jika NAB tidak tersedia atau Anda tidak yakin, untuk kebutuhan testing Anda boleh memakai estimasi NAB dari pengetahuan model, tetapi harus diberi label jelas sebagai estimasi AI non-resmi.\n"
                    . "Data JSON:\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ],
        ];

        $json = $this->requestCompletion($baseUrl, $apiKey, $timeout, $model, $messages);
        $content = (string) data_get($json, 'choices.0.message.content', '');
        if ($content === '') {
            throw new RuntimeException('Respons Sumopod kosong.');
        }

        $parsed = $this->parseContent($content);
        if ($parsed['kesimpulan'] === '' || $parsed['saran'] === '') {
            $repairMessages = [
                [
                    'role' => 'system',
                    'content' => 'Ubah teks menjadi JSON valid dengan dua properti string: kesimpulan dan saran. '
                        . 'Jangan tambahkan penjelasan lain.',
                ],
                [
                    'role' => 'user',
                    'content' => "Teks:\n" . $content,
                ],
            ];
            $repairJson = $this->requestCompletion($baseUrl, $apiKey, $timeout, $model, $repairMessages, 0, 300);
            $repairContent = (string) data_get($repairJson, 'choices.0.message.content', '');
            if ($repairContent !== '') {
                $parsed = $this->parseContent($repairContent);
                $json = $repairJson;
            }
        }

        if ($parsed['kesimpulan'] === '' || $parsed['saran'] === '') {
            Log::warning('Sumopod response format invalid for LHU summary', [
                'model' => $model,
                'content_preview' => mb_substr($content, 0, 600),
            ]);
            throw new RuntimeException('Respons Sumopod tidak memenuhi format kesimpulan/saran.');
        }

        return [
            'kesimpulan' => $parsed['kesimpulan'],
            'saran' => $parsed['saran'],
            'model' => (string) data_get($json, 'model', $model),
        ];
    }

    private function requestCompletion(
        string $baseUrl,
        string $apiKey,
        int $timeout,
        string $model,
        array $messages,
        float $temperature = 0.2,
        int $maxTokens = 500
    ): array {
        $response = Http::acceptJson()
            ->asJson()
            ->withToken($apiKey)
            ->timeout(max(5, $timeout))
            ->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Permintaan ke Sumopod gagal. HTTP ' . $response->status() . '.');
        }

        return (array) $response->json();
    }

    private function parseContent(string $content): array
    {
        $trimmed = trim($content);
        $jsonCandidate = $this->extractJsonCandidate($trimmed);
        if ($jsonCandidate !== null) {
            $decoded = json_decode($jsonCandidate, true);
        } else {
            $decoded = json_decode($trimmed, true);
        }
        if (is_array($decoded)) {
            return [
                'kesimpulan' => trim((string) ($decoded['kesimpulan'] ?? '')),
                'saran' => trim((string) ($decoded['saran'] ?? '')),
            ];
        }

        $kesimpulan = $this->extractSection($trimmed, 'kesimpulan', 'saran');
        $saran = $this->extractSection($trimmed, 'saran', null);

        return [
            'kesimpulan' => $kesimpulan,
            'saran' => $saran,
        ];
    }

    private function extractJsonCandidate(string $text): ?string
    {
        if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/i', $text, $m)) {
            return trim((string) $m[1]);
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return trim(substr($text, $start, $end - $start + 1));
    }

    private function extractSection(string $text, string $section, ?string $nextSection): string
    {
        $sectionPattern = preg_quote($section, '/');
        $nextPattern = $nextSection ? preg_quote($nextSection, '/') : null;
        $pattern = $nextPattern
            ? '/(?:^|\n)\s*(?:\d+[\.\)]\s*)?\**' . $sectionPattern . '\**\s*[:\-]?\s*(.+?)(?=(?:\n\s*(?:\d+[\.\)]\s*)?\**' . $nextPattern . '\**\s*[:\-]?)|\z)/is'
            : '/(?:^|\n)\s*(?:\d+[\.\)]\s*)?\**' . $sectionPattern . '\**\s*[:\-]?\s*(.+)\z/is';

        if (!preg_match($pattern, $text, $match)) {
            return '';
        }

        $value = trim((string) ($match[1] ?? ''));
        $value = preg_replace('/^["\']|["\']$/', '', $value);
        return trim((string) $value);
    }
}

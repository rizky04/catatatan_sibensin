<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiScanController extends Controller
{
    public function scan(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'receipt' => 'required|image|max:5120', // maksimal 5MB
        ]);

        try {
            $image = $request->file('receipt');
            $base64Image = base64_encode(file_get_contents($image->getPathname()));
            $mimeType = $image->getMimeType();

            // 2. Prompt yang lebih baik untuk Gemini 2.0 Flash
            $prompt = "You are an expert at reading Indonesian gas station (SPBU) receipts.

Extract the following information from this receipt image and return ONLY valid JSON.
Do not include any explanation, markdown, or additional text.

Required format:
{
    \"date\": \"YYYY-MM-DD\",
    \"location_name\": \"SPBU location/city name\",
    \"fuel_type\": \"Pertalite or Pertamax or Pertamax Turbo or Dexlite or Pertamina Dex\",
    \"price_per_liter\": 10000,
    \"liters\": 45.5,
    \"total_price\": 455000
}

Rules:
- Date must be in YYYY-MM-DD format (e.g., 2024-01-15)
- price_per_liter and total_price are integers (no decimals)
- liters can be decimal (use dot, e.g., 45.50)
- If a field cannot be found, use null
- For fuel_type, match exactly with the options above
- Look carefully for numbers on the receipt

Return ONLY the JSON object, nothing else.";

            $apiKey = env('GEMINI_API_KEY');

            if (!$apiKey) {
                throw new \Exception("GEMINI_API_KEY tidak ditemukan. Silakan tambahkan di file .env");
            }

            // 3. Gunakan model GEMINI 2.0 FLASH (GRATIS)
            $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key={$apiKey}";

            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Image
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,      // Rendah untuk hasil konsisten
                    'topP' => 0.95,
                    'topK' => 40,
                    'maxOutputTokens' => 1024,
                ]
            ]);

            // 4. Handle error response
            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? $response->body();
                Log::error('Gemini API Error: ' . $errorMessage);

                // Coba dengan model cadangan jika perlu
                return $this->tryBackupModel($base64Image, $mimeType, $prompt, $apiKey);
            }

            // 5. Parse response
            $result = $response->json();
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($text)) {
                throw new \Exception("Tidak ada response dari AI");
            }

            // 6. Bersihkan response dari markdown
            $cleanJson = preg_replace('/```json\s*|\s*```/', '', $text);
            $cleanJson = trim($cleanJson);

            // Cari JSON jika ada teks lain
            if (!str_starts_with($cleanJson, '{')) {
                preg_match('/\{[^{}]*\{(?:[^{}]*\{[^{}]*\}[^{}]*)*[^{}]*\}[^{}]*\}/s', $cleanJson, $matches);
                if (isset($matches[0])) {
                    $cleanJson = $matches[0];
                } else {
                    preg_match('/\{.*\}/s', $cleanJson, $matches);
                    if (isset($matches[0])) {
                        $cleanJson = $matches[0];
                    }
                }
            }

            $data = json_decode($cleanJson, true);

            // 7. Validasi JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON Parse Error: ' . json_last_error_msg());
                Log::error('Raw text: ' . $text);
                Log::error('Cleaned JSON: ' . $cleanJson);
                throw new \Exception("Gagal parsing data struk: " . json_last_error_msg());
            }

            // 8. Validasi data yang diekstrak
            $requiredFields = ['date', 'location_name', 'fuel_type', 'price_per_liter', 'liters', 'total_price'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    $data[$field] = null;
                }
            }

            // 9. Konversi tipe data yang benar
            if ($data['price_per_liter'] !== null) {
                $data['price_per_liter'] = (int) $data['price_per_liter'];
            }
            if ($data['liters'] !== null) {
                $data['liters'] = (float) $data['liters'];
            }
            if ($data['total_price'] !== null) {
                $data['total_price'] = (int) $data['total_price'];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('AI Scan Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal memproses struk: ' . $e->getMessage()
            ], 500);
        }
    }

    private function tryBackupModel($base64Image, $mimeType, $prompt, $apiKey)
    {
        // Coba dengan Gemini 2.5 Flash sebagai backup
        $backupModels = [
            'gemini-2.5-flash',
            'gemini-2.0-flash-lite'
        ];

        $lastError = null;

        foreach ($backupModels as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}";

                $response = Http::timeout(30)->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $base64Image
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 1024,
                    ]
                ]);

                if ($response->successful()) {
                    Log::info("Using backup model: {$model}");
                    return $response->json();
                }

                $lastError = $response->body();
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        throw new \Exception("Semua model AI gagal. Error terakhir: " . $lastError);
    }
}

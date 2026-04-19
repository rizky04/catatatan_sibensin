<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiScanController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'receipt' => 'required|image|max:10120',
        ]);

        try {
            $image = $request->file('receipt');
            $base64Image = base64_encode(file_get_contents($image->getPathname()));
            $mimeType = $image->getMimeType();

            // Prompt khusus untuk struk Indonesia
            $prompt = "Anda adalah AI khusus untuk membaca struk SPBU (Pertamina) di Indonesia.

Analisis gambar struk berikut dan ekstrak data dengan format JSON yang valid.

Format struk yang umum:
- Tanggal: Biasanya format DD/MM/YYYY atau DD-MM-YYYY
- Nama produk: PERTALITE, PERTAMAX, PERTAMAX TURBO, DEXLITE, PERTAMINA DEX
- Harga/Liter: Angka setelah 'Harga/Liter : Rp.'
- Volume: Angka setelah 'Volume : (L)' atau 'Liter :'
- Total Harga: Angka setelah 'Total Harga : Rp.'

Contoh struk:
PERTAMINA
SPBU RTA MILONO
Jl. RTA MILONO PALANGKA RAYA
Waktu : 24/06/2023 12:42:37
Nama Produk : PERTALITE
Harga/Liter : Rp. 10,000
Volume : (L) 3.00
Total Harga : Rp. 30,000

KEMBALIKAN HANYA JSON VALID tanpa teks lain, tanpa markdown, tanpa penjelasan.

Format JSON yang diminta:
{
    \"date\": \"YYYY-MM-DD\",
    \"location_name\": \"Nama SPBU dan lokasi\",
    \"fuel_type\": \"Pertalite/Pertamax/Pertamax Turbo/Dexlite/Pertamina Dex\",
    \"price_per_liter\": 10000,
    \"liters\": 3.00,
    \"total_price\": 30000
}

Aturan penting:
1. date: Konversi dari format DD/MM/YYYY ke YYYY-MM-DD (contoh: 24/06/2023 -> 2023-06-24)
2. price_per_liter: Hanya angka tanpa titik atau koma (contoh: 10,000 -> 10000)
3. liters: Angka volume, gunakan titik untuk desimal (contoh: 3.00)
4. total_price: Hanya angka tanpa titik atau koma (contoh: 30,000 -> 30000)
5. location_name: Gabungkan nama SPBU dan alamatnya
6. Jika ada data yang tidak ditemukan, gunakan null

KEMBALIKAN HANYA JSON!";

            $apiKey = env('GEMINI_API_KEY');

            if (!$apiKey) {
                throw new \Exception("GEMINI_API_KEY tidak ditemukan");
            }

            // Gunakan model yang support vision
            $models = ['gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-2.0-flash-lite'];
            $result = null;
            $lastError = null;

            foreach ($models as $model) {
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
                            'topP' => 0.95,
                            'maxOutputTokens' => 1024,
                        ]
                    ]);

                    if ($response->successful()) {
                        $result = $response->json();
                        Log::info("Success using model: {$model}");
                        break;
                    }

                    $lastError = $response->body();
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                }
            }

            if (!$result) {
                throw new \Exception("Gagal memproses dengan semua model: " . $lastError);
            }

            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($text)) {
                throw new \Exception("Tidak ada response dari AI");
            }

            // Bersihkan response
            $cleanJson = preg_replace('/```json\s*|\s*```/', '', $text);
            $cleanJson = trim($cleanJson);

            // Cari JSON object
            if (!str_starts_with($cleanJson, '{')) {
                preg_match('/\{[^{}]*\}/s', $cleanJson, $matches);
                if (isset($matches[0])) {
                    $cleanJson = $matches[0];
                }
            }

            $data = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON Parse Error: ' . json_last_error_msg());
                Log::error('Raw text: ' . $text);

                // Fallback: ekstrak manual dari text
                $data = $this->manualExtractFromText($text);
            }

            // Validasi dan konversi data
            $data = $this->validateAndConvertData($data);

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

    private function manualExtractFromText($text)
    {
        $data = [
            'date' => null,
            'location_name' => null,
            'fuel_type' => null,
            'price_per_liter' => null,
            'liters' => null,
            'total_price' => null
        ];

        // Extract tanggal (format: DD/MM/YYYY atau DD-MM-YYYY)
        if (preg_match('/(\d{2})[\/\-](\d{2})[\/\-](\d{4})/', $text, $matches)) {
            $data['date'] = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }

        // Extract lokasi (ambil baris setelah PERTAMINA)
        if (preg_match('/PERTAMINA\s+([^\n]+)\s+([^\n]+)/', $text, $matches)) {
            $data['location_name'] = trim($matches[1] . ' ' . $matches[2]);
        }

        // Extract fuel type
        if (preg_match('/Nama Produk\s*:\s*([A-Z\s]+)/i', $text, $matches)) {
            $fuel = trim($matches[1]);
            $fuel = str_replace('PERTALITE', 'Pertalite', $fuel);
            $fuel = str_replace('PERTAMAX', 'Pertamax', $fuel);
            $fuel = str_replace('PERTAMAX TURBO', 'Pertamax Turbo', $fuel);
            $fuel = str_replace('DEXLITE', 'Dexlite', $fuel);
            $data['fuel_type'] = $fuel;
        }

        // Extract price per liter
        if (preg_match('/Harga\/Liter\s*:\s*Rp\.?\s*([\d\.,]+)/i', $text, $matches)) {
            $price = str_replace(['.', ','], ['', '.'], $matches[1]);
            $data['price_per_liter'] = (int) floatval($price);
        }

        // Extract liters
        if (preg_match('/Volume\s*:\s*\(L\)\s*([\d\.,]+)/i', $text, $matches)) {
            $liters = str_replace(',', '.', $matches[1]);
            $data['liters'] = (float) $liters;
        } elseif (preg_match('/Liter\s*:\s*([\d\.,]+)/i', $text, $matches)) {
            $liters = str_replace(',', '.', $matches[1]);
            $data['liters'] = (float) $liters;
        }

        // Extract total price
        if (preg_match('/Total Harga\s*:\s*Rp\.?\s*([\d\.,]+)/i', $text, $matches)) {
            $total = str_replace(['.', ','], ['', '.'], $matches[1]);
            $data['total_price'] = (int) floatval($total);
        }

        return $data;
    }

    private function validateAndConvertData($data)
    {
        // Konversi date ke format YYYY-MM-DD
        if ($data['date']) {
            try {
                $date = \Carbon\Carbon::parse($data['date']);
                $data['date'] = $date->format('Y-m-d');
            } catch (\Exception $e) {
                $data['date'] = date('Y-m-d');
            }
        } else {
            $data['date'] = date('Y-m-d');
        }

        // Validasi fuel_type
        $validFuels = ['Pertalite', 'Pertamax', 'Pertamax Turbo', 'Dexlite', 'Pertamina Dex'];
        if ($data['fuel_type'] && !in_array($data['fuel_type'], $validFuels)) {
            if (stripos($data['fuel_type'], 'pertalite') !== false) $data['fuel_type'] = 'Pertalite';
            elseif (stripos($data['fuel_type'], 'pertamax turbo') !== false) $data['fuel_type'] = 'Pertamax Turbo';
            elseif (stripos($data['fuel_type'], 'pertamax') !== false) $data['fuel_type'] = 'Pertamax';
            elseif (stripos($data['fuel_type'], 'dex') !== false) $data['fuel_type'] = 'Pertamina Dex';
            else $data['fuel_type'] = 'Pertalite';
        }

        // Konversi tipe data
        $data['price_per_liter'] = $data['price_per_liter'] ? (int) $data['price_per_liter'] : null;
        $data['liters'] = $data['liters'] ? (float) $data['liters'] : null;
        $data['total_price'] = $data['total_price'] ? (int) $data['total_price'] : null;

        // Hitung total jika tidak ada tapi price dan liters ada
        if (!$data['total_price'] && $data['price_per_liter'] && $data['liters']) {
            $data['total_price'] = $data['price_per_liter'] * $data['liters'];
        }

        return $data;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Pakai yang ini, dijamin ada!
use Illuminate\Support\Facades\Log;

class AiScanController extends Controller
{
    public function scan(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'receipt' => 'required|image|max:5120',
        ]);

        try {
            $image = $request->file('receipt');
            $base64Image = base64_encode(file_get_contents($image->path()));
            $mimeType = $image->getMimeType();

            // 2. Prompt (Perintah)
            $prompt = "Ekstrak data dari struk SPBU ini dan kembalikan HANYA JSON valid tanpa teks lain:
            {
                \"date\": \"YYYY-MM-DD\",
                \"location_name\": \"string\",
                \"fuel_type\": \"string (Pertalite/Pertamax/Dexlite/Pertamina Dex)\",
                \"price_per_liter\": integer,
                \"liters\": float,
                \"total_price\": integer
            }";

            // 3. Tembak API Gemini Langsung
            $apiKey = env('GEMINI_API_KEY');
// Ganti v1beta menjadi v1
$url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key={$apiKey}";
            $response = Http::post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64Image]]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                throw new \Exception("Gemini API Error: " . $response->body());
            }

            $result = $response->json();
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Bersihkan markdown ```json jika ada
            $cleanJson = trim(str_replace(['```json', '```'], '', $text));
            $data = json_decode($cleanJson, true);

            if (!$data) {
                return response()->json(['error' => 'Gagal parsing data struk.'], 422);
            }

            return response()->json($data);

        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }
}

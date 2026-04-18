<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\AI\Facades\AI; // Pastikan memanggil Facade SDK
use Illuminate\Support\Facades\Storage;

class AiScanController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'receipt' => 'required|image|max:5120',
        ]);

        try {
            // Menggunakan Laravel AI SDK
            $result = AI::withModel('gemini-1.5-flash')
                ->prompt("Ekstrak data dari struk SPBU ini. Kembalikan HANYA JSON valid:
                    {
                        'date': 'YYYY-MM-DD',
                        'location_name': 'string',
                        'fuel_type': 'string',
                        'price_per_liter': integer,
                        'liters': float,
                        'total_price': integer
                    }")
                ->withFile($request->file('receipt')->path())
                ->generate();

            // SDK biasanya otomatis membersihkan ```json ... ```
            $data = json_decode($result->text(), true);

            if (!$data) {
                return response()->json(['error' => 'AI gagal memproses format data.'], 422);
            }

            return response()->json($data);

        } catch (\Exception $e) {
            // Ini akan membantu kamu melihat error aslinya di log
            \Log::error('AI SDK Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghubungi AI: ' . $e->getMessage()], 500);
        }
    }


}

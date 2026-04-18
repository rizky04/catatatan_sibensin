<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\AI; // <-- Gunakan yang ini
class AiScanController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'receipt' => 'required|image|max:5120',
        ]);

        try {
            // 1. Simpan foto struk (opsional jika ingin disimpan permanen)
            $path = $request->file('receipt')->store('receipts', 'public');

            // 2. Gunakan Predict Object agar output PASTI sesuai schema
            $data = AI::withModel('gemini-1.5-flash')
                ->prompt("Ekstrak data dari struk SPBU ini secara akurat.")
                ->withFile($request->file('receipt')->path())
                ->predictObject([
                    'date' => 'string (format YYYY-MM-DD)',
                    'location_name' => 'string',
                    'fuel_type' => 'string (Pertalite, Pertamax, Pertamax Turbo, Dexlite, atau Pertamina Dex)',
                    'price_per_liter' => 'number',
                    'liters' => 'number',
                    'total_price' => 'number'
                ]);

            // Tambahkan path image ke response agar bisa disimpan ke database nanti
            return response()->json([
                'success' => true,
                'data' => $data,
                'image_path' => $path
            ]);

        } catch (\Exception $e) {
            Log::error('AI SDK Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membaca struk: ' . $e->getMessage()], 500);
        }
    }
}

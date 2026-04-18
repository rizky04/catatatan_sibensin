<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Gunakan Facade yang ini:
use Laravel\AI\Facades\AI;
use Illuminate\Support\Facades\Log;

class AiScanController extends Controller
{
    public function scan(Request $request)
    {
        // Validasi
        $request->validate([
            'receipt' => 'required|image|max:5120',
        ]);

        try {
            // Gunakan predictable object sesuai dokumentasi Laravel 13
            $data = AI::withModel('gemini-1.5-flash')
                ->prompt("Ekstrak data dari struk SPBU ini.")
                ->withFile($request->file('receipt')->path())
                ->predictObject([
                    'date' => 'string (YYYY-MM-DD)',
                    'location_name' => 'string',
                    'fuel_type' => 'string',
                    'price_per_liter' => 'integer',
                    'liters' => 'float',
                    'total_price' => 'integer'
                ]);

            return response()->json($data);

        } catch (\Exception $e) {
            Log::error('AI Scan Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal akses AI: ' . $e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Ai\Agents\FuelAssistant;
use App\Models\FuelEntry;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::where('user_id', Auth::id())->get();
        $activeVehicle = $vehicles->where('is_active', true)->first();

        return view('chatbot.index', compact('vehicles', 'activeVehicle'));
    }

    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'vehicle_id' => 'nullable|exists:vehicles,id'
        ]);

        $user = Auth::user();
        $vehicle = null;

        if ($request->vehicle_id) {
            $vehicle = Vehicle::where('user_id', $user->id)
                ->where('id', $request->vehicle_id)
                ->first();
        } else {
            $vehicle = Vehicle::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
        }

        try {
            // Get fuel statistics for context
            $stats = $this->getFuelStatistics($vehicle);

            // Build enhanced prompt with context
            $contextPrompt = $this->buildContextPrompt($request->message, $stats, $vehicle);

            $agent = new FuelAssistant($user, $vehicle);

            if ($request->conversation_id) {
                $agent = $agent->continue($request->conversation_id, as: $user);
            }

            $response = $agent->prompt($contextPrompt);

            return response()->json([
                'success' => true,
                'message' => (string) $response,
                'conversation_id' => $response->conversationId,
            ]);

        } catch (\Exception $e) {
            \Log::error('Chatbot error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Maaf, terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getFuelStatistics($vehicle = null)
    {
        try {
            // Base query
            $query = FuelEntry::where('user_id', Auth::id());

            if ($vehicle) {
                $query->where('vehicle_id', $vehicle->id);
            }

            // Get all entries for calculation (no GROUP BY issues)
            $entries = $query->orderBy('date', 'asc')->orderBy('odometer', 'asc')->get();

            if ($entries->isEmpty()) {
                return [
                    'has_data' => false,
                    'message' => 'Belum ada data pengisian BBM.'
                ];
            }

            // Basic calculations from collection
            $totalCost = $entries->sum('total_price');
            $totalLiters = $entries->sum('liters');
            $firstEntry = $entries->first();
            $lastEntry = $entries->last();
            $totalDistance = $lastEntry->odometer - ($firstEntry->odometer ?? 0);

            // Calculate average efficiency
            $efficiencies = [];
            $prevOdometer = null;
            $prevLiters = null;

            foreach ($entries as $entry) {
                if ($prevOdometer !== null && $prevLiters !== null) {
                    $distance = $entry->odometer - $prevOdometer;
                    if ($distance > 0 && $prevLiters > 0) {
                        $efficiencies[] = $distance / $prevLiters;
                    }
                }
                $prevOdometer = $entry->odometer;
                $prevLiters = $entry->liters;
            }

            $avgEfficiency = !empty($efficiencies) ? array_sum($efficiencies) / count($efficiencies) : 0;

            // Group by fuel type using collection (no SQL GROUP BY)
            $fuelTypeStats = [];
            foreach ($entries as $entry) {
                $fuelType = $entry->fuel_type;
                if (!isset($fuelTypeStats[$fuelType])) {
                    $fuelTypeStats[$fuelType] = [
                        'fuel_type' => $fuelType,
                        'total_liters' => 0,
                        'total_cost' => 0,
                        'total_count' => 0
                    ];
                }
                $fuelTypeStats[$fuelType]['total_liters'] += $entry->liters;
                $fuelTypeStats[$fuelType]['total_cost'] += $entry->total_price;
                $fuelTypeStats[$fuelType]['total_count']++;
            }

            // Monthly trend using collection
            $monthlyData = [];
            foreach ($entries as $entry) {
                $month = $entry->date->format('Y-m');
                $monthName = $entry->date->format('F Y');
                if (!isset($monthlyData[$month])) {
                    $monthlyData[$month] = [
                        'month' => $monthName,
                        'month_key' => $month,
                        'total_cost' => 0,
                        'total_liters' => 0,
                        'total_count' => 0
                    ];
                }
                $monthlyData[$month]['total_cost'] += $entry->total_price;
                $monthlyData[$month]['total_liters'] += $entry->liters;
                $monthlyData[$month]['total_count']++;
            }

            // Sort by month and get last 6
            ksort($monthlyData);
            $monthlyStats = array_values(array_slice($monthlyData, -6));

            // Get last 5 entries for recent activity
            $recentEntries = $entries->take(5);

            return [
                'has_data' => true,
                'total_cost' => $totalCost,
                'total_liters' => $totalLiters,
                'total_distance' => $totalDistance,
                'avg_efficiency' => round($avgEfficiency, 2),
                'entries_count' => $entries->count(),
                'first_entry_date' => $firstEntry->date->format('d M Y'),
                'last_entry_date' => $lastEntry->date->format('d M Y'),
                'fuel_type_stats' => array_values($fuelTypeStats),
                'monthly_stats' => $monthlyStats,
                'recent_entries' => $recentEntries->map(function($entry) {
                    return [
                        'date' => $entry->date->format('d M Y'),
                        'fuel_type' => $entry->fuel_type,
                        'liters' => $entry->liters,
                        'total_price' => $entry->total_price,
                        'odometer' => $entry->odometer
                    ];
                })
            ];

        } catch (\Exception $e) {
            \Log::error('Error getting fuel statistics: ' . $e->getMessage());
            return [
                'has_data' => false,
                'message' => 'Error memuat data: ' . $e->getMessage()
            ];
        }
    }

    private function buildContextPrompt($userQuestion, $stats, $vehicle = null)
    {
        $context = "Pertanyaan user: {$userQuestion}\n\n";
        $context .= "DATA STATISTIK BBM:\n";

        if (!$stats['has_data']) {
            $context .= "Belum ada data pengisian BBM. Sarankan user untuk mengisi data BBM terlebih dahulu.\n";
            $context .= "\nJawab dengan bahasa Indonesia yang ramah dan membantu.\n";
            return $context;
        }

        $context .= "📊 RINGKASAN UMUM:\n";
        $context .= "- Total pengeluaran: Rp " . number_format($stats['total_cost'], 0, ',', '.') . "\n";
        $context .= "- Total liter BBM: " . number_format($stats['total_liters'], 1, ',', '.') . " L\n";
        $context .= "- Total jarak tempuh: " . number_format($stats['total_distance'], 0, ',', '.') . " KM\n";
        $context .= "- Rata-rata efisiensi: " . number_format($stats['avg_efficiency'], 1, ',', '.') . " KM/Liter\n";
        $context .= "- Frekuensi pengisian: " . $stats['entries_count'] . " kali\n";
        $context .= "- Periode data: {$stats['first_entry_date']} - {$stats['last_entry_date']}\n\n";

        if (!empty($stats['fuel_type_stats'])) {
            $context .= "⛽ STATISTIK PER JENIS BBM:\n";
            foreach ($stats['fuel_type_stats'] as $fuel) {
                $avgPrice = $fuel['total_cost'] > 0 ? $fuel['total_cost'] / $fuel['total_liters'] : 0;
                $context .= "• {$fuel['fuel_type']}:\n";
                $context .= "  - Volume: " . number_format($fuel['total_liters'], 1, ',', '.') . " L\n";
                $context .= "  - Biaya: Rp " . number_format($fuel['total_cost'], 0, ',', '.') . "\n";
                $context .= "  - Rata-rata harga: Rp " . number_format($avgPrice, 0, ',', '.') . "/L\n";
                $context .= "  - Frekuensi: {$fuel['total_count']}x\n";
            }
            $context .= "\n";
        }

        if (!empty($stats['monthly_stats'])) {
            $context .= "📈 TREN BULANAN (6 bulan terakhir):\n";
            foreach ($stats['monthly_stats'] as $month) {
                $context .= "• {$month['month']}:\n";
                $context .= "  - Volume: " . number_format($month['total_liters'], 1, ',', '.') . " L\n";
                $context .= "  - Biaya: Rp " . number_format($month['total_cost'], 0, ',', '.') . "\n";
                if ($month['total_count'] > 0) {
                    $avgPerMonth = $month['total_cost'] / $month['total_count'];
                    $context .= "  - Rata-rata per pengisian: Rp " . number_format($avgPerMonth, 0, ',', '.') . "\n";
                }
            }
            $context .= "\n";
        }

        if (!empty($stats['recent_entries'])) {
            $context .= "🕒 AKTIVITAS TERBARU:\n";
            foreach ($stats['recent_entries'] as $entry) {
                $context .= "• {$entry['date']}: {$entry['fuel_type']} - " . number_format($entry['liters'], 1, ',', '.') . " L (Rp " . number_format($entry['total_price'], 0, ',', '.') . ") - Odometer: " . number_format($entry['odometer'], 0, ',', '.') . " KM\n";
            }
            $context .= "\n";
        }

        if ($vehicle) {
            $context .= "🚗 KENDARAAN AKTIF:\n";
            $context .= "- Nama: {$vehicle->name}\n";
            $context .= "- Plat nomor: {$vehicle->license_plate}\n";
            $context .= "- Odometer awal: " . number_format($vehicle->odometer_initial, 0, ',', '.') . " KM\n\n";
        }

        $context .= "INSTRUKSI:\n";
        $context .= "1. Jawab dengan bahasa Indonesia yang ramah, informatif, dan mudah dipahami\n";
        $context .= "2. Gunakan data di atas untuk menjawab pertanyaan user\n";
        $context .= "3. Berikan rekomendasi praktis untuk penghematan BBM jika memungkinkan\n";
        $context .= "4. Jika data tidak cukup, sarankan user untuk mengisi data BBM secara rutin\n";
        $context .= "5. Tampilkan angka dengan format yang rapi (gunakan pemisah ribuan)\n";
        $context .= "6. Jika user bertanya tentang hal di luar data, arahkan untuk mengisi data terlebih dahulu\n";

        return $context;
    }
}

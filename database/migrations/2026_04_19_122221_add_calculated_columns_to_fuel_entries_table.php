<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_entries', function (Blueprint $table) {
            // Kolom untuk menyimpan hasil perhitungan
            $table->decimal('fuel_efficiency', 10, 2)->nullable()->after('odometer');
            $table->decimal('cost_per_km', 10, 2)->nullable()->after('fuel_efficiency');
            $table->text('search_text')->nullable()->after('cost_per_km');

            // Index untuk performa query
            $table->index(['vehicle_id', 'date']);
            $table->index(['user_id', 'date']);
            $table->index(['fuel_type', 'date']);

            // Full text index untuk search (MySQL)
            $table->fullText('search_text');
        });
    }

    public function down(): void
    {
        Schema::table('fuel_entries', function (Blueprint $table) {
            $table->dropColumn(['fuel_efficiency', 'cost_per_km', 'search_text']);
            $table->dropIndex(['vehicle_id', 'date']);
            $table->dropIndex(['user_id', 'date']);
            $table->dropIndex(['fuel_type', 'date']);
            $table->dropFullText('fuel_entries_search_text_fulltext');
        });
    }
};

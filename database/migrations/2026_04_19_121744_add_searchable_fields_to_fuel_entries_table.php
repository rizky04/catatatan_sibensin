<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_entries', function (Blueprint $table) {
            // Tambahkan kolom untuk full-text search
            $table->text('search_text')->nullable();
            $table->json('search_metadata')->nullable();

            // Index untuk pencarian
            $table->index(['user_id', 'vehicle_id', 'date']);
            $table->fullText('search_text'); // Full-text index untuk MySQL

            // Kolom untuk caching hasil analisis
            $table->decimal('fuel_efficiency', 10, 2)->nullable();
            $table->decimal('cost_per_km', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('fuel_entries', function (Blueprint $table) {
            $table->dropColumn(['search_text', 'search_metadata', 'fuel_efficiency', 'cost_per_km']);
            $table->dropIndex(['user_id', 'vehicle_id', 'date']);
            $table->dropFullText('fuel_entries_search_text_fulltext');
        });
    }
};

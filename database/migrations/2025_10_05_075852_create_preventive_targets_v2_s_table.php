<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('preventive_targets_v2_s', function (Blueprint $table) {
            
            $table->id();
            $table->bigInteger('equipment_type_id');
            $table->tinyInteger('month')->comment('Bulan target (1-12)');
            $table->year('year')->comment('Tahun target');
            $table->unsignedSmallInteger('target_count')->comment('Target jumlah PM (kali) untuk semua unit tipe ini di bulan tersebut');
            $table->foreignId('created_by')->nullable();

            // Unique Constraint (PENTING: Mencegah target ganda)
            $table->unique(['equipment_type_id', 'month', 'year'], 'unique_target_per_month');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventive_targets_v2_s');
    }
};

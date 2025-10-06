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
        Schema::create('masterpmtasks', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('equipment_type_id')->constrained('master_equipment_types'); 
            $table->string('task_name');
            $table->string('task_category', 5); // I, L, C, T
            $table->text('anomaly_threshold');  // Deskripsi standar anomali
            $table->string('frequency_type', 15); // Shift, Weekly, Monthly
            $table->string('responsible_role', 50); // Teknisi Umum, Teknisi Spesialis

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masterpmtasks');
    }
};

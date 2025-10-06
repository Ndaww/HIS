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
        Schema::create('p_m_form_details', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key ke Header Form PM
            $table->unsignedBigInteger('form_header_id');
            
            // Detail Tugas
            $table->string('task_description');
            $table->string('standard_value', 100)->nullable()
                  ->comment('Nilai standar/batasan yang ditetapkan');
            
            // Hasil Pelaksanaan
            $table->string('actual_value', 100)->nullable()
                  ->comment('Nilai aktual hasil pengukuran/pemeriksaan');
            $table->string('pm_status', 50)
                  ->comment('Contoh: OK, Not OK, Adjusted');
            $table->text('pm_notes')->nullable()
                  ->comment('Catatan spesifik untuk tugas ini');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_m_form_details');
    }
};

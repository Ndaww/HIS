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
        Schema::create('p_m_form_headers', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke tabel jadwal (preventive_schedules_v2_s)
            // Asumsi: Jika tabel jadwal menggunakan ID BIGINT
            $table->unsignedBigInteger('schedule_id'); 
            
            // ID Peralatan dan Teknisi
            $table->unsignedBigInteger('equipment_id'); 
            $table->unsignedBigInteger('technician_id');
            
            // Detail Pelaksanaan
            $table->date('pm_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            
            // Hasil dan Catatan Akhir
            $table->string('overall_result', 100)->nullable()
                  ->comment('Contoh: Baik, Perlu Perbaikan Minor, Perlu Tindak Lanjut');
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_m_form_headers');
    }
};

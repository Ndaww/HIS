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
        Schema::create('preventive_schedules_v2_s', function (Blueprint $table) {
            // Kolom Utama
            $table->id();
            $table->foreignId('target_id')
                  ->nullable() 
                  ->constrained('preventive_targets_v2_s') 
                  ->onDelete('cascade'); 

            // Relasi ke Unit Equipment Spesifik
            // Asumsi tabel master equipment unit Anda bernama 'master_equipment'
            $table->foreignId('equipment_id')
                  ->constrained('master_equipment')
                  ->onDelete('cascade');

            // Relasi ke Teknisi
            // Asumsi tabel users Anda bernama 'users'
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');

            // Kolom Perencanaan Waktu
            $table->tinyInteger('target_month')->comment('Bulan yang direncanakan');
            $table->year('target_year')->comment('Tahun yang direncanakan');
            
            // Kolom Pelaksanaan
            $table->date('scheduled_date')->nullable()->comment('Tanggal PM direncanakan');
            $table->date('realization_date')->nullable()->comment('Tanggal PM selesai dikerjakan');

            // Status Pekerjaan
            // Gunakan ENUM jika statusnya pasti, atau VARCHAR jika fleksibel
            $table->enum('status', ['Scheduled', 'In Progress', 'Completed', 'Canceled'])->default('Scheduled');
            
            // // Kolom Tambahan (Opsional)
            // $table->string('report_file')->nullable()->comment('Link file laporan PM');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventive_schedules_v2_s');
    }
};

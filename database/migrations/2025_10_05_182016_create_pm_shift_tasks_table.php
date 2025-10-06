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
        Schema::create('pm_shift_tasks', function (Blueprint $table) {
             $table->id();

            // Identifikasi Tugas yang Dijadwalkan
            $table->foreignId('master_pm_task_id');
            $table->unsignedTinyInteger('month'); 
            $table->year('year');
            $table->string('assigned_shift', 10); // Shift yang diberi tugas (misal: 'Shift 1')
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('Pending'); // Status: Pending, In Progress, Done, etc.
            $table->timestamp('completion_date')->nullable(); // Waktu penyelesaian tugas
            $table->text('notes')->nullable();

            $table->unique(['master_pm_task_id', 'month', 'year'], 'pm_transaction_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_shift_tasks');
    }
};

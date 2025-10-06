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
        Schema::create('pm_shift_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_pm_task_id');
            
            $table->string('shift_name', 10)->comment('Contoh: Shift 1, Shift 2, Shift 3');
            $table->string('month', 20);
            $table->integer('year');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_shift_schedules');
    }
};

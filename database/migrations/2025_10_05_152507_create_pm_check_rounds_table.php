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
        Schema::create('pm_check_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users'); 
            $table->string('shift_name', 20); 
            $table->string('round_status', 20); 
            $table->dateTime('start_time');
            $table->dateTime('completion_time')->nullable();
            $table->integer('total_anomalies')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_check_rounds');
    }
};

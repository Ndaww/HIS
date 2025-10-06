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
        Schema::create('pm_check_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id'); 
            $table->foreignId('equipment_id'); 
            $table->foreignId('task_id'); 
            $table->string('result_status', 10); 
            $table->text('anomaly_details')->nullable(); 
            // $table->foreignId('work_order_id')->nullable()->constrained('work_orders');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pm_check_results');
    }
};

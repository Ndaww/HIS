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
        Schema::create('preventive_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id');//->constrained('master_equipments');
            $table->foreignId('room_id');//->constrained('master_rooms');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'in_progress', 'done', 'overdue'])->default('pending');
            $table->date('performed_date')->nullable();
            $table->foreignId('executor_id')->nullable();//->constrained('users'); // Pengeksekusi tugas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventive_tasks');
    }
};

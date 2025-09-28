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
        Schema::create('equipment_specialists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_type_id');//->constrained('master_equipment_types')->onDelete('cascade');
            $table->foreignId('specialist_type_id');//->constrained('master_specialist_types')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_specialists');
    }
};

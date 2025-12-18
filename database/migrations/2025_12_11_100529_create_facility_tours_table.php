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
        Schema::create('facility_tours', function (Blueprint $table) {
            $table->id();
            $table->string('pelapor');              
            $table->string('title');              
            $table->unsignedBigInteger('room_id');
            $table->enum('risk_grading', ['low','medium','high']);
            $table->unsignedBigInteger('department_id');
            $table->text('detail');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_tours');
    }
};

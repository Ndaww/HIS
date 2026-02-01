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
        Schema::create('pln_meter_readings', function (Blueprint $table) {
            $table->id();
            $table->string('id_pelanggan_pln');
            $table->date('tanggal_pencatatan')->nullable();
            $table->time('jam_pencatatan')->nullable();
            $table->decimal('cos_phi', 5, 3)->nullable();
            $table->decimal('wbp', 12, 2)->nullable();
            $table->decimal('lwbp', 12, 2)->nullable();
            $table->decimal('kwh', 12, 2)->nullable();
            $table->decimal('kvarh', 12, 2)->nullable();
            $table->text('temuan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pln_meter_readings');
    }
};

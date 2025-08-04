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
        Schema::create('master_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('floor');
            $table->string('class');
            $table->enum('status', ['kosong' , 'preventive','done preventive','done ga' ,'terisi'])->default('kosong');
            $table->date('preventive_done_at')->nullable();
            $table->enum('ga_status', ['pending', 'ok', 'not_ok'])->default('pending');
            $table->text('ga_notes')->nullable();
            $table->timestamp('nurse_confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_rooms');
    }
};

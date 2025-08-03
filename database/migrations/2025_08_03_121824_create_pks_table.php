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
        Schema::create('pks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');//->constrained()->onDelete('cascade');x
            $table->string('partner_name');
            $table->string('cooperation_type');
            $table->text('objective');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('initial_document')->nullable();
            $table->string('draft_document')->nullable();
            $table->string('final_document')->nullable();
            $table->enum('status', [
                'submitted',     // submitted by user
                'verified',      // verified by legal
                'approved',      // approved by director
                'rejected',      // rejected at any step
                'signed'         // signed and archived
            ])->default('submitted');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pks');
    }
};

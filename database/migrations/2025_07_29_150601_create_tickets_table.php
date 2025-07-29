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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number',25);
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('requester_id');//->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id');//->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_employee_id')->nullable();//->constrained('users')->nullOnDelete();
            $table->enum('status', ['open', 'in_progress', 'pending', 'solved', 'closed', 'escalated'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

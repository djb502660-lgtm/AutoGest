<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('maintenance_type', 100)->nullable();
            $table->date('scheduled_date');
            $table->unsignedInteger('mileage_target')->nullable();
            $table->foreignId('assigned_mechanic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['programado', 'completado', 'vencido', 'cancelado'])->default('programado');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'scheduled_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mechanic_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['preventivo', 'correctivo']);
            $table->string('description');
            $table->unsignedInteger('mileage_at_service')->nullable();
            $table->text('parts_used')->nullable();
            $table->text('technical_notes')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->enum('status', ['pendiente', 'en_proceso', 'completado', 'cancelado'])->default('pendiente');
            $table->dateTime('performed_at')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'status']);
            $table->index(['mechanic_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};

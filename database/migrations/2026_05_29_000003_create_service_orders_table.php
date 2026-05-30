<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mechanic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['recibida', 'en_proceso', 'completada', 'entregada', 'cancelada'])->default('recibida');
            $table->enum('priority', ['baja', 'normal', 'alta', 'urgente'])->default('normal');
            $table->text('description')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('recommendations')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['vehicle_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['mechanic_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('advisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('service_order_id')->nullable()->constrained()->nullOnDelete();
            $table->date('requested_date');
            $table->time('requested_time')->nullable();
            $table->string('service_type', 120);
            $table->text('description');
            $table->text('additional_work')->nullable();
            $table->boolean('requires_approval')->default(false);
            $table->enum('status', ['pendiente', 'confirmada', 'rechazada', 'convertida', 'cancelada'])->default('pendiente');
            $table->string('source', 20)->default('chatbot');
            $table->text('advisor_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'requested_date']);
            $table->index('advisor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_requests');
    }
};

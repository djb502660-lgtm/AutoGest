<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['maintenance_due', 'insurance_expiry', 'inspection_expiry', 'custom']);
            $table->string('title');
            $table->text('message');
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->date('due_date')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_resolved']);
            $table->index(['vehicle_id', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};

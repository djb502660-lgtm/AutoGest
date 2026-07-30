<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_model_templates', function (Blueprint $table) {
            $table->id();
            $table->string('brand', 80);
            $table->string('model', 80);
            $table->enum('maintenance_type', ['preventivo', 'correctivo'])->default('preventivo');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('interval_km')->nullable();
            $table->unsignedSmallInteger('interval_months')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['brand', 'model']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_model_templates');
    }
};

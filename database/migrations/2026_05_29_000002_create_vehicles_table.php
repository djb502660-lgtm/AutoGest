<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('plate', 20)->unique();
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('color', 50)->nullable();
            $table->unsignedInteger('mileage')->default(0);
            $table->string('vin', 50)->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['activo', 'inactivo', 'en_taller'])->default('activo');
            $table->date('insurance_expiry')->nullable();
            $table->date('inspection_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};

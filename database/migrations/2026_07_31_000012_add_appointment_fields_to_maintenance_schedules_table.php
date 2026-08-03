<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->renameColumn('maintenance_type', 'service_type');
        });

        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->enum('service_type', ['preventivo', 'correctivo', 'diagnostico', 'garantia'])->default('preventivo')->change();
            $table->enum('status', ['programado', 'confirmado', 'en_taller', 'cancelado'])->default('programado')->change();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->enum('service_type', ['preventivo', 'correctivo'])->default('preventivo')->change();
            $table->enum('status', ['programado', 'completado', 'vencido', 'cancelado'])->default('programado')->change();
            $table->renameColumn('service_type', 'maintenance_type');
        });
    }
};

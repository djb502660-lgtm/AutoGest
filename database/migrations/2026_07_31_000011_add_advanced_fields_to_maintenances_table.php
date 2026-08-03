<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->enum('type', ['preventivo', 'correctivo', 'garantia'])->default('preventivo')->change();
            $table->string('fuel_level', 20)->nullable()->after('mileage_at_service');
            $table->boolean('inventory_spare_wheel')->default(true)->after('fuel_level');
            $table->boolean('inventory_tools')->default(true)->after('inventory_spare_wheel');
            $table->boolean('inventory_radio')->default(true)->after('inventory_tools');
            $table->boolean('inventory_documents')->default(false)->after('inventory_radio');
            $table->decimal('parts_cost', 10, 2)->default(0)->after('technical_notes');
            $table->decimal('labor_cost', 10, 2)->default(0)->after('parts_cost');
            $table->enum('status', ['pendiente', 'en_proceso', 'completado', 'cancelado'])->default('pendiente')->change();
        });
    }

    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->enum('type', ['preventivo', 'correctivo'])->default('preventivo')->change();
            $table->dropColumn([
                'fuel_level',
                'inventory_spare_wheel',
                'inventory_tools',
                'inventory_radio',
                'inventory_documents',
                'parts_cost',
                'labor_cost',
            ]);
        });
    }
};

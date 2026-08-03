<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('sub_model', 100)->nullable()->after('model');
            $table->string('engine_number', 50)->nullable()->after('vin');
            $table->string('transmission_type', 50)->nullable()->after('engine_number');
            $table->string('tire_size', 50)->nullable()->after('transmission_type');
            $table->date('registration_date')->nullable()->after('inspection_expiry');
            $table->string('paint_reference', 50)->nullable()->after('registration_date');
            $table->string('transponder', 50)->nullable()->after('paint_reference');
            $table->string('radio_code', 50)->nullable()->after('transponder');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'sub_model',
                'engine_number',
                'transmission_type',
                'tire_size',
                'registration_date',
                'paint_reference',
                'transponder',
                'radio_code',
            ]);
        });
    }
};

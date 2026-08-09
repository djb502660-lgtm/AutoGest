<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('vehicle_id')->constrained('users')->nullOnDelete();
            $table->time('start_time')->nullable()->after('scheduled_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->unsignedTinyInteger('duration_minutes')->nullable()->after('end_time');
            
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropIndex(['client_id']);
            $table->dropColumn(['client_id', 'start_time', 'end_time', 'duration_minutes']);
        });
    }
};

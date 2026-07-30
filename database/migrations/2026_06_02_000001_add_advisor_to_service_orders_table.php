<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreignId('advisor_id')
                ->nullable()
                ->after('mechanic_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('source', 20)->default('manual')->after('created_by');

            $table->index('advisor_id');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['advisor_id']);
            $table->dropColumn(['advisor_id', 'source']);
        });
    }
};

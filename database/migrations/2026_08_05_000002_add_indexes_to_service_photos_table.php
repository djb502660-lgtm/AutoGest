<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('service_photos'))->pluck('name');

        Schema::table('service_photos', function (Blueprint $table) use ($indexes) {
            if (! $indexes->contains('service_photos_service_order_id_type_index')) {
                $table->index(['service_order_id', 'type']);
            }
            if (! $indexes->contains('service_photos_user_id_index')) {
                $table->index('user_id');
            }
            if (! $indexes->contains('service_photos_created_at_index')) {
                $table->index('created_at');
            }
        });
    }

    public function down(): void
    {
        // Indexes are created with the table.
    }
};

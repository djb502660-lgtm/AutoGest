<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE appointment_requests MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE appointment_requests MODIFY COLUMN status ENUM('pendiente', 'confirmada', 'rechazada', 'convertida') NOT NULL DEFAULT 'pendiente'");
    }
};

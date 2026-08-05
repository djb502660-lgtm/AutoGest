<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advisor_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_request_id')->constrained('appointment_requests')->onDelete('cascade');
            $table->string('type'); // created, updated, cancelled
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advisor_notifications');
    }
};

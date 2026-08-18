<?php

use App\Models\Alert;
use App\Models\AppointmentRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $columns = collect(Schema::getColumnListing('alerts'));

            if (! $columns->contains('appointment_request_id')) {
                $table->foreignId('appointment_request_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('appointment_requests')
                    ->nullOnDelete();
            }
        });

        $this->backfillLinks();
        $this->resolveHandledAppointments();
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $columns = collect(Schema::getColumnListing('alerts'));

            if ($columns->contains('appointment_request_id')) {
                $table->dropConstrainedForeignId('appointment_request_id');
            }
        });
    }

    private function backfillLinks(): void
    {
        Alert::query()
            ->whereNull('appointment_request_id')
            ->whereNotNull('vehicle_id')
            ->where('title', 'like', '%cita%chatbot%')
            ->each(function (Alert $alert) {
                $appointment = AppointmentRequest::query()
                    ->where('vehicle_id', $alert->vehicle_id)
                    ->when($alert->due_date, fn ($q) => $q->whereDate('requested_date', $alert->due_date))
                    ->latest('id')
                    ->first();

                if ($appointment) {
                    $alert->update(['appointment_request_id' => $appointment->id]);
                }
            });
    }

    private function resolveHandledAppointments(): void
    {
        $handledIds = AppointmentRequest::query()
            ->whereNotIn('status', ['pendiente', 'confirmada'])
            ->pluck('id');

        if ($handledIds->isEmpty()) {
            return;
        }

        Alert::query()
            ->whereIn('appointment_request_id', $handledIds)
            ->where('is_resolved', false)
            ->update([
                'is_read' => true,
                'is_resolved' => true,
                'resolved_at' => now(),
            ]);
    }
};

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\DashboardCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CalendarController extends Controller
{
    public function index(Request $request, DashboardCalendarService $calendar)
    {
        $period = $calendar->resolvePeriod(
            (int) $request->input('month', now()->month),
            (int) $request->input('year', now()->year),
        );

        $schedules = MaintenanceSchedule::with(['vehicle', 'assignedMechanic'])
            ->whereBetween('scheduled_date', [$period['grid_start']->toDateString(), $period['grid_end']->toDateString()])
            ->get()
            ->groupBy(fn ($item) => $item->scheduled_date->format('Y-m-d'));

        $orders = ServiceOrder::with('vehicle')
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$period['grid_start'], $period['grid_end']])
            ->get()
            ->groupBy(fn ($item) => $item->scheduled_at->format('Y-m-d'));

        $weeks = $calendar->makeGrid($period, [
            'schedules' => $schedules,
            'orders' => $orders,
        ]);

        $upcoming = MaintenanceSchedule::with('vehicle')
            ->where('scheduled_date', '>=', now()->toDateString())
            ->whereIn('status', ['programado', 'vencido'])
            ->orderBy('scheduled_date')
            ->take(8)
            ->get();

        return view('admin.calendar.index', [
            'weeks' => $weeks,
            'current' => $period['current'],
            'month' => $period['month'],
            'year' => $period['year'],
            'prev' => $period['prev'],
            'next' => $period['next'],
            'upcoming' => $upcoming,
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.calendar.create', [
            ...$this->formData(),
            'selectedDate' => $request->string('date')->toString() ?: now()->format('Y-m-d'),
        ]);
    }

    public function store(Request $request)
    {
        $schedule = MaintenanceSchedule::create($this->normalize($request->validate($this->rules())));
        $schedule->load('vehicle');

        ActivityLog::record(
            'schedule.created',
            "Se programó «{$schedule->title}» para el vehículo {$schedule->vehicle->plate}.",
            $schedule,
        );

        return redirect()
            ->route('calendar.index', [
                'month' => Carbon::parse($schedule->scheduled_date)->month,
                'year' => Carbon::parse($schedule->scheduled_date)->year,
            ])
            ->with('success', 'Evento programado correctamente.');
    }

    public function edit(MaintenanceSchedule $schedule)
    {
        return view('admin.calendar.edit', [
            'schedule' => $schedule,
            ...$this->formData(),
        ]);
    }

    public function update(Request $request, MaintenanceSchedule $schedule)
    {
        $schedule->update($this->normalize($request->validate($this->rules())));

        ActivityLog::record(
            'schedule.updated',
            "Se actualizó la programación «{$schedule->title}».",
            $schedule,
        );

        return redirect()
            ->route('calendar.index', [
                'month' => $schedule->scheduled_date->month,
                'year' => $schedule->scheduled_date->year,
            ])
            ->with('success', 'Evento actualizado correctamente.');
    }

    public function destroy(MaintenanceSchedule $schedule)
    {
        $title = $schedule->title;
        $month = $schedule->scheduled_date->month;
        $year = $schedule->scheduled_date->year;
        $schedule->delete();

        ActivityLog::record('schedule.deleted', "Se eliminó la programación «{$title}».");

        return redirect()
            ->route('calendar.index', compact('month', 'year'))
            ->with('success', 'Evento eliminado correctamente.');
    }

    private function formData(): array
    {
        return [
            'clients' => User::activeByRole(UserRole::Client)->get(),
            'vehicles' => Vehicle::orderBy('plate')->get(),
            'mechanics' => User::activeByRole(UserRole::Mechanic, UserRole::Advisor)->get(),
        ];
    }

    private function rules(): array
    {
        return [
            'client_id' => ['nullable', 'exists:users,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'title' => ['required', 'string', 'max:255'],
            'service_type' => ['required', Rule::in(['preventivo', 'correctivo', 'diagnostico', 'garantia'])],
            'scheduled_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'mileage_target' => ['nullable', 'integer', 'min:0'],
            'assigned_mechanic_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::in(['programado', 'confirmado', 'en_taller', 'cancelado'])],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function normalize(array $data): array
    {
        $data['assigned_mechanic_id'] = $data['assigned_mechanic_id'] ?: null;
        $data['client_id'] = $data['client_id'] ?: null;

        // Calculate end time based on start time and duration
        if (!empty($data['start_time']) && !empty($data['duration_minutes'])) {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $data['start_time']);
            $endTime = $startTime->addMinutes($data['duration_minutes']);
            $data['end_time'] = $endTime->format('H:i');
        }

        return $data;
    }
}

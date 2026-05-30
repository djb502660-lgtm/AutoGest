<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('es');

        $month = max(1, min(12, (int) $request->input('month', now()->month)));
        $year = (int) $request->input('year', now()->year);

        $current = Carbon::create($year, $month, 1)->locale('es');
        $startOfMonth = $current->copy()->startOfMonth();
        $endOfMonth = $current->copy()->endOfMonth();

        $gridStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $schedules = MaintenanceSchedule::with(['vehicle', 'assignedMechanic'])
            ->whereBetween('scheduled_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->get()
            ->groupBy(fn ($item) => $item->scheduled_date->format('Y-m-d'));

        $orders = ServiceOrder::with('vehicle')
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$gridStart->startOfDay(), $gridEnd->endOfDay()])
            ->get()
            ->groupBy(fn ($item) => $item->scheduled_at->format('Y-m-d'));

        $weeks = [];
        $day = $gridStart->copy();

        while ($day <= $gridEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $key = $day->format('Y-m-d');
                $week[] = [
                    'date' => $day->copy(),
                    'in_month' => $day->month === $month,
                    'is_today' => $day->isToday(),
                    'schedules' => $schedules->get($key, collect()),
                    'orders' => $orders->get($key, collect()),
                ];
                $day->addDay();
            }
            $weeks[] = $week;
        }

        $upcoming = MaintenanceSchedule::with('vehicle')
            ->where('scheduled_date', '>=', now()->toDateString())
            ->whereIn('status', ['programado', 'vencido'])
            ->orderBy('scheduled_date')
            ->take(8)
            ->get();

        return view('admin.calendar.index', [
            'weeks' => $weeks,
            'current' => $current,
            'month' => $month,
            'year' => $year,
            'prev' => $startOfMonth->copy()->subMonth(),
            'next' => $startOfMonth->copy()->addMonth(),
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
            'vehicles' => Vehicle::orderBy('plate')->get(),
            'mechanics' => User::where('role', UserRole::Mechanic)->where('status', 'activo')->orderBy('name')->get(),
        ];
    }

    private function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'title' => ['required', 'string', 'max:255'],
            'maintenance_type' => ['nullable', 'string', 'max:100'],
            'scheduled_date' => ['required', 'date'],
            'mileage_target' => ['nullable', 'integer', 'min:0'],
            'assigned_mechanic_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::in(['programado', 'completado', 'vencido', 'cancelado'])],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function normalize(array $data): array
    {
        $data['assigned_mechanic_id'] = $data['assigned_mechanic_id'] ?: null;

        return $data;
    }
}

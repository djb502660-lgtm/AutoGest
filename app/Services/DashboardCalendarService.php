<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardCalendarService
{
    public function resolvePeriod(?int $month = null, ?int $year = null): array
    {
        Carbon::setLocale('es');

        $month = max(1, min(12, $month ?: now()->month));
        $year = $year ?: now()->year;

        $current = Carbon::create($year, $month, 1)->locale('es');
        $startOfMonth = $current->copy()->startOfMonth();
        $endOfMonth = $current->copy()->endOfMonth();

        return [
            'month' => $month,
            'year' => $year,
            'current' => $current,
            'start_of_month' => $startOfMonth,
            'end_of_month' => $endOfMonth,
            'grid_start' => $startOfMonth->copy()->startOfWeek(Carbon::MONDAY),
            'grid_end' => $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY),
            'prev' => $startOfMonth->copy()->subMonth(),
            'next' => $startOfMonth->copy()->addMonth(),
        ];
    }

    public function makeWidget(array $period, array $eventGroups, array $options = []): array
    {
        $events = $this->normalizeEvents($eventGroups);
        $eventsByDate = $events
            ->filter(fn (array $event) => $event['date']->between($period['grid_start'], $period['grid_end']))
            ->groupBy(fn (array $event) => $event['date']->format('Y-m-d'));

        $weeks = [];
        $day = $period['grid_start']->copy();

        while ($day <= $period['grid_end']) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $key = $day->format('Y-m-d');

                $week[] = [
                    'date' => $day->copy(),
                    'in_month' => $day->month === $period['month'],
                    'is_today' => $day->isToday(),
                    'events' => $eventsByDate->get($key, collect()),
                ];

                $day->addDay();
            }

            $weeks[] = $week;
        }

        return [
            'title' => $options['title'] ?? 'Agenda del mes',
            'subtitle' => $options['subtitle'] ?? 'Calendario operativo integrado en el panel.',
            'upcoming_title' => $options['upcoming_title'] ?? 'Próximos eventos',
            'empty_text' => $options['empty_text'] ?? 'No hay eventos programados en este periodo.',
            'current' => $period['current'],
            'prev_url' => $options['prev_url'] ?? null,
            'next_url' => $options['next_url'] ?? null,
            'weeks' => $weeks,
            'legend' => $options['legend'] ?? [],
            'upcoming' => $events
                ->filter(fn (array $event) => $event['date']->isSameDay(today()) || $event['date']->isFuture())
                ->sortBy(fn (array $event) => $event['date']->timestamp)
                ->values()
                ->take($options['upcoming_limit'] ?? 8),
        ];
    }

    private function normalizeEvents(array $eventGroups): Collection
    {
        return collect($eventGroups)
            ->flatMap(function (array $group) {
                $items = collect($group['items'] ?? []);

                return $items->map(function ($item) use ($group) {
                    $date = value($group['date'], $item);
                    $date = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

                    return [
                        'date' => $date,
                        'label' => value($group['label'], $item),
                        'meta' => value($group['meta'] ?? fn () => null, $item),
                        'variant' => value($group['variant'] ?? fn () => 'event-muted', $item),
                        'url' => value($group['url'] ?? fn () => null, $item),
                    ];
                });
            })
            ->sortBy(fn (array $event) => [$event['date']->timestamp, $event['label']])
            ->values();
    }
}

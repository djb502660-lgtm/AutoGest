<section class="panel dashboard-calendar-panel">
    <div class="calendar-toolbar dashboard-calendar-toolbar">
        <div>
            <h3>{{ $calendar['title'] }}</h3>
            <p class="subtle">{{ $calendar['subtitle'] }}</p>
        </div>

        <div class="calendar-nav">
            @if (!empty($calendar['prev_url']))
                <a href="{{ $calendar['prev_url'] }}" aria-label="Mes anterior">← Anterior</a>
            @endif

            <span class="calendar-title">{{ $calendar['current']->translatedFormat('F Y') }}</span>

            @if (!empty($calendar['next_url']))
                <a href="{{ $calendar['next_url'] }}" aria-label="Mes siguiente">Siguiente →</a>
            @endif
        </div>
    </div>

    <div class="calendar-layout">
        <div>
            <div class="calendar-grid">
                @foreach (['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $weekday)
                    <div class="calendar-weekday">{{ $weekday }}</div>
                @endforeach

                @foreach ($calendar['weeks'] as $week)
                    @foreach ($week as $day)
                        <div class="calendar-day {{ $day['in_month'] ? '' : 'outside' }} {{ $day['is_today'] ? 'today' : '' }}">
                            <div class="day-number">
                                <span>{{ $day['date']->format('d') }}</span>
                            </div>

                            <div class="calendar-day-events">
                                @forelse ($day['events']->take(3) as $event)
                                    @if ($event['url'])
                                        <a href="{{ $event['url'] }}" class="event-chip {{ $event['variant'] }}" title="{{ $event['meta'] }}">
                                            {{ $event['label'] }}
                                        </a>
                                    @else
                                        <span class="event-chip {{ $event['variant'] }}" title="{{ $event['meta'] }}">
                                            {{ $event['label'] }}
                                        </span>
                                    @endif
                                @empty
                                    <span class="calendar-empty">Sin eventos</span>
                                @endforelse

                                @if ($day['events']->count() > 3)
                                    <span class="event-chip event-muted">+{{ $day['events']->count() - 3 }} más</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>

            @if (!empty($calendar['legend']))
                <div class="calendar-legend">
                    @foreach ($calendar['legend'] as $item)
                        <span>
                            <i class="{{ $item['variant'] }}"></i>
                            {{ $item['label'] }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        <aside class="panel calendar-side-panel">
            <h4>{{ $calendar['upcoming_title'] }}</h4>

            <div class="upcoming-list">
                @forelse ($calendar['upcoming'] as $event)
                    <div class="upcoming-item">
                        <strong>{{ $event['label'] }}</strong>
                        <span>{{ $event['date']->translatedFormat('d M') }}</span>
                        @if ($event['meta'])
                            <p>{{ $event['meta'] }}</p>
                        @endif
                    </div>
                @empty
                    <p class="upcoming-empty">{{ $calendar['empty_text'] }}</p>
                @endforelse
            </div>
        </aside>
    </div>
</section>

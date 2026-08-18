@extends('layouts.client')

@section('title', 'Gastos')
@section('heading', 'Gastos en mantenimiento')
@section('subheading')
    Resumen de los últimos 12 meses.
@endsection

@section('content')
    <div class="panel">
        <div class="chart-wrap">
            <div class="donut" @style(['background' => $donutGradient])>
                <div class="donut-hole">
                    Total<br><strong style="color:var(--text);font-size:1rem;">${{ number_format($total, 0) }}</strong>
                </div>
            </div>
            <div class="legend">
                @foreach ($categories as $label => $cat)
                    @if ($cat['amount'] > 0)
                        <div class="legend-item">
                            <span class="legend-dot" @style(['background' => $cat['color']])></span>
                            {{ $label }} — ${{ number_format($cat['amount'], 2) }}
                            ({{ $total > 0 ? round(($cat['amount'] / $total) * 100) : 0 }}%)
                        </div>
                    @endif
                @endforeach
                @if ($total <= 0)
                    <p style="color:var(--muted);margin:0;">Sin gastos registrados en el período.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="panel">
        <h3 style="margin:0 0 12px;">Detalle reciente</h3>
        <table class="table">
            <thead><tr><th>Fecha</th><th>Vehículo</th><th>Servicio</th><th>Costo</th></tr></thead>
            <tbody>
                @forelse ($recentExpenses as $m)
                    <tr>
                        <td>{{ $m->performed_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $m->vehicle->plate }}</td>
                        <td>{{ $m->description }}</td>
                        <td>${{ number_format($m->cost, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">Sin gastos en los últimos 12 meses.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@extends('layouts.admin')

@section('title', $title)
@section('heading', $title)
@section('subheading', 'Resultados generados según los filtros aplicados.')

@push('styles')
<style>
    .summary-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
    .summary-box { padding:12px; border-radius:12px; background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.08); }
    .summary-box span { display:block; color:var(--muted); font-size:0.68rem; text-transform:uppercase; letter-spacing:.1em; }
    .summary-box strong { font-size:1.2rem; margin-top:6px; display:block; }
    @media print { .sidebar, .topbar, .btn { display:none !important; } .shell { grid-template-columns:1fr; } }
    @media (max-width:900px) { .summary-grid { grid-template-columns:1fr; } }
</style>
@endpush

@section('top-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">← Nuevo reporte</a>
    <button type="button" class="btn btn-warning" onclick="window.print()">Imprimir</button>
@endsection

@section('content')
    <div class="panel">
        <div class="summary-grid">
            @foreach ($summary as $label => $value)
                <div class="summary-box">
                    <span>{{ $label }}</span>
                    <strong>{{ $value }}</strong>
                </div>
            @endforeach
        </div>

        <table class="table">
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ count($columns) }}">No hay datos para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

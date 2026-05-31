@extends('layouts.admin')

@section('title', $title)
@section('heading', $title)
@section('subheading', 'Resultados generados según los filtros aplicados.')

@section('top-actions')
    <a href="{{ route('reports.index') }}" class="btn btn-secondary">← Nuevo reporte</a>
    <a href="{{ route('reports.pdf', $filters) }}" class="btn btn-primary">Descargar PDF</a>
    <form method="POST" action="{{ route('reports.email') }}" style="display:inline;margin:0;">
        @csrf
        @foreach ($filters as $key => $value)
            @if ($value !== null && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <button type="submit" class="btn btn-secondary">Enviar a mi correo</button>
    </form>
    <button type="button" class="btn btn-warning" onclick="window.print()">Imprimir</button>
@endsection

@section('content')
    <div class="panel">
        <p class="report-export-note">
            Puedes descargar este reporte en PDF o recibirlo en <strong>{{ auth()->user()->email }}</strong>.
            @if (config('mail.default') === 'log')
                En desarrollo los correos se registran en <code>storage/logs/laravel.log</code> (Laragon Mailpit también puede capturarlos si está activo).
            @endif
        </p>

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

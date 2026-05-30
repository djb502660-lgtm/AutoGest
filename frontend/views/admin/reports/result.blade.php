@extends('layouts.admin')

@section('title', $title)
@section('heading', $title)
@section('subheading', 'Resultados generados según los filtros aplicados.')


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

@extends('layouts.admin')

@section('title', 'Editar evento')
@section('heading', 'Editar evento')
@section('subheading')
    Actualiza la programación de «{{ $schedule->title }}».
@endsection

@section('top-actions')
    <a href="{{ route('calendar.index', ['month' => $schedule->scheduled_date->month, 'year' => $schedule->scheduled_date->year]) }}" class="btn btn-secondary">← Volver</a>
@endsection

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('calendar.update', $schedule) }}">
            @csrf @method('PUT')
            @include('admin.calendar._form', ['schedule' => $schedule, 'clients' => $clients, 'vehicles' => $vehicles, 'mechanics' => $mechanics])
            <div class="actions">
                <button type="submit" class="btn btn-primary">Actualizar evento</button>
                <a href="{{ route('calendar.index', ['month' => $schedule->scheduled_date->month, 'year' => $schedule->scheduled_date->year]) }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>

        <form method="POST" action="{{ route('calendar.destroy', $schedule) }}" style="margin-top:12px;" data-confirm="¿Eliminar este evento?" data-confirm-title="Eliminar evento" data-confirm-label="Eliminar">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">Eliminar evento</button>
        </form>
    </div>
@endsection

@extends('layouts.admin')

@section('title', 'Nuevo evento')
@section('heading', 'Nuevo evento en calendario')
@section('subheading', 'Programa un mantenimiento para una fecha específica.')

@section('top-actions')
    <a href="{{ route('calendar.index') }}" class="btn btn-secondary">← Volver al calendario</a>
@endsection

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('calendar.store') }}">
            @csrf
            @include('admin.calendar._form', compact('vehicles', 'mechanics', 'selectedDate'))
            <div class="actions">
                <button type="submit" class="btn btn-primary">Guardar evento</button>
                <a href="{{ route('calendar.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

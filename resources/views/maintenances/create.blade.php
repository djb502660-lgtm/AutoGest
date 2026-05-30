@extends('layouts.admin')

@section('title', 'Nuevo mantenimiento')
@section('heading', 'Nuevo mantenimiento')
@section('subheading', 'Registra un servicio preventivo o correctivo.')

@section('top-actions')
    <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">← Volver</a>
@endsection

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('maintenances.store') }}">
            @csrf
            @include('maintenances._form', compact('vehicles', 'mechanics', 'orders'))
            <div class="actions">
                <button type="submit" class="btn btn-primary">Guardar mantenimiento</button>
                <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

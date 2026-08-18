@extends('layouts.admin')

@section('title', 'Editar mantenimiento')
@section('heading', 'Editar mantenimiento')
@section('subheading', 'Actualiza el registro de servicio.')

@section('top-actions')
    <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">← Volver</a>
@endsection

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('maintenances.update', $maintenance) }}">
            @csrf @method('PUT')
            @include('admin.maintenances._form', ['maintenance' => $maintenance, 'vehicles' => $vehicles, 'mechanics' => $mechanics, 'orders' => $orders])
            <div class="actions">
                <button type="submit" class="btn btn-primary">Actualizar mantenimiento</button>
                <a href="{{ route('maintenances.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

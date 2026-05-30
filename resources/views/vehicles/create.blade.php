@extends('layouts.admin')

@section('title', 'Nuevo vehículo')
@section('heading', 'Nuevo vehículo')
@section('subheading', 'Registra un vehículo y asígnalo a un cliente.')

@section('top-actions')
    <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">← Volver</a>
@endsection

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('vehicles.store') }}">
            @csrf
            @include('vehicles._form', ['clients' => $clients])
            <div class="actions">
                <button type="submit" class="btn btn-primary">Guardar vehículo</button>
                <a href="{{ route('vehicles.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

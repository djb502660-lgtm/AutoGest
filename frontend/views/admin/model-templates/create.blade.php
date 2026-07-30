@extends('layouts.admin')

@section('title', 'Nueva plantilla')
@section('heading', 'Nueva plantilla de mantenimiento')
@section('subheading', 'Cada modelo puede tener uno o más tipos de servicio sugeridos.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('model-templates.store') }}">
            @csrf
            @include('admin.model-templates._form')
            <div class="actions">
                <button type="submit" class="btn btn-primary">Guardar plantilla</button>
                <a href="{{ route('model-templates.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

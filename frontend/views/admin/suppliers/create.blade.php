@extends('layouts.admin')

@section('title', 'Nuevo proveedor')
@section('heading', 'Nuevo proveedor')
@section('subheading', 'Registra un nuevo proveedor.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('suppliers.store') }}">
            @csrf
            <div class="form-group">
                <label for="name">Nombre *</label>
                <input type="text" id="name" name="name" required autofocus>
            </div>
            <div class="form-group">
                <label for="contact_person">Persona de contacto</label>
                <input type="text" id="contact_person" name="contact_person">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone">
            </div>
            <div class="form-group">
                <label for="address">Dirección</label>
                <input type="text" id="address" name="address">
            </div>
            <div class="form-group">
                <label for="city">Ciudad</label>
                <input type="text" id="city" name="city">
            </div>
            <div class="form-group">
                <label for="country">País</label>
                <input type="text" id="country" name="country" value="México">
            </div>
            <div class="form-group">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" checked> Activo
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection

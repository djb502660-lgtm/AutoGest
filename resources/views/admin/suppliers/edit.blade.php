@extends('layouts.admin')

@section('title', 'Editar proveedor')
@section('heading', 'Editar proveedor')
@section('subheading', 'Modifica la información del proveedor.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nombre *</label>
                <input type="text" id="name" name="name" value="{{ $supplier->name }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="contact_person">Persona de contacto</label>
                <input type="text" id="contact_person" name="contact_person" value="{{ $supplier->contact_person }}">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ $supplier->email }}">
            </div>
            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" value="{{ $supplier->phone }}">
            </div>
            <div class="form-group">
                <label for="address">Dirección</label>
                <input type="text" id="address" name="address" value="{{ $supplier->address }}">
            </div>
            <div class="form-group">
                <label for="city">Ciudad</label>
                <input type="text" id="city" name="city" value="{{ $supplier->city }}">
            </div>
            <div class="form-group">
                <label for="country">País</label>
                <input type="text" id="country" name="country" value="{{ $supplier->country }}">
            </div>
            <div class="form-group">
                <label for="notes">Notas</label>
                <textarea id="notes" name="notes" rows="3">{{ $supplier->notes }}</textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" {{ $supplier->is_active ? 'checked' : '' }}> Activo
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@endsection

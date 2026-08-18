@extends('layouts.advisor')

@section('title', 'Nuevo cliente')
@section('heading', 'Nuevo cliente')
@section('subheading', 'Registra un nuevo cliente en el sistema.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('advisor.clients.store') }}">
            @csrf
            <div class="form-group">
                <label for="name">Nombre *</label>
                <input type="text" id="name" name="name" required autofocus>
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña *</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone">
            </div>
            <div class="form-group">
                <label for="status">Estado *</label>
                <select id="status" name="status" required>
                    <option value="activo" selected>Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div class="form-actions">
                <a href="{{ route('advisor.clients.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection

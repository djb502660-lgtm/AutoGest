@extends('layouts.advisor')

@section('title', 'Editar cliente')
@section('heading', 'Editar cliente')
@section('subheading', 'Modifica la información del cliente.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('advisor.clients.update', $client) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nombre *</label>
                <input type="text" id="name" name="name" value="{{ $client->name }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="{{ $client->email }}" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password">
                <small>Dejar en blanco para mantener la actual</small>
            </div>
            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" value="{{ $client->phone }}">
            </div>
            <div class="form-group">
                <label for="status">Estado *</label>
                <select id="status" name="status" required>
                    <option value="activo" {{ $client->status === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ $client->status === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="form-actions">
                <a href="{{ route('advisor.clients.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@endsection

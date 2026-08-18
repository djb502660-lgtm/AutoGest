@extends('layouts.admin')

@section('title', 'Nueva marca')
@section('heading', 'Nueva marca')
@section('subheading', 'Registra una nueva marca de productos.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('brands.store') }}">
            @csrf
            <div class="form-group">
                <label for="name">Nombre *</label>
                <input type="text" id="name" name="name" required autofocus>
            </div>
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" required>
            </div>
            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" checked> Activo
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('brands.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection

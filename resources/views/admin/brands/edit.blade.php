@extends('layouts.admin')

@section('title', 'Editar marca')
@section('heading', 'Editar marca')
@section('subheading', 'Modifica la información de la marca.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('brands.update', $brand) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nombre *</label>
                <input type="text" id="name" name="name" value="{{ $brand->name }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" value="{{ $brand->slug }}" required>
            </div>
            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea id="description" name="description" rows="3">{{ $brand->description }}</textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" {{ $brand->is_active ? 'checked' : '' }}> Activo
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('brands.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@endsection

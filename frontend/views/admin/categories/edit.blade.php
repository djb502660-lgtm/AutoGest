@extends('layouts.admin')

@section('title', 'Editar categoría')
@section('heading', 'Editar categoría')
@section('subheading', 'Modifica la información de la categoría.')

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('categories.update', $category) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nombre *</label>
                <input type="text" id="name" name="name" value="{{ $category->name }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" value="{{ $category->slug }}" required>
            </div>
            <div class="form-group">
                <label for="description">Descripción</label>
                <textarea id="description" name="description" rows="3">{{ $category->description }}</textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" {{ $category->is_active ? 'checked' : '' }}> Activo
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@endsection

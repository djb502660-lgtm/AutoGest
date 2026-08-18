@extends('layouts.admin')

@section('title', 'Nuevo usuario')
@section('heading', 'Nuevo usuario')
@section('subheading', 'Crea una cuenta y asigna rol y permisos.')

@section('top-actions')
    <a href="{{ route('users.index') }}" class="btn btn-secondary">← Volver</a>
@endsection

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            @include('admin.users._form', ['roles' => $roles])

            <div class="actions">
                <button type="submit" class="btn btn-primary">Guardar usuario</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

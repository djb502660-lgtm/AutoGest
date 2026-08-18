@extends('layouts.admin')

@section('title', 'Editar usuario')
@section('heading', 'Editar usuario')
@section('subheading', 'Actualiza datos, rol y estado de la cuenta.')

@section('top-actions')
    <a href="{{ route('users.index') }}" class="btn btn-secondary">← Volver</a>
@endsection

@section('content')
    <div class="panel">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('admin.users._form', ['user' => $user, 'roles' => $roles])

            <div class="actions">
                <button type="submit" class="btn btn-primary">Actualizar usuario</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection

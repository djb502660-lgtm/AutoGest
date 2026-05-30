@extends('layouts.client')

@section('title', 'Perfil')
@section('heading', 'Perfil de usuario')
@section('subheading')
    Actualiza tu información personal.
@endsection

@section('content')
    <div class="panel" style="max-width:560px;">
        <form method="POST" action="{{ route('client.profile.update') }}">
            @csrf @method('PUT')
            <div class="field">
                <label>Nombre</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')<span style="color:#fda4af;font-size:0.78rem;">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label>Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')<span style="color:#fda4af;font-size:0.78rem;">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label>Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                @error('phone')<span style="color:#fda4af;font-size:0.78rem;">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label>Nueva contraseña (opcional)</label>
                <input type="password" name="password" autocomplete="new-password">
                @error('password')<span style="color:#fda4af;font-size:0.78rem;">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label>Confirmar contraseña</label>
                <input type="password" name="password_confirmation" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </form>
    </div>
@endsection

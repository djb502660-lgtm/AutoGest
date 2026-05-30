@extends('layouts.admin')

@section('title', 'Mi perfil')
@section('heading', 'Mi perfil')
@section('subheading', 'Actualiza tu información personal y contraseña de acceso.')

@section('content')
    <div class="panel" style="max-width:640px;">
        <div class="profile-meta" style="margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border);">
            <p style="margin:0;font-size:0.9rem;color:var(--text-muted);">
                <strong>Rol:</strong> {{ $user->role->label() }}
                @if ($user->last_login_at)
                    · <strong>Último acceso:</strong> {{ $user->last_login_at->format('d/m/Y H:i') }}
                @endif
            </p>
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="field">
                    <label for="name">Nombre completo</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label for="phone">Teléfono</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="0991234567">
                    @error('phone')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label for="password">Nueva contraseña (opcional)</label>
                    <input type="password" id="password" name="password" autocomplete="new-password">
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
@endsection

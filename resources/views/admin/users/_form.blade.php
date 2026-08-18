@props(['user' => null, 'roles'])

<div class="form-grid">
    <div class="field">
        <label for="name">Nombre completo</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user?->name) }}" required>
        @error('name')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="field">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user?->email) }}" required>
        @error('email')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="field">
        <label for="phone">Teléfono</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $user?->phone) }}" placeholder="0991234567">
        @error('phone')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="field">
        <label for="role">Rol</label>
        <select id="role" name="role" required>
            @foreach ($roles as $roleOption)
                <option value="{{ $roleOption->value }}" @selected(old('role', $user?->role?->value) === $roleOption->value)>
                    {{ $roleOption->label() }}
                </option>
            @endforeach
        </select>
        @error('role')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="field">
        <label for="status">Estado</label>
        <select id="status" name="status" required>
            <option value="activo" @selected(old('status', $user?->status ?? 'activo') === 'activo')>Activo</option>
            <option value="inactivo" @selected(old('status', $user?->status) === 'inactivo')>Inactivo</option>
        </select>
        @error('status')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="field">
        <label for="password">{{ $user ? 'Nueva contraseña (opcional)' : 'Contraseña' }}</label>
        <input type="password" id="password" name="password" {{ $user ? '' : 'required' }} autocomplete="new-password">
        @error('password')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>

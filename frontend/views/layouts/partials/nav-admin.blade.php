<nav class="menu nav flex-column" aria-label="Menú administrador">
    <div class="menu-label">Principal</div>
    <a class="menu-item nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">📊 Dashboard</a>

    <div class="menu-label">Gestión</div>
    <a class="menu-item nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">🚗 Vehículos</a>
    <a class="menu-item nav-link {{ request()->routeIs('model-templates.*') ? 'active' : '' }}" href="{{ route('model-templates.index') }}">📋 Plantillas por modelo</a>
    <a class="menu-item nav-link {{ request()->routeIs('maintenances.*') ? 'active' : '' }}" href="{{ route('maintenances.index') }}">🛠️ Mantenimientos</a>
    <a class="menu-item nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">👥 Usuarios</a>

    <div class="menu-label">Control</div>
    <a class="menu-item nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">📈 Reportes</a>

    <div class="menu-label">Cuenta</div>
    <a class="menu-item nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">👤 Mi perfil</a>
</nav>

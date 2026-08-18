<nav class="menu" aria-label="Menú administrador">
    <div class="menu-label">📂 DASHBOARD Y OPERACIONES</div>
    <a class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">📊 Dashboard General</a>
    <a class="menu-item {{ request()->routeIs('admin.chatbot-appointments.*') ? 'active' : '' }}" href="{{ route('admin.chatbot-appointments.index') }}">🤖 Solicitudes chatbot</a>
    <a class="menu-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">📋 Órdenes del Día</a>
    <a class="menu-item {{ request()->routeIs('calendar.*') ? 'active' : '' }}" href="{{ route('calendar.index') }}">📅 Agenda y Calendario</a>

    <div class="menu-label">📂 GESTIÓN DE TALLER Y MANTENIMIENTO</div>
    <a class="menu-item {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">🚘 Vehículos</a>
    <a class="menu-item {{ request()->routeIs('maintenances.*') ? 'active' : '' }}" href="{{ route('maintenances.index') }}">🔧 Mantenimientos y Pautas</a>

    <div class="menu-label">📂 PRODUCTOS E INVENTARIO</div>
    <a class="menu-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">🎛️ Gestión Unificada</a>

    <div class="menu-label">📂 ADMINISTRACIÓN Y USUARIOS</div>
    <a class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">👥 Gestión de Usuarios</a>
    <a class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">📈 Reportes e Indicadores</a>
    <a class="menu-item {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">🔍 Auditoría del Sistema</a>

    <div class="menu-label">Cuenta</div>
    <a class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">👤 Mi perfil</a>
</nav>

<nav class="menu" aria-label="Menú mecánico">
    <div class="menu-label">Operación</div>
    <a class="menu-item {{ request()->routeIs('mechanic.dashboard') ? 'active' : '' }}" href="{{ route('mechanic.dashboard') }}">Dashboard</a>
    <a class="menu-item {{ request()->routeIs('mechanic.orders.*') ? 'active' : '' }}" href="{{ route('mechanic.orders.index') }}">Órdenes de servicio</a>
    <a class="menu-item {{ request()->routeIs('mechanic.calendar.*') ? 'active' : '' }}" href="{{ route('mechanic.calendar.index') }}">Calendario</a>
    <a class="menu-item {{ request()->routeIs('mechanic.vehicles.*') ? 'active' : '' }}" href="{{ route('mechanic.vehicles.index') }}">Vehículos</a>
    <a class="menu-item {{ request()->routeIs('mechanic.history') ? 'active' : '' }}" href="{{ route('mechanic.history') }}">Historial</a>
</nav>

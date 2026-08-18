<nav class="menu nav flex-column" aria-label="Menú mecánico">
    <a class="menu-item nav-link {{ request()->routeIs('mechanic.dashboard') ? 'active' : '' }}" href="{{ route('mechanic.dashboard') }}">📊 Dashboard</a>
    <a class="menu-item nav-link {{ request()->routeIs('mechanic.orders.*') ? 'active' : '' }}" href="{{ route('mechanic.orders.index') }}">📋 Órdenes de servicio</a>
    <a class="menu-item nav-link {{ request()->routeIs('mechanic.vehicles.*') ? 'active' : '' }}" href="{{ route('mechanic.vehicles.index') }}">🚗 Vehículos</a>
    <a class="menu-item nav-link {{ request()->routeIs('mechanic.history') ? 'active' : '' }}" href="{{ route('mechanic.history') }}">📜 Historial</a>
</nav>

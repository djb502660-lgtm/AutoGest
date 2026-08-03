<nav class="menu nav flex-column" aria-label="Menú cliente">
    <a class="menu-item nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" href="{{ route('client.dashboard') }}">📊 Dashboard</a>
    <a class="menu-item nav-link {{ request()->routeIs('client.vehicles.*') ? 'active' : '' }}" href="{{ route('client.vehicles.index') }}">🚗 Mis vehículos</a>
    <a class="menu-item nav-link {{ request()->routeIs('client.orders.*') || request()->routeIs('client.maintenances.*') ? 'active' : '' }}" href="{{ route('client.orders.index') }}">📋 Órdenes e Historial</a>
</nav>

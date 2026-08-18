<nav class="menu" aria-label="Menú cliente">
    <a class="menu-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" href="{{ route('client.dashboard') }}">📊 Dashboard</a>
    <a class="menu-item {{ request()->routeIs('client.chatbot.*') ? 'active' : '' }}" href="{{ route('client.chatbot.index') }}">🤖 Asistente</a>
    <a class="menu-item {{ request()->routeIs('client.vehicles.*') ? 'active' : '' }}" href="{{ route('client.vehicles.index') }}">🚗 Mis vehículos</a>
    <a class="menu-item {{ request()->routeIs('client.orders.*') || request()->routeIs('client.maintenances.*') ? 'active' : '' }}" href="{{ route('client.orders.index') }}">📋 Órdenes e Historial</a>
</nav>

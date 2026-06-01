<nav class="menu nav flex-column" aria-label="Menú cliente">
    <a class="menu-item nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" href="{{ route('client.dashboard') }}">📊 Dashboard</a>
    <a class="menu-item nav-link {{ request()->routeIs('client.vehicles.*') ? 'active' : '' }}" href="{{ route('client.vehicles.index') }}">🚗 Mis vehículos</a>
    <a class="menu-item nav-link {{ request()->routeIs('client.maintenances.history') ? 'active' : '' }}" href="{{ route('client.maintenances.history') }}">📜 Historial</a>
    <a class="menu-item nav-link {{ request()->routeIs('client.maintenances.upcoming') ? 'active' : '' }}" href="{{ route('client.maintenances.upcoming') }}">📅 Próximos</a>
    <a class="menu-item nav-link {{ request()->routeIs('client.orders.*') ? 'active' : '' }}" href="{{ route('client.orders.index') }}">📋 Órdenes</a>
    <a class="menu-item nav-link {{ request()->routeIs('client.expenses.*') ? 'active' : '' }}" href="{{ route('client.expenses.index') }}">💰 Gastos</a>
    <a class="menu-item nav-link {{ request()->routeIs('client.notifications.*') ? 'active' : '' }}" href="{{ route('client.notifications.index') }}">🔔 Notificaciones</a>
    <a class="menu-item nav-link {{ request()->routeIs('client.profile.*') ? 'active' : '' }}" href="{{ route('client.profile.edit') }}">👤 Perfil</a>
    <a class="menu-item nav-link {{ request()->routeIs('client.chatbot.*') ? 'active' : '' }}" href="{{ route('client.chatbot.index') }}">🤖 Chatbot</a>
</nav>

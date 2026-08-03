<nav class="menu nav flex-column" aria-label="Menú asesor de servicio">
    <a class="menu-item nav-link {{ request()->routeIs('advisor.dashboard') ? 'active' : '' }}" href="{{ route('advisor.dashboard') }}">📊 Dashboard</a>
    <a class="menu-item nav-link {{ request()->routeIs('advisor.orders.*') ? 'active' : '' }}" href="{{ route('advisor.orders.index') }}">📋 Órdenes de trabajo</a>
    <a class="menu-item nav-link {{ request()->routeIs('advisor.appointments.*') ? 'active' : '' }}" href="{{ route('advisor.appointments.index') }}">🤖 Solicitudes chatbot</a>
</nav>

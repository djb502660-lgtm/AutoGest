<nav class="menu" aria-label="Menú asesor de servicio">
    <a class="menu-item {{ request()->routeIs('advisor.dashboard') ? 'active' : '' }}" href="{{ route('advisor.dashboard') }}">📊 Dashboard</a>
    <a class="menu-item {{ request()->routeIs('advisor.orders.*') ? 'active' : '' }}" href="{{ route('advisor.orders.index') }}">📋 Órdenes de trabajo</a>
    <a class="menu-item {{ request()->routeIs('advisor.chatbot-appointments.*') ? 'active' : '' }}" href="{{ route('advisor.chatbot-appointments.index') }}">🤖 Solicitudes chatbot</a>
</nav>

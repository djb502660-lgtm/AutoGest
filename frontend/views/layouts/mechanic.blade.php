<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoGest • @yield('title', 'Mecánico')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/autogest-ui.css') }}">
    @stack('styles')
</head>
<body data-theme="mechanic">
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">AG</div>
                <div>
                    <h1>AutoGest</h1>
                    <p class="brand-text">Taller · Mecánico</p>
                </div>
            </div>
            <div class="user-box">
                <strong>{{ auth()->user()->name }}</strong>
                Mecánico
            </div>
            <nav class="menu">
                <a class="menu-item {{ request()->routeIs('mechanic.dashboard') ? 'active' : '' }}" href="{{ route('mechanic.dashboard') }}">📊 Dashboard</a>
                <a class="menu-item {{ request()->routeIs('mechanic.orders.*') ? 'active' : '' }}" href="{{ route('mechanic.orders.index') }}">📋 Órdenes de servicio</a>
                <a class="menu-item {{ request()->routeIs('mechanic.maintenances.*') ? 'active' : '' }}" href="{{ route('mechanic.maintenances.create') }}">🛠️ Registrar mantenimiento</a>
                <a class="menu-item {{ request()->routeIs('mechanic.vehicles.*') ? 'active' : '' }}" href="{{ route('mechanic.vehicles.index') }}">🚗 Vehículos</a>
                <a class="menu-item {{ request()->routeIs('mechanic.history') ? 'active' : '' }}" href="{{ route('mechanic.history') }}">📜 Historial</a>
            </nav>
            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;">Cerrar sesión</button>
                </form>
            </div>
        </aside>
        <main class="main">
            <header class="topbar">
                <div class="top-copy">
                    <h2>@yield('heading')</h2>
                    @hasSection('subheading')<p>@yield('subheading')</p>@endif
                </div>
                <div class="top-actions">@yield('top-actions')</div>
            </header>
            <section class="content">
                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoGest • @yield('title', 'Panel')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/autogest-ui.css') }}">
    @stack('styles')
</head>
<body data-theme="admin">
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">AG</div>
                <div>
                    <h1>AutoGest</h1>
                    <p>Centro de operaciones</p>
                </div>
            </div>

            <nav class="menu">
                <div class="menu-label">Principal</div>
                <a class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">📊 Dashboard</a>

                <div class="menu-label">Gestión</div>
                <a class="menu-item {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">🚗 Vehículos</a>
                <a class="menu-item {{ request()->routeIs('maintenances.*') ? 'active' : '' }}" href="{{ route('maintenances.index') }}">🛠️ Mantenimientos</a>
                <a class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">👥 Usuarios</a>

                <div class="menu-label">Control</div>
                <a class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">📈 Reportes</a>
                <a class="menu-item {{ request()->routeIs('calendar.*') ? 'active' : '' }}" href="{{ route('calendar.index') }}">📅 Calendario</a>

                <div class="menu-label">Cuenta</div>
                <a class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">👤 Mi perfil</a>
            </nav>

            <div class="sidebar-footer">
                <strong>Sesión activa</strong><br>
                {{ auth()->user()->name }} · {{ auth()->user()->role->label() }}
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div class="top-copy">
                    <h2>@yield('heading')</h2>
                    <p>@yield('subheading')</p>
                </div>
                <div class="top-actions">
                    @yield('top-actions')
                    <a href="{{ route('profile.edit') }}" class="btn btn-secondary btn-sm">Mi perfil</a>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="logout">Cerrar sesión</button>
                    </form>
                </div>
            </header>

            <section class="content">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>

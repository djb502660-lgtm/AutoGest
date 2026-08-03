<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AutoGest • @yield('title', 'Mecánico')</title>
  <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  @include('layouts.partials.bootstrap-head')
  <style>
    :root {
      --primary: #0284c7;
      --primary-light: #e0f2fe;
      --accent: #ea580c;
      --accent-light: #fff7ed;
      --bg-body: #f8fafc;
      --bg-card: #ffffff;
      --text-main: #1e293b;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
      --sidebar-width: 250px;
    }

    * { box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    body { background-color: var(--bg-body); color: var(--text-main); display: flex; min-height: 100vh; margin: 0; padding: 0; }
    
    /* Evitar conflicto con bootstrap row/col si no es necesario */
    .container-fluid { padding: 0; }
    .row { margin: 0; }

    /* NAVEGACIÓN LATERAL */
    .sidebar {
      width: var(--sidebar-width);
      background-color: var(--bg-card);
      border-right: 1px solid var(--border-color);
      display: flex; flex-direction: column;
      padding: 20px 16px; position: fixed; height: 100vh; z-index: 1000;
    }

    .brand { display: flex; align-items: center; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color); margin-bottom: 16px; }
    .brand-logo { width: 38px; height: 38px; background: var(--accent); color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem; }

    .user-profile { background: #f1f5f9; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    .user-profile h4 { font-size: 0.9rem; color: var(--text-main); margin:0 0 4px; }
    .user-profile p { font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin:0; }

    .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 6px; padding: 0; margin: 0; }
    .nav-item a {
      display: flex; align-items: center; gap: 12px;
      padding: 10px 12px; color: var(--text-muted);
      text-decoration: none; border-radius: 8px;
      font-size: 0.88rem; font-weight: 600; transition: all 0.2s;
    }
    .nav-item a:hover, .nav-item.active a { background-color: var(--accent-light); color: var(--accent); }

    .logout-item { margin-top: auto; }
    .logout-item a { color: #ef4444; }
    .logout-item a:hover { background: #fef2f2; }

    /* CONTENIDO PRINCIPAL */
    .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 28px; width: calc(100% - var(--sidebar-width)); }

    /* ENCABEZADO CON NOTIFICACIONES */
    .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .top-header h1 { font-size: 1.4rem; font-weight: 700; margin:0 0 4px; }
    .top-header p { font-size: 0.85rem; color: var(--text-muted); margin:0; }

    .notification-btn {
      position: relative; background: white; border: 1px solid var(--border-color);
      width: 42px; height: 42px; border-radius: 10px; display: flex;
      align-items: center; justify-content: center; font-size: 1.2rem;
      color: var(--text-main); cursor: pointer; text-decoration: none; transition: background 0.2s;
    }
    .notification-btn:hover { background: #f1f5f9; }
    .notification-badge {
      position: absolute; top: 6px; right: 6px; width: 10px; height: 10px;
      background: #ef4444; border-radius: 50%; border: 2px solid white;
    }
    
    @media (max-width: 991px) {
        .sidebar { display: none; }
        .main-content { margin-left: 0; width: 100%; padding: 16px; }
        /* Add offcanvas or simple mobile support if needed, but keeping it simple for now based on provided HTML */
    }
  </style>
  @stack('styles')
</head>
<body data-theme="mechanic">

  <!-- BARRA LATERAL -->
  <aside class="sidebar d-none d-lg-flex">
    <div class="brand">
      <div class="brand-logo"><i class="fa-solid fa-wrench"></i></div>
      <div>
        <strong style="font-size: 1rem;">AutoGest</strong>
        <p style="font-size: 0.72rem; color: var(--text-muted); margin:0;">Taller • Mecánico</p>
      </div>
    </div>

    <div class="user-profile">
      <h4>{{ auth()->user()->name ?? 'Mecánico' }}</h4>
      <p><i class="fa-solid fa-screwdriver-wrench"></i> Técnico Especialista</p>
    </div>

    <ul class="nav-menu">
      <li class="nav-item {{ request()->routeIs('mechanic.dashboard') ? 'active' : '' }}">
        <a href="{{ route('mechanic.dashboard') }}"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
      </li>
      <li class="nav-item {{ request()->routeIs('mechanic.orders.*') ? 'active' : '' }}">
        <a href="{{ route('mechanic.orders.index') }}"><i class="fa-solid fa-list-check"></i> Órdenes de Servicio</a>
      </li>
      <li class="nav-item {{ request()->routeIs('mechanic.vehicles.*') ? 'active' : '' }}">
        <a href="{{ route('mechanic.vehicles.index') }}"><i class="fa-solid fa-car"></i> Vehículos en Taller</a>
      </li>
      <li class="nav-item {{ request()->routeIs('mechanic.history') ? 'active' : '' }}">
        <a href="{{ route('mechanic.history') }}"><i class="fa-solid fa-clock-rotate-left"></i> Historial</a>
      </li>
      
      <li class="nav-item logout-item">
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
      </li>
    </ul>
  </aside>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="main-content">
    
    <!-- ENCABEZADO CON CAMPANITA DE NOTIFICACIÓN -->
    <div class="top-header">
      <div>
        <h1>@yield('heading', 'Panel de Trabajo del Mecánico')</h1>
        <p>@yield('subheading', 'Agenda general del taller y resumen del estado de los servicios.')</p>
      </div>
      
      <div class="d-flex align-items-center gap-3">
          @yield('top-actions')
          <!-- Campanita con dropdown -->
          <div class="dropdown">
            <a href="#" class="notification-btn" title="Notificaciones" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa-regular fa-bell"></i>
              @php $newOrders = \App\Models\ServiceOrder::where('status', 'recibida')->count(); @endphp
              @if($newOrders > 0)
                <span class="notification-badge"></span>
              @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="notifDropdown" style="min-width: 250px;">
              <li><h6 class="dropdown-header">Notificaciones</h6></li>
              @if($newOrders > 0)
                <li>
                  <a class="dropdown-item d-flex flex-column" href="{{ route('mechanic.orders.index') }}">
                    <strong>Nueva orden recibida</strong>
                    <span class="text-muted small">Tienes {{ $newOrders }} orden(es) por atender. Ver detalle.</span>
                  </a>
                </li>
              @else
                <li><span class="dropdown-item text-muted text-center py-3">Sin notificaciones</span></li>
              @endif
            </ul>
          </div>
      </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @yield('content')

  </main>

  @include('layouts.partials.bootstrap-scripts')
  @stack('scripts')
</body>
</html>

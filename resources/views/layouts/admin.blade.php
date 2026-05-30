<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoGest • @yield('title', 'Panel')</title>
    <style>
        :root {
            --text:#f8fafc;
            --muted:#9db0ca;
            --accent:#22c55e;
            --warning:#fbbf24;
            --danger:#fb7185;
            --shadow:0 26px 80px rgba(2,6,23,0.62);
        }
        * { box-sizing:border-box; }
        body {
            margin:0; min-height:100vh; color:var(--text);
            font-family:Inter,system-ui,sans-serif;
            background:
                radial-gradient(circle at top, rgba(14,116,144,0.2), transparent 28%),
                linear-gradient(180deg, #01040d, #020817 24%, #01040d);
        }
        .shell { display:grid; grid-template-columns:300px 1fr; min-height:100vh; }
        .sidebar { padding:24px; border-right:1px solid rgba(148,163,184,0.12); background:rgba(2,6,23,0.7); display:flex; flex-direction:column; gap:24px; }
        .brand { display:flex; align-items:center; gap:12px; }
        .brand-mark { width:38px; height:38px; border-radius:12px; display:grid; place-items:center; font-weight:800; color:#021b0d; background:linear-gradient(180deg,#22c55e,#0ea5e9); }
        .brand h1 { margin:0; font-size:1.1rem; }
        .brand p { margin:2px 0 0; color:var(--muted); font-size:0.72rem; }
        .menu-label { color:var(--muted); font-size:0.65rem; font-weight:800; letter-spacing:0.16em; text-transform:uppercase; margin:8px 0 4px; }
        .menu { display:flex; flex-direction:column; gap:8px; }
        .menu-item { display:flex; align-items:center; gap:10px; padding:12px 13px; border-radius:12px; color:#d8e5ff; text-decoration:none; background:rgba(8,15,29,0.58); border:1px solid rgba(148,163,184,0.08); font-size:0.94rem; }
        .menu-item.active { background:linear-gradient(180deg,rgba(14,116,144,0.25),rgba(34,197,94,0.18)); border-color:rgba(34,197,94,0.26); }
        .sidebar-footer { margin-top:auto; border-radius:16px; padding:14px; background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.08); color:var(--muted); font-size:0.82rem; line-height:1.35; }
        .main { display:flex; flex-direction:column; min-width:0; }
        .topbar { display:flex; align-items:center; justify-content:space-between; padding:24px 26px 16px; border-bottom:1px solid rgba(148,163,184,0.1); gap:16px; flex-wrap:wrap; }
        .top-copy h2 { margin:0; font-size:1.4rem; }
        .top-copy p { margin:4px 0 0; color:var(--muted); }
        .top-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .btn { display:inline-flex; align-items:center; gap:8px; border:0; border-radius:10px; padding:10px 14px; font-size:0.82rem; font-weight:800; text-decoration:none; cursor:pointer; }
        .btn-primary { color:#021b0d; background:linear-gradient(180deg,#22c55e,#16a34a); }
        .btn-secondary { color:#dbeafe; background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.14); }
        .btn-danger { color:#fff; background:linear-gradient(180deg,#fb7185,#e11d48); }
        .btn-warning { color:#021b0d; background:linear-gradient(180deg,#fbbf24,#f59e0b); }
        .logout { border:0; border-radius:10px; padding:10px 12px; font-size:0.78rem; font-weight:800; color:#021b0d; background:linear-gradient(180deg,#fbbf24,#f59e0b); cursor:pointer; }
        .content { padding:22px; display:flex; flex-direction:column; gap:20px; }
        .panel { background:rgba(6,13,27,0.84); border:1px solid rgba(148,163,184,0.12); border-radius:18px; padding:16px; box-shadow:var(--shadow); }
        .alert { padding:12px 14px; border-radius:12px; font-size:0.86rem; }
        .alert-success { background:rgba(34,197,94,0.14); border:1px solid rgba(34,197,94,0.28); color:#86efac; }
        .alert-error { background:rgba(251,113,133,0.14); border:1px solid rgba(251,113,133,0.28); color:#fda4af; }
        .table { width:100%; border-collapse:collapse; font-size:0.82rem; }
        .table th, .table td { text-align:left; padding:12px 8px; border-bottom:1px solid rgba(148,163,184,0.08); vertical-align:middle; }
        .table th { color:var(--muted); font-size:0.68rem; text-transform:uppercase; letter-spacing:0.12em; }
        .badge { border-radius:999px; padding:5px 8px; font-size:0.58rem; font-weight:800; letter-spacing:0.08em; text-transform:uppercase; white-space:nowrap; }
        .badge.green { background:rgba(34,197,94,0.16); color:#86efac; }
        .badge.yellow { background:rgba(251,191,36,0.16); color:#fde68a; }
        .badge.red { background:rgba(251,113,133,0.16); color:#fda4af; }
        .badge.blue { background:rgba(14,165,233,0.16); color:#7dd3fc; }
        .filters { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
        .filters input, .filters select, .field input, .field select { border-radius:10px; border:1px solid rgba(96,165,250,0.25); background:rgba(2,6,23,0.92); color:var(--text); padding:10px 12px; font-size:0.88rem; }
        .filters input { min-width:220px; flex:1; }
        .field { display:flex; flex-direction:column; gap:8px; margin-bottom:14px; }
        .field label { font-size:0.75rem; font-weight:700; color:#d0e1ff; text-transform:uppercase; letter-spacing:0.1em; }
        .field-error { color:#fda4af; font-size:0.78rem; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:8px; }
        .actions-inline { display:flex; gap:6px; }
        .actions-inline form { margin:0; }
        .btn-sm { padding:6px 10px; font-size:0.72rem; }
        .pagination { display:flex; gap:8px; flex-wrap:wrap; margin-top:16px; }
        .pagination a, .pagination span { padding:8px 12px; border-radius:8px; text-decoration:none; color:#dbeafe; background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.08); font-size:0.82rem; }
        .pagination .active { background:rgba(34,197,94,0.18); border-color:rgba(34,197,94,0.28); }
        @media (max-width:900px) { .shell { grid-template-columns:1fr; } .form-grid { grid-template-columns:1fr; } }
    </style>
    @stack('styles')
</head>
<body>
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

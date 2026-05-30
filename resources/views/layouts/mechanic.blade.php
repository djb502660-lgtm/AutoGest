<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoGest • @yield('title', 'Mecánico')</title>
    <style>
        :root { --text:#f8fafc; --muted:#94a3b8; --accent:#22c55e; --warning:#fbbf24; --shadow:0 26px 80px rgba(2,6,23,0.62); }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--text); font-family:Inter,system-ui,sans-serif; background:linear-gradient(180deg,#01040d,#020817); }
        .shell { display:grid; grid-template-columns:260px 1fr; min-height:100vh; }
        .sidebar { padding:20px; background:#0f172a; border-right:1px solid rgba(148,163,184,0.12); display:flex; flex-direction:column; gap:16px; }
        .brand { font-weight:800; font-size:1.1rem; color:#86efac; margin-bottom:4px; }
        .user-box { padding:12px; border-radius:12px; background:rgba(8,15,29,0.72); font-size:0.82rem; color:var(--muted); }
        .user-box strong { display:block; color:var(--text); margin-bottom:4px; }
        .menu { display:flex; flex-direction:column; gap:4px; }
        .menu-item { padding:10px 12px; border-radius:10px; color:#dbeafe; text-decoration:none; font-size:0.88rem; }
        .menu-item.active { background:rgba(34,197,94,0.18); color:#86efac; font-weight:700; }
        .menu-item:hover { background:rgba(148,163,184,0.08); }
        .sidebar-footer { margin-top:auto; }
        .main { min-width:0; }
        .topbar { padding:20px 24px; border-bottom:1px solid rgba(148,163,184,0.1); display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
        .topbar h2 { margin:0; font-size:1.35rem; }
        .topbar p { margin:4px 0 0; color:var(--muted); font-size:0.88rem; }
        .content { padding:20px 24px; }
        .panel { background:rgba(6,13,27,0.84); border:1px solid rgba(148,163,184,0.12); border-radius:16px; padding:16px; box-shadow:var(--shadow); margin-bottom:16px; }
        .stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
        .stat { padding:16px; border-radius:14px; background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.08); }
        .stat span { color:var(--muted); font-size:0.72rem; text-transform:uppercase; letter-spacing:.08em; }
        .stat strong { display:block; font-size:1.8rem; margin-top:6px; }
        .btn { display:inline-flex; align-items:center; gap:6px; border:0; border-radius:10px; padding:10px 14px; font-size:0.82rem; font-weight:700; text-decoration:none; cursor:pointer; }
        .btn-primary { background:linear-gradient(180deg,#22c55e,#16a34a); color:#021b0d; }
        .btn-secondary { background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.14); color:#dbeafe; }
        .btn-warning { background:linear-gradient(180deg,#fbbf24,#f59e0b); color:#111; }
        .btn-danger { background:linear-gradient(180deg,#fb7185,#e11d48); color:#fff; }
        .btn-sm { padding:6px 10px; font-size:0.72rem; }
        .table { width:100%; border-collapse:collapse; font-size:0.84rem; }
        .table th, .table td { text-align:left; padding:10px 8px; border-bottom:1px solid rgba(148,163,184,0.08); vertical-align:middle; }
        .table th { color:var(--muted); font-size:0.68rem; text-transform:uppercase; }
        .badge { border-radius:999px; padding:4px 8px; font-size:0.62rem; font-weight:800; text-transform:uppercase; }
        .badge.green { background:rgba(34,197,94,0.16); color:#86efac; }
        .badge.yellow { background:rgba(251,191,36,0.16); color:#fde68a; }
        .badge.red { background:rgba(251,113,133,0.16); color:#fda4af; }
        .badge.blue { background:rgba(14,165,233,0.16); color:#7dd3fc; }
        .alert { padding:12px; border-radius:12px; margin-bottom:16px; font-size:0.86rem; }
        .alert-success { background:rgba(34,197,94,0.14); color:#86efac; border:1px solid rgba(34,197,94,0.28); }
        .alert-warn { background:rgba(251,191,36,0.12); color:#fde68a; border:1px solid rgba(251,191,36,0.25); }
        .filters { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
        .filters input, .filters select, .field input, .field select, .field textarea { width:100%; border-radius:10px; border:1px solid rgba(96,165,250,0.25); background:rgba(2,6,23,0.92); color:var(--text); padding:10px 12px; font-size:0.88rem; }
        .field { display:flex; flex-direction:column; gap:6px; margin-bottom:12px; }
        .field label { font-size:0.75rem; font-weight:700; color:#d0e1ff; text-transform:uppercase; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .comment { padding:10px 12px; border-radius:10px; background:rgba(8,15,29,0.72); margin-bottom:8px; font-size:0.84rem; }
        .comment small { color:var(--muted); display:block; margin-top:4px; }
        .tabs { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
        .tab { padding:8px 12px; border-radius:8px; text-decoration:none; color:var(--muted); font-size:0.82rem; font-weight:700; }
        .tab.active { background:rgba(34,197,94,0.18); color:#86efac; }
        .progress-bar { height:10px; border-radius:999px; background:rgba(148,163,184,0.15); overflow:hidden; margin-top:8px; }
        .progress-bar span { display:block; height:100%; background:linear-gradient(90deg,#22c55e,#0ea5e9); }
        .reminder { padding:10px 12px; border-radius:10px; background:rgba(251,191,36,0.1); border:1px solid rgba(251,191,36,0.2); color:#fde68a; font-size:0.82rem; margin-bottom:8px; }
        @media (max-width:900px) { .shell { grid-template-columns:1fr; } .stats, .form-grid, .grid-2 { grid-template-columns:1fr; } }
    </style>
    @stack('styles')
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">AutoGest</div>
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
                    <button type="submit" class="btn btn-warning" style="width:100%;justify-content:center;">Cerrar sesión</button>
                </form>
            </div>
        </aside>
        <main class="main">
            <header class="topbar">
                <div>
                    <h2>@yield('heading')</h2>
                    @hasSection('subheading')<p>@yield('subheading')</p>@endif
                </div>
                <div>@yield('top-actions')</div>
            </header>
            <section class="content">
                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>

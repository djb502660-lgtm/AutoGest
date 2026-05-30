<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoGest • Dashboard</title>
    <style>
        :root {
            --bg:#020817;
            --bg-soft:#030d1f;
            --panel:rgba(6,13,27,0.86);
            --panel-border:rgba(59,130,246,0.18);
            --text:#f8fafc;
            --muted:#9db0ca;
            --accent:#22c55e;
            --accent-2:#0ea5e9;
            --warning:#fbbf24;
            --danger:#fb7185;
            --shadow:0 26px 80px rgba(2,6,23,0.62);
        }
        * { box-sizing:border-box; }
        body {
            margin:0;
            min-height:100vh;
            color:var(--text);
            font-family: Inter, system-ui, sans-serif;
            background:
                radial-gradient(circle at top, rgba(14,116,144,0.2), transparent 28%),
                linear-gradient(180deg, #01040d, #020817 24%, #01040d);
        }
        .shell { display:grid; grid-template-columns:300px 1fr; min-height:100vh; }
        .sidebar { padding:24px; border-right:1px solid rgba(148,163,184,0.12); background:rgba(2,6,23,0.7); backdrop-filter: blur(2px); display:flex; flex-direction:column; gap:24px; }
        .brand { display:flex; align-items:center; gap:12px; }
        .brand-mark {
            width:38px;
            height:38px;
            border-radius:12px;
            display:grid;
            place-items:center;
            font-weight:800;
            color:#021b0d;
            background:linear-gradient(180deg, #22c55e, #0ea5e9);
            box-shadow:0 10px 25px rgba(34,197,94,0.28);
        }
        .brand h1 { margin:0; font-size:1.1rem; }
        .brand p { margin:2px 0 0; color:var(--muted); font-size:0.72rem; }
        .menu { display:flex; flex-direction:column; gap:8px; }
        .menu-item {
            display:flex;
            align-items:center;
            gap:10px;
            padding:12px 13px;
            border-radius:12px;
            color:#d8e5ff;
            text-decoration:none;
            background:rgba(8,15,29,0.58);
            border:1px solid rgba(148,163,184,0.08);
            font-size:0.94rem;
        }
        .menu-item.active { background:linear-gradient(180deg, rgba(14,116,144,0.25), rgba(34,197,94,0.18)); border-color:rgba(34,197,94,0.26); }
        .sidebar-footer {
            margin-top:auto;
            border-radius:16px;
            padding:14px;
            background:rgba(8,15,29,0.72);
            border:1px solid rgba(148,163,184,0.08);
            color:var(--muted);
            font-size:0.82rem;
            line-height:1.35;
        }
        .main { display:flex; flex-direction:column; min-width:0; }
        .topbar { display:flex; align-items:center; justify-content:space-between; padding:24px 26px 16px; border-bottom:1px solid rgba(148,163,184,0.1); gap:16px; }
        .top-copy h2 { margin:0; font-size:1.4rem; }
        .top-copy p { margin:4px 0 0; color:var(--muted); }
        .top-actions { display:flex; align-items:center; gap:10px; }
        .pill {
            padding:10px 12px;
            border-radius:999px;
            border:1px solid rgba(148,163,184,0.14);
            font-size:0.72rem;
            font-weight:700;
            color:#d4e8ff;
            background:rgba(8,15,29,0.72);
        }
        .logout {
            text-decoration:none;
            border-radius:10px;
            padding:10px 12px;
            font-size:0.78rem;
            font-weight:800;
            color:#021b0d;
            background:linear-gradient(180deg, #fbbf24, #f59e0b);
        }
        .content { padding:22px; display:flex; flex-direction:column; gap:20px; }
        .stats-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
        .stat-card {
            background:linear-gradient(180deg, rgba(6,13,27,0.94), rgba(5,10,22,0.94));
            border:1px solid rgba(148,163,184,0.12);
            border-radius:18px;
            padding:16px;
            box-shadow:var(--shadow);
            display:flex;
            flex-direction:column;
            gap:10px;
        }
        .stat-header { display:flex; align-items:center; justify-content:space-between; color:var(--muted); font-size:0.78rem; text-transform:uppercase; letter-spacing:0.14em; }
        .stat-value { font-size:2rem; font-weight:800; }
        .stat-trend { display:flex; align-items:center; gap:8px; font-size:0.72rem; font-weight:700; }
        .trend-up { color:#86efac; }
        .trend-warn { color:#fde68a; }
        .trend-danger { color:#fda4af; }
        .panel-grid { display:grid; grid-template-columns:1.2fr 0.8fr; gap:14px; }
        .panel {
            background:rgba(6,13,27,0.84);
            border:1px solid rgba(148,163,184,0.12);
            border-radius:18px;
            padding:16px;
            box-shadow:var(--shadow);
        }
        .panel h3 { margin:0; font-size:1rem; }
        .panel p.subtle { margin:6px 0 0; color:var(--muted); font-size:0.84rem; }
        .mini-list { display:flex; flex-direction:column; gap:10px; margin-top:14px; }
        .mini-item {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            border-radius:12px;
            padding:12px;
            background:rgba(8,15,29,0.64);
            border:1px solid rgba(148,163,184,0.08);
        }
        .mini-label { display:flex; flex-direction:column; gap:4px; }
        .mini-label strong { font-size:0.84rem; }
        .mini-label span { color:var(--muted); font-size:0.72rem; }
        .badge {
            border-radius:999px;
            padding:5px 8px;
            font-size:0.58rem;
            font-weight:800;
            letter-spacing:0.1em;
            text-transform:uppercase;
        }
        .badge.green { background:rgba(34,197,94,0.16); color:#86efac; }
        .badge.yellow { background:rgba(251,191,36,0.16); color:#fde68a; }
        .badge.red { background:rgba(251,113,133,0.16); color:#fda4af; }
        .table { width:100%; border-collapse:collapse; margin-top:12px; font-size:0.82rem; }
        .table th, .table td { text-align:left; padding:10px 8px; border-bottom:1px solid rgba(148,163,184,0.08); }
        .table th { color:var(--muted); font-size:0.68rem; text-transform:uppercase; letter-spacing:0.12em; }
        .report-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-top:14px; }
        .report-box {
            border-radius:14px;
            padding:14px;
            background:rgba(8,15,29,0.68);
            border:1px solid rgba(148,163,184,0.08);
        }
        .report-box .num { font-size:1.6rem; font-weight:800; margin-top:8px; }
        .ruler { height:8px; border-radius:999px; background:rgba(148,163,184,0.12); margin-top:10px; overflow:hidden; }
        .ruler > span { display:block; height:100%; border-radius:inherit; background:linear-gradient(90deg, #22c55e, #0ea5e9); }
    </style>
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
                <a class="menu-item active" href="/dashboard">📊 Dashboard</a>
                <a class="menu-item" href="{{ route('vehicles.index') }}">🚗 Vehículos</a>
                <a class="menu-item" href="{{ route('maintenances.index') }}">🛠️ Mantenimientos</a>
                <a class="menu-item" href="{{ route('users.index') }}">👥 Usuarios</a>
                <a class="menu-item" href="{{ route('reports.index') }}">📈 Reportes</a>
                <a class="menu-item" href="{{ route('calendar.index') }}">📅 Calendario</a>
            </nav>

            <div class="sidebar-footer">
                <strong>Sesión activa</strong><br>
                {{ $user->name }} · {{ $user->role->label() }}<br>
                Último acceso: monitoreo del sistema.
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div class="top-copy">
                    <h2>Sistema administrativo</h2>
                    <p>Vista táctica del mantenimiento vehicular inteligente.</p>
                </div>

                <div class="top-actions">
                    <div class="pill">Modo operativo: Autonomía inteligente</div>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="logout">Cerrar sesión</button>
                    </form>
                </div>
            </header>

            <section class="content">
                <div class="stats-grid">
                    <article class="stat-card">
                        <div class="stat-header"><span>Vehículos</span><span>↗</span></div>
                        <div class="stat-value">{{ $stats['vehiculos'] }}</div>
                        <div class="stat-trend trend-up">Registrados en el sistema</div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-header"><span>Mantenimientos</span><span>↗</span></div>
                        <div class="stat-value">{{ $stats['mantenimientos'] }}</div>
                        <div class="stat-trend trend-up">Realizados este mes</div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-header"><span>Alertas</span><span>⚠</span></div>
                        <div class="stat-value">{{ $stats['alertas'] }}</div>
                        <div class="stat-trend trend-warn">{{ $stats['alertas_criticas'] }} críticas pendientes</div>
                    </article>

                    <article class="stat-card">
                        <div class="stat-header"><span>Usuarios</span><span>✓</span></div>
                        <div class="stat-value">{{ $stats['usuarios'] }}</div>
                        <div class="stat-trend trend-up">Cuentas activas</div>
                    </article>
                </div>

                <div class="panel-grid">
                    <section class="panel">
                        <h3>Órdenes de servicio recientes</h3>
                        <p class="subtle">Seguimiento de trabajos abiertos y estados de ejecución.</p>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Vehículo</th>
                                    <th>Servicio</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentOrders as $order)
                                <tr>
                                    <td>{{ $order->vehicle->plate }}</td>
                                    <td>{{ $order->description }}</td>
                                    <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                                    <td>{{ $order->scheduled_at?->translatedFormat('d M') ?? $order->created_at->translatedFormat('d M') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4">No hay órdenes registradas.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </section>

                    <section class="panel">
                        <h3>Resumen operativo</h3>
                        <p class="subtle">Distribución de incidencias y salud técnica del parque.</p>

                        <div class="report-grid">
                            <div class="report-box">
                                <div class="badge green">OK</div>
                                <div class="num">{{ $summary['flota_saludable'] }}%</div>
                                <div class="subtle">Flota saludable</div>
                                <div class="ruler"><span style="width:{{ $summary['flota_saludable'] }}%"></span></div>
                            </div>
                            <div class="report-box">
                                <div class="badge yellow">ATENCIÓN</div>
                                <div class="num">{{ $summary['tareas_proximas'] }}</div>
                                <div class="subtle">Tareas próximas</div>
                                <div class="ruler"><span style="width:{{ min($summary['tareas_proximas'] * 8, 100) }}%"></span></div>
                            </div>
                            <div class="report-box">
                                <div class="badge red">CRÍTICO</div>
                                <div class="num">{{ $summary['alertas_criticas'] }}</div>
                                <div class="subtle">Alertas urgentes</div>
                                <div class="ruler"><span style="width:{{ min($summary['alertas_criticas'] * 15, 100) }}%"></span></div>
                            </div>
                            <div class="report-box">
                                <div class="badge green">BITÁCORA</div>
                                <div class="num">{{ $summary['registros_hoy'] }}</div>
                                <div class="subtle">Registros del día</div>
                                <div class="ruler"><span style="width:{{ min($summary['registros_hoy'] * 10, 100) }}%"></span></div>
                            </div>
                        </div>
                    </section>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

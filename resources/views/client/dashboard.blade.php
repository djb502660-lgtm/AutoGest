<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoGest • Portal Cliente</title>
    <style>
        body { margin:0; font-family:Inter,system-ui,sans-serif; background:#020817; color:#f8fafc; }
        .wrap { max-width:960px; margin:0 auto; padding:32px 20px; }
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        h1 { margin:0; font-size:1.5rem; }
        p { color:#94a3b8; margin:6px 0 0; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .card { background:rgba(6,13,27,.9); border:1px solid rgba(148,163,184,.12); border-radius:16px; padding:16px; }
        .card h2 { margin:0 0 10px; font-size:1rem; }
        ul { margin:0; padding-left:18px; color:#cbd5e1; line-height:1.6; }
        .alert { padding:10px; border-radius:10px; margin-bottom:8px; font-size:.85rem; }
        .critical { background:rgba(251,113,133,.12); color:#fda4af; }
        .warning { background:rgba(251,191,36,.12); color:#fde68a; }
        .logout { background:#22c55e; color:#021b0d; border:0; border-radius:10px; padding:10px 14px; font-weight:700; cursor:pointer; }
        @media (max-width:700px){ .grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <div>
                <h1>Portal del Cliente</h1>
                <p>{{ $user->name }} · Seguimiento de vehículos</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout">Cerrar sesión</button>
            </form>
        </header>

        <div class="grid">
            <section class="card">
                <h2>Mis vehículos ({{ $vehicles->count() }})</h2>
                <ul>
                    @forelse ($vehicles as $vehicle)
                    <li>{{ $vehicle->displayName() }} — {{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}</li>
                    @empty
                    <li>No tienes vehículos registrados.</li>
                    @endforelse
                </ul>
            </section>

            <section class="card">
                <h2>Alertas activas</h2>
                @forelse ($alerts as $alert)
                <div class="alert {{ $alert->severity === 'critical' ? 'critical' : 'warning' }}">
                    <strong>{{ $alert->title }}</strong><br>{{ $alert->message }}
                </div>
                @empty
                <p style="color:#94a3b8;margin:0;">Sin alertas pendientes.</p>
                @endforelse
            </section>

            <section class="card" style="grid-column:1/-1;">
                <h2>Órdenes de servicio recientes</h2>
                <ul>
                    @forelse ($orders as $order)
                    <li>{{ $order->vehicle->plate }} — {{ $order->description }} ({{ $order->statusLabel() }})</li>
                    @empty
                    <li>No hay órdenes registradas.</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</body>
</html>

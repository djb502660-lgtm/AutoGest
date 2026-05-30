<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoGest • Panel Mecánico</title>
    <style>
        body { margin:0; font-family:Inter,system-ui,sans-serif; background:#020817; color:#f8fafc; }
        .wrap { max-width:960px; margin:0 auto; padding:32px 20px; }
        header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        h1 { margin:0; font-size:1.5rem; }
        p { color:#94a3b8; margin:6px 0 0; }
        .card { background:rgba(6,13,27,.9); border:1px solid rgba(148,163,184,.12); border-radius:16px; padding:16px; margin-bottom:12px; }
        .badge { display:inline-block; padding:4px 8px; border-radius:999px; font-size:.7rem; font-weight:700; }
        .yellow { background:rgba(251,191,36,.16); color:#fde68a; }
        .green { background:rgba(34,197,94,.16); color:#86efac; }
        .logout { background:#f59e0b; color:#111; border:0; border-radius:10px; padding:10px 14px; font-weight:700; cursor:pointer; }
        table { width:100%; border-collapse:collapse; margin-top:12px; font-size:.88rem; }
        th, td { text-align:left; padding:10px 8px; border-bottom:1px solid rgba(148,163,184,.08); }
        th { color:#94a3b8; font-size:.72rem; text-transform:uppercase; }
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <div>
                <h1>Panel del Mecánico</h1>
                <p>{{ $user->name }} · Órdenes asignadas</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout">Cerrar sesión</button>
            </form>
        </header>

        <div class="card">
            <strong>Mis órdenes de servicio ({{ $orders->count() }})</strong>
            <table>
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Vehículo</th>
                        <th>Servicio</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->vehicle->plate }}</td>
                        <td>{{ $order->description }}</td>
                        <td><span class="badge {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                        <td>{{ ucfirst($order->priority) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5">No tienes órdenes asignadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AutoGest • @yield('title', 'Cliente')</title>
    <style>
        :root { --text:#f8fafc; --muted:#94a3b8; --accent:#38bdf8; --accent-dark:#0ea5e9; --shadow:0 26px 80px rgba(2,6,23,0.62); }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--text); font-family:Inter,system-ui,sans-serif; background:linear-gradient(180deg,#01040d,#020817); }
        .shell { display:grid; grid-template-columns:260px 1fr; min-height:100vh; }
        .sidebar { padding:20px; background:#0f172a; border-right:1px solid rgba(148,163,184,0.12); display:flex; flex-direction:column; gap:16px; }
        .brand { font-weight:800; font-size:1.1rem; color:#7dd3fc; margin-bottom:4px; }
        .user-box { padding:12px; border-radius:12px; background:rgba(8,15,29,0.72); font-size:0.82rem; color:var(--muted); }
        .user-box strong { display:block; color:var(--text); margin-bottom:4px; }
        .menu { display:flex; flex-direction:column; gap:4px; }
        .menu-item { padding:10px 12px; border-radius:10px; color:#dbeafe; text-decoration:none; font-size:0.88rem; }
        .menu-item.active { background:rgba(56,189,248,0.18); color:#7dd3fc; font-weight:700; }
        .menu-item:hover { background:rgba(148,163,184,0.08); }
        .sidebar-footer { margin-top:auto; }
        .main { min-width:0; position:relative; }
        .topbar { padding:20px 24px; border-bottom:1px solid rgba(148,163,184,0.1); display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
        .topbar h2 { margin:0; font-size:1.35rem; }
        .topbar p { margin:4px 0 0; color:var(--muted); font-size:0.88rem; }
        .content { padding:20px 24px 80px; }
        .panel { background:rgba(6,13,27,0.84); border:1px solid rgba(148,163,184,0.12); border-radius:16px; padding:16px; box-shadow:var(--shadow); margin-bottom:16px; }
        .stats { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
        .stat { padding:16px; border-radius:14px; background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.08); }
        .stat span { color:var(--muted); font-size:0.72rem; text-transform:uppercase; letter-spacing:.08em; }
        .stat strong { display:block; font-size:1.8rem; margin-top:6px; }
        .btn { display:inline-flex; align-items:center; gap:6px; border:0; border-radius:10px; padding:10px 14px; font-size:0.82rem; font-weight:700; text-decoration:none; cursor:pointer; }
        .btn-primary { background:linear-gradient(180deg,#38bdf8,#0ea5e9); color:#021b0d; }
        .btn-secondary { background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.14); color:#dbeafe; }
        .btn-sm { padding:6px 10px; font-size:0.72rem; }
        .table { width:100%; border-collapse:collapse; font-size:0.84rem; }
        .table th, .table td { text-align:left; padding:10px 8px; border-bottom:1px solid rgba(148,163,184,0.08); vertical-align:middle; }
        .table th { color:var(--muted); font-size:0.68rem; text-transform:uppercase; }
        .badge { border-radius:999px; padding:4px 8px; font-size:0.62rem; font-weight:800; text-transform:uppercase; }
        .badge.green { background:rgba(34,197,94,0.16); color:#86efac; }
        .badge.yellow { background:rgba(251,191,36,0.16); color:#fde68a; }
        .badge.red { background:rgba(251,113,133,0.16); color:#fda4af; }
        .badge.blue { background:rgba(14,165,233,0.16); color:#7dd3fc; }
        .alert-box { padding:12px; border-radius:12px; margin-bottom:16px; font-size:0.86rem; }
        .alert-success { background:rgba(34,197,94,0.14); color:#86efac; border:1px solid rgba(34,197,94,0.28); }
        .filters { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:14px; }
        .filters input, .filters select, .field input, .field select, .field textarea { width:100%; border-radius:10px; border:1px solid rgba(96,165,250,0.25); background:rgba(2,6,23,0.92); color:var(--text); padding:10px 12px; font-size:0.88rem; }
        .field { display:flex; flex-direction:column; gap:6px; margin-bottom:12px; }
        .field label { font-size:0.75rem; font-weight:700; color:#d0e1ff; text-transform:uppercase; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .vehicle-card { display:flex; gap:14px; padding:14px; border-radius:14px; background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.08); margin-bottom:10px; }
        .vehicle-thumb { width:72px; height:52px; border-radius:10px; background:linear-gradient(135deg,#1e3a5f,#0f172a); display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0; }
        .vehicle-card h4 { margin:0 0 4px; font-size:0.92rem; }
        .vehicle-card p { margin:0; color:var(--muted); font-size:0.78rem; }
        .schedule-card { padding:14px; border-radius:14px; background:rgba(8,15,29,0.72); border-left:3px solid var(--accent); margin-bottom:10px; }
        .schedule-card h4 { margin:0 0 6px; font-size:0.9rem; }
        .schedule-card p { margin:0; color:var(--muted); font-size:0.78rem; }
        .notif-item { padding:12px 14px; border-radius:12px; background:rgba(8,15,29,0.72); border:1px solid rgba(148,163,184,0.08); margin-bottom:8px; }
        .notif-item.unread { border-color:rgba(56,189,248,0.35); }
        .notif-item strong { display:block; margin-bottom:4px; }
        .notif-item small { color:var(--muted); font-size:0.72rem; }
        .chart-wrap { display:flex; align-items:center; gap:24px; flex-wrap:wrap; }
        .donut { width:160px; height:160px; border-radius:50%; position:relative; flex-shrink:0; }
        .donut-hole { position:absolute; inset:28%; border-radius:50%; background:#060d1b; display:flex; align-items:center; justify-content:center; font-size:0.72rem; color:var(--muted); text-align:center; }
        .legend { display:flex; flex-direction:column; gap:8px; font-size:0.82rem; }
        .legend-item { display:flex; align-items:center; gap:8px; }
        .legend-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
        .chatbot-fab { position:fixed; bottom:24px; right:24px; width:52px; height:52px; border-radius:50%; background:linear-gradient(180deg,#38bdf8,#0ea5e9); border:0; color:#021b0d; font-size:1.4rem; cursor:pointer; box-shadow:0 8px 24px rgba(14,165,233,0.4); z-index:100; }
        .chatbot-panel { position:fixed; bottom:88px; right:24px; width:340px; max-height:420px; background:#0f172a; border:1px solid rgba(148,163,184,0.2); border-radius:16px; display:none; flex-direction:column; z-index:100; box-shadow:var(--shadow); overflow:hidden; }
        .chatbot-panel.open { display:flex; }
        .chatbot-header { padding:12px 14px; background:rgba(56,189,248,0.12); font-weight:700; font-size:0.88rem; }
        .chatbot-messages { flex:1; overflow-y:auto; padding:12px; display:flex; flex-direction:column; gap:8px; max-height:280px; }
        .chat-msg { padding:8px 12px; border-radius:12px; font-size:0.82rem; max-width:85%; line-height:1.4; }
        .chat-msg.bot { background:rgba(8,15,29,0.9); align-self:flex-start; }
        .chat-msg.user { background:rgba(56,189,248,0.2); align-self:flex-end; }
        .chatbot-input { display:flex; gap:8px; padding:10px; border-top:1px solid rgba(148,163,184,0.12); }
        .chatbot-input input { flex:1; border-radius:8px; border:1px solid rgba(96,165,250,0.25); background:rgba(2,6,23,0.92); color:var(--text); padding:8px 10px; font-size:0.82rem; }
        .chatbot-input button { border:0; border-radius:8px; background:var(--accent); color:#021b0d; padding:8px 12px; font-weight:700; cursor:pointer; }
        @media (max-width:900px) { .shell { grid-template-columns:1fr; } .stats, .form-grid, .grid-2 { grid-template-columns:1fr; } .chatbot-panel { width:calc(100% - 32px); right:16px; } }
    </style>
    @stack('styles')
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">AutoGest</div>
            <div class="user-box">
                <strong>{{ auth()->user()->name }}</strong>
                Cliente
            </div>
            <nav class="menu">
                <a class="menu-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" href="{{ route('client.dashboard') }}">📊 Dashboard</a>
                <a class="menu-item {{ request()->routeIs('client.vehicles.*') ? 'active' : '' }}" href="{{ route('client.vehicles.index') }}">🚗 Mis vehículos</a>
                <a class="menu-item {{ request()->routeIs('client.maintenances.history') ? 'active' : '' }}" href="{{ route('client.maintenances.history') }}">📜 Historial</a>
                <a class="menu-item {{ request()->routeIs('client.maintenances.upcoming') ? 'active' : '' }}" href="{{ route('client.maintenances.upcoming') }}">📅 Próximos</a>
                <a class="menu-item {{ request()->routeIs('client.orders.*') ? 'active' : '' }}" href="{{ route('client.orders.index') }}">📋 Órdenes</a>
                <a class="menu-item {{ request()->routeIs('client.expenses.*') ? 'active' : '' }}" href="{{ route('client.expenses.index') }}">💰 Gastos</a>
                <a class="menu-item {{ request()->routeIs('client.notifications.*') ? 'active' : '' }}" href="{{ route('client.notifications.index') }}">🔔 Notificaciones</a>
                <a class="menu-item {{ request()->routeIs('client.profile.*') ? 'active' : '' }}" href="{{ route('client.profile.edit') }}">👤 Perfil</a>
                <a class="menu-item {{ request()->routeIs('client.chatbot.*') ? 'active' : '' }}" href="{{ route('client.chatbot.index') }}">🤖 Chatbot</a>
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
                <div>
                    <h2>@yield('heading')</h2>
                    @hasSection('subheading')<p>@yield('subheading')</p>@endif
                </div>
                <div>@yield('top-actions')</div>
            </header>
            <section class="content">
                @if (session('success'))<div class="alert-box alert-success">{{ session('success') }}</div>@endif
                @yield('content')
            </section>
            @unless(request()->routeIs('client.chatbot.*'))
            <button type="button" class="chatbot-fab" id="chatFab" title="AutoGest Bot">🤖</button>
            <div class="chatbot-panel" id="chatPanel">
                <div class="chatbot-header">AutoGest Bot</div>
                <div class="chatbot-messages" id="chatMessages">
                    <div class="chat-msg bot">¡Hola! Soy tu asistente. Pregúntame sobre el estado de tu vehículo, horarios o servicios.</div>
                </div>
                <form class="chatbot-input" id="chatForm">
                    @csrf
                    <input type="text" id="chatInput" placeholder="Escribe tu pregunta..." autocomplete="off">
                    <button type="submit">Enviar</button>
                </form>
            </div>
            @endunless
        </main>
    </div>
    @unless(request()->routeIs('client.chatbot.*'))
    <script>
        const fab = document.getElementById('chatFab');
        const panel = document.getElementById('chatPanel');
        const form = document.getElementById('chatForm');
        const input = document.getElementById('chatInput');
        const messages = document.getElementById('chatMessages');
        fab?.addEventListener('click', () => panel.classList.toggle('open'));
        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;
            appendMsg(text, 'user');
            input.value = '';
            try {
                const res = await fetch('{{ route('client.chatbot.message') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ message: text })
                });
                const data = await res.json();
                appendMsg(data.reply || 'No pude procesar tu mensaje.', 'bot');
            } catch { appendMsg('Error de conexión. Intenta de nuevo.', 'bot'); }
        });
        function appendMsg(text, type) {
            const el = document.createElement('div');
            el.className = 'chat-msg ' + type;
            el.textContent = text;
            messages.appendChild(el);
            messages.scrollTop = messages.scrollHeight;
        }
    </script>
    @endunless
    @stack('scripts')
</body>
</html>

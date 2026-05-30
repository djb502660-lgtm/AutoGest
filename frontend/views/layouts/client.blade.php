<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AutoGest • @yield('title', 'Cliente')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/autogest-ui.css') }}">
    @stack('styles')
</head>
<body data-theme="client">
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">AG</div>
                <div>
                    <h1>AutoGest</h1>
                    <p class="brand-text">Portal cliente</p>
                </div>
            </div>
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
                <div class="top-copy">
                    <h2>@yield('heading')</h2>
                    @hasSection('subheading')<p>@yield('subheading')</p>@endif
                </div>
                <div class="top-actions">@yield('top-actions')</div>
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

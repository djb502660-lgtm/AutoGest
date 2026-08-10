<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AutoGest • @yield('title', 'Cliente')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @include('layouts.partials.bootstrap-head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .notification-btn {
            position: relative;
            background: #38bdf8;
            border: 1px solid #0284c7;
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .notification-btn:hover {
            background: #0284c7;
        }
        .notification-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 10px;
            height: 10px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }
    </style>
    @stack('styles')
</head>
<body data-theme="client">
    <div class="container-fluid g-0 px-0">
        <div class="row g-0 min-vh-100">
            <div class="col-12 d-lg-none mobile-topbar border-bottom bg-white px-3 py-2 d-flex align-items-center justify-content-between sticky-top">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#clientSidebar" aria-controls="clientSidebar">
                    ☰ Menú
                </button>
                <span class="fw-bold">AutoGest</span>
                <span class="small text-muted">{{ auth()->user()->name }}</span>
            </div>

            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="clientSidebar" aria-labelledby="clientSidebarLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title" id="clientSidebarLabel">Portal cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                </div>
                <div class="offcanvas-body sidebar p-3 d-flex flex-column">
                    @include('layouts.partials.panel-brand', ['subtitle' => 'Portal cliente'])
                    <div class="user-box">
                        <strong>{{ auth()->user()->name }}</strong>
                        Cliente
                    </div>
                    @include('layouts.partials.nav-client')
                    <div class="sidebar-footer mt-auto">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn logout w-100">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </div>

            <aside class="col-lg-auto d-none d-lg-flex flex-column sidebar border-end">
                @include('layouts.partials.panel-brand', ['subtitle' => 'Portal cliente'])
                <div class="user-box">
                    <strong>{{ auth()->user()->name }}</strong>
                    Cliente
                </div>
                @include('layouts.partials.nav-client')
                <div class="sidebar-footer mt-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn logout w-100">Cerrar sesión</button>
                    </form>
                </div>
            </aside>

            <main class="col min-vh-100 d-flex flex-column main position-relative">
                <header class="topbar">
                    <div class="top-copy">
                        <h2>@yield('heading')</h2>
                        @hasSection('subheading')<p>@yield('subheading')</p>@endif
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="top-actions">@yield('top-actions')</div>
                        <!-- Campanita con dropdown cliente -->
                        <div class="dropdown">
                            <a href="#" class="notification-btn" title="Notificaciones" id="clientNotifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-regular fa-bell"></i>
                                @php
                                    $clientAlerts = auth()->user()->alerts()->where('is_read', false)->latest()->take(5)->get();
                                    $unreadCount = auth()->user()->alerts()->where('is_read', false)->count();
                                @endphp
                                @if($unreadCount > 0)
                                    <span class="notification-badge"></span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="clientNotifDropdown" style="min-width: 280px; max-width: 320px;">
                                <li><h6 class="dropdown-header">Notificaciones</h6></li>
                                @if($unreadCount > 0)
                                    @foreach($clientAlerts as $alert)
                                        <li>
                                            <a class="dropdown-item d-flex flex-column py-2 border-bottom" href="{{ route('client.notifications.index') }}">
                                                <strong class="text-primary" style="font-size:0.85rem;">{{ $alert->title }}</strong>
                                                <span class="text-muted small" style="font-size:0.78rem;">{{ Str::limit($alert->message, 50) }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                    <li>
                                        <a class="dropdown-item text-center text-primary fw-bold small py-2" href="{{ route('client.notifications.index') }}">
                                            Ver todas las notificaciones ({{ $unreadCount }})
                                        </a>
                                    </li>
                                @else
                                    <li><span class="dropdown-item text-muted text-center py-3">Sin notificaciones</span></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </header>
                <section class="content flex-grow-1">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    @endif
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
                        <input type="text" id="chatInput" class="form-control form-control-sm" placeholder="Escribe tu pregunta..." autocomplete="off">
                        <button type="submit" class="btn btn-primary btn-sm">Enviar</button>
                    </form>
                </div>
                @endunless
            </main>
        </div>
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
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content
                    || document.querySelector('input[name="_token"]')?.value;
                const res = await fetch('{{ route('client.chatbot.message') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ message: text })
                });
                if (!res.ok) {
                    appendMsg('Error del servidor (' + res.status + '). ¿Ejecutaste las migraciones?', 'bot');
                    return;
                }
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
    @include('layouts.partials.bootstrap-scripts')
    @stack('scripts')
</body>
</html>

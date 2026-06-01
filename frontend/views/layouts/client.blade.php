<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AutoGest • @yield('title', 'Cliente')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @include('layouts.partials.bootstrap-head')
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
                            <button type="submit" class="btn btn-secondary w-100">Cerrar sesión</button>
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
                        <button type="submit" class="btn btn-secondary w-100">Cerrar sesión</button>
                    </form>
                </div>
            </aside>

            <main class="col min-vh-100 d-flex flex-column main position-relative">
                <header class="topbar">
                    <div class="top-copy">
                        <h2>@yield('heading')</h2>
                        @hasSection('subheading')<p>@yield('subheading')</p>@endif
                    </div>
                    <div class="top-actions">@yield('top-actions')</div>
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
    @include('layouts.partials.bootstrap-scripts')
    @stack('scripts')
</body>
</html>

@extends('layouts.panel')

@section('theme', 'client')
@section('nav-partial', 'layouts.partials.nav-client')
@section('brand-subtitle', 'Portal cliente')
@section('sidebar-id', 'clientSidebar')
@section('offcanvas-title', 'Portal cliente')
@section('role-label', 'Cliente')
@section('main-class', 'main-relative')

@section('notifications')
    <div class="dropdown">
        <a href="#" class="notification-btn" title="Notificaciones" id="clientNotifDropdown" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
            <i class="fa-regular fa-bell"></i>
            @php
                $clientAlerts = auth()->user()->alerts()->where('is_read', false)->latest()->take(5)->get();
                $unreadCount = auth()->user()->alerts()->where('is_read', false)->count();
            @endphp
            @if($unreadCount > 0)
                <span class="notification-badge"></span>
            @endif
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="clientNotifDropdown">
            <li><h6 class="dropdown-header">Notificaciones</h6></li>
            @if($unreadCount > 0)
                @foreach($clientAlerts as $alert)
                    <li>
                        <a class="dropdown-item" href="{{ route('client.notifications.index') }}">
                            <strong>{{ $alert->title }}</strong>
                            <span class="muted">{{ Str::limit($alert->message, 50) }}</span>
                        </a>
                    </li>
                @endforeach
                <li>
                    <a class="dropdown-item" href="{{ route('client.notifications.index') }}">
                        Ver todas las notificaciones ({{ $unreadCount }})
                    </a>
                </li>
            @else
                <li><span class="dropdown-item muted">Sin notificaciones</span></li>
            @endif
        </ul>
    </div>
@endsection

@section('after-content')
    @unless(request()->routeIs('client.chatbot.*'))
        <button type="button" class="chatbot-fab" id="chatFab" title="Abrir AutoGest Bot" aria-controls="chatPanel" aria-expanded="false">
            🤖
            <span class="visually-hidden">Abrir AutoGest Bot</span>
        </button>
        <div class="chatbot-panel" id="chatPanel" role="dialog" aria-labelledby="chatbotTitle" aria-hidden="true">
            <div class="chatbot-header">
                <strong id="chatbotTitle">AutoGest Bot</strong>
            </div>
            <div class="chatbot-messages" id="chatMessages">
                <div class="chat-msg bot">¡Hola! Soy tu asistente. Pregúntame sobre el estado de tu vehículo, horarios o servicios.</div>
            </div>
            <form class="chatbot-input" id="chatForm" data-message-url="{{ route('client.chatbot.message') }}">
                @csrf
                <label class="visually-hidden" for="chatInput">Escribe tu pregunta</label>
                <input type="text" id="chatInput" class="chatbot-field" placeholder="Escribe tu pregunta..." autocomplete="off">
                <button type="submit">Enviar</button>
            </form>
        </div>
    @endunless
@endsection

@section('page-scripts')
    @unless(request()->routeIs('client.chatbot.*'))
    @include('layouts.partials.chatbot-markup')
    <script>
        const fab = document.getElementById('chatFab');
        const panel = document.getElementById('chatPanel');
        const form = document.getElementById('chatForm');
        const input = document.getElementById('chatInput');
        const messages = document.getElementById('chatMessages');

        fab?.addEventListener('click', () => {
            const willOpen = !panel.classList.contains('open');
            panel.classList.toggle('open', willOpen);
            panel.setAttribute('aria-hidden', willOpen ? 'false' : 'true');
            fab.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            if (willOpen) {
                input?.focus();
            }
        });

        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;
            appendMsg(text, 'user');
            input.value = '';
            try {
                const csrfToken = document.querySelector('meta[name=csrf-token]')?.content
                    || document.querySelector('input[name="_token"]')?.value;
                const res = await fetch(form.dataset.messageUrl, {
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
            if (type === 'bot') {
                el.innerHTML = window.AutoGestChat.formatReply(text);
            } else {
                el.textContent = text;
            }
            messages.appendChild(el);
            messages.scrollTop = messages.scrollHeight;
        }
    </script>
    @endunless
@endsection

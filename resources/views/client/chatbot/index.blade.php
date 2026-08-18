@extends('layouts.client')

@section('title', 'Chatbot')
@section('heading', 'AutoGest Bot')
@section('subheading')
    Asistente inteligente con IA para consultas mecánicas y de tu vehículo.
@endsection

@section('content')
    <style>
        .typing-indicator {
            display: none;
            align-items: center;
            gap: 4px;
            padding: 8px 12px;
            border-radius: 12px;
            background: rgba(8, 15, 29, 0.9);
            width: fit-content;
            align-self: flex-start;
        }

        .typing-indicator span {
            width: 6px;
            height: 6px;
            background-color: var(--muted, #94a3b8);
            border-radius: 50%;
            display: inline-block;
            animation: typingBounce 1.4s infinite ease-in-out both;
        }

        .typing-indicator span:nth-child(1) {
            animation-delay: -0.32s;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes typingBounce {

            0%,
            80%,
            100% {
                transform: scale(0);
            }

            40% {
                transform: scale(1);
            }
        }

        .chat-msg {
            white-space: pre-line;
            word-break: break-word;
            line-height: 1.45;
        }
    </style>

    <div class="grid-2">
        <!-- Panel Principal del Chat -->
        <div class="panel" style="display:flex;flex-direction:column;height:520px;position:relative;">
            <div id="chatBody"
                style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:10px;margin-bottom:12px;padding-right:4px;">
                @forelse ($messages as $msg)
                    <div class="chat-msg {{ $msg->sender === 'user' ? 'user' : 'bot' }}"
                        style="padding:10px 14px;border-radius:12px;font-size:0.88rem;max-width:85%;{{ $msg->sender === 'user' ? 'background:rgba(56,189,248,0.2);align-self:flex-end;color:#e0f2fe;' : 'background:rgba(8,15,29,0.9);align-self:flex-start;color:#f8fafc;' }}">
                        {{ $msg->message }}
                    </div>
                @empty
                    <div class="chat-msg bot"
                        style="padding:10px 14px;border-radius:12px;font-size:0.88rem;background:rgba(8,15,29,0.9);align-self:flex-start;color:#f8fafc;">
                        ¡Hola! 👋 Soy el asistente virtual de AutoGest. Puedes hacerme cualquier pregunta sobre el estado de tu
                        vehículo, agendamiento de citas, reportes de mantenimiento o consultas de mecánica en general.
                    </div>
                @endforelse

                <div class="typing-indicator" id="typingIndicator">
                    <span></span><span></span><span></span>
                </div>
            </div>

            <form id="chatForm" onsubmit="enviarMensaje(event)" style="display:flex;gap:8px;">
                @csrf
                <input type="text" id="userInput" class="input-field" placeholder="Escribe tu consulta libremente..."
                    autocomplete="off" required
                    style="flex:1;border-radius:10px;border:1px solid rgba(96,165,250,0.25);background:rgba(2,6,23,0.92);color:var(--text, #fff);padding:10px 12px;">
                <button type="submit" class="btn btn-primary" style="padding:10px 18px;">Enviar</button>
            </form>
        </div>

        <!-- Panel de Preguntas Frecuentes y Accesos Rápidos -->
        <div class="panel" style="display:flex;flex-direction:column;gap:12px;">
            <h3 style="margin:0 0 4px;">Preguntas frecuentes y Opciones</h3>
            <p style="font-size:0.82rem;color:var(--muted, #94a3b8);margin:0 0 8px;">Haz clic en cualquiera de las
                siguientes sugerencias para consultar al instante:</p>

            <div class="notif-item"
                style="cursor:pointer;padding:10px;border-radius:8px;background:rgba(15,23,42,0.6);margin-bottom:6px;"
                onclick="preguntarDirecto('1')">
                <strong>1️⃣ Ver estado de mi vehículo</strong>
                <div style="font-size:0.78rem;color:var(--muted, #94a3b8);">Consulta en tiempo real el progreso de tu auto
                    en el taller.</div>
            </div>

            <div class="notif-item"
                style="cursor:pointer;padding:10px;border-radius:8px;background:rgba(15,23,42,0.6);margin-bottom:6px;"
                onclick="preguntarDirecto('2')">
                <strong>2️⃣ Agendar una cita</strong>
                <div style="font-size:0.78rem;color:var(--muted, #94a3b8);">Solicita turno de servicio para tu vehículo.
                </div>
            </div>

            <div class="notif-item"
                style="cursor:pointer;padding:10px;border-radius:8px;background:rgba(15,23,42,0.6);margin-bottom:12px;"
                onclick="preguntarDirecto('3')">
                <strong>3️⃣ Ver mis gastos de mantenimiento</strong>
                <div style="font-size:0.78rem;color:var(--muted, #94a3b8);">Resumen consolidado de tu historial de gastos.
                </div>
            </div>

            @if($faqs->isNotEmpty())
                <h4 style="margin:8px 0 4px;font-size:0.9rem;">Otras consultas</h4>
                @foreach ($faqs as $faq)
                    <div class="notif-item"
                        style="cursor:pointer;padding:8px;border-radius:6px;background:rgba(15,23,42,0.4);margin-bottom:4px;"
                        onclick="preguntarDirecto(@js($faq->question))">
                        <strong>{{ $faq->question }}</strong>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function scrollAlFinal() {
            const chatBody = document.getElementById('chatBody');
            if (chatBody) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        }

        function mostrarCargando(activar) {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) {
                indicator.style.display = activar ? 'flex' : 'none';
                if (activar) scrollAlFinal();
            }
        }

        function agregarMensaje(texto, remitente) {
            const chatBody = document.getElementById('chatBody');
            const typingIndicator = document.getElementById('typingIndicator');

            const div = document.createElement('div');
            div.className = `chat-msg ${remitente}`;
            div.style.cssText = `padding:10px 14px;border-radius:12px;font-size:0.88rem;max-width:85%;white-space:pre-line;word-break:break-word;line-height:1.45;${remitente === 'user'
                ? 'background:rgba(56,189,248,0.2);align-self:flex-end;color:#e0f2fe;'
                : 'background:rgba(8,15,29,0.9);align-self:flex-start;color:#f8fafc;'
                }`;
            div.innerText = texto;

            chatBody.insertBefore(div, typingIndicator);
            scrollAlFinal();
        }

        function enviarMensaje(event) {
            if (event) event.preventDefault();

            const input = document.getElementById('userInput');
            const text = input.value.trim();
            if (!text) return;

            agregarMensaje(text, 'user');
            input.value = '';
            mostrarCargando(true);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]')?.value;

            const targetUrl = "{{ Route::has('client.chatbot.message') ? route('client.chatbot.message') : (Route::has('chatbot.message') ? route('chatbot.message') : '/cliente/chatbot/mensaje') }}";

            fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: text })
            })
                .then(res => {
                    if (!res.ok) throw new Error('Http error ' + res.status);
                    return res.json();
                })
                .then(data => {
                    mostrarCargando(false);
                    agregarMensaje(data.reply || "No se recibió respuesta.", 'bot');
                })
                .catch(err => {
                    mostrarCargando(false);
                    agregarMensaje("Estoy experimentando una breve pausa técnica. ¿Deseas reintentar tu pregunta?", 'bot');
                });
        }
        function preguntarDirecto(texto) {
            const input = document.getElementById('userInput');
            input.value = texto;
            enviarMensaje();
        }

        document.addEventListener('DOMContentLoaded', function () {
            scrollAlFinal();
        });
    </script>
@endpush
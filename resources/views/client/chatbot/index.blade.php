@extends('layouts.client')

@section('title', 'Chatbot')
@section('heading', 'AutoGest Bot')
@section('subheading', 'Consultas sobre tu vehículo, citas, horarios y servicios.')

@section('content')
    <div class="chatbot-page">
        <section class="panel chatbot-room" aria-label="Conversación">
            <div class="chatbot-room-messages" id="chatBody">
                @forelse ($messages as $msg)
                    <div class="chat-msg {{ $msg->sender === 'user' ? 'user' : 'bot' }}">
                        @if ($msg->sender === 'bot')
                            {!! \App\Support\ChatbotMarkup::toHtml((string) $msg->message) !!}
                        @else
                            {{ $msg->message }}
                        @endif
                    </div>
                @empty
                    <div class="chat-msg bot">
                        ¡Hola! Soy el asistente de AutoGest. Pregúntame por el estado de tu vehículo, una cita, horarios o servicios.
                    </div>
                @endforelse

                <div class="chatbot-typing" id="typingIndicator" hidden>
                    <span></span><span></span><span></span>
                </div>
            </div>

            <form class="chatbot-input" id="chatForm" data-message-url="{{ route('client.chatbot.message') }}">
                @csrf
                <label class="visually-hidden" for="userInput">Escribe tu pregunta</label>
                <input type="text" id="userInput" class="chatbot-field" placeholder="Escribe tu pregunta..." autocomplete="off">
                <button type="submit">Enviar</button>
            </form>
        </section>

        <aside class="panel chatbot-suggest" aria-label="Atajos y preguntas frecuentes">
            <h3>Atajos</h3>
            <p class="muted">Pulsa una opción para enviarla al asistente.</p>

            <button type="button" class="chatbot-suggest-item" data-question="1">
                <strong>Estado del vehículo</strong>
                <span>Consulta el progreso de tu auto en el taller.</span>
            </button>
            <button type="button" class="chatbot-suggest-item" data-question="2">
                <strong>Agendar una cita</strong>
                <span>Solicita un turno de servicio.</span>
            </button>
            <button type="button" class="chatbot-suggest-item" data-question="3">
                <strong>Gastos de mantenimiento</strong>
                <span>Resumen de tu historial de gastos.</span>
            </button>

            @if ($faqs->isNotEmpty())
                <h3>Preguntas frecuentes</h3>
                @foreach ($faqs as $faq)
                    <button type="button" class="chatbot-suggest-item" data-question="{{ $faq->question }}">
                        <strong>{{ $faq->question }}</strong>
                    </button>
                @endforeach
            @endif
        </aside>
    </div>
@endsection

@push('scripts')
    @include('layouts.partials.chatbot-markup')
    <script>
        const chatBody = document.getElementById('chatBody');
        const typingIndicator = document.getElementById('typingIndicator');
        const form = document.getElementById('chatForm');
        const input = document.getElementById('userInput');

        function scrollAlFinal() {
            if (chatBody) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        }

        function mostrarCargando(activar) {
            if (!typingIndicator) return;
            typingIndicator.hidden = !activar;
            if (activar) scrollAlFinal();
        }

        function agregarMensaje(texto, remitente) {
            const div = document.createElement('div');
            div.className = 'chat-msg ' + remitente;
            if (remitente === 'bot') {
                div.innerHTML = window.AutoGestChat.formatReply(texto);
            } else {
                div.textContent = texto;
            }
            chatBody.insertBefore(div, typingIndicator);
            scrollAlFinal();
        }

        function enviarMensaje(textoDirecto) {
            const text = (textoDirecto ?? input.value).trim();
            if (!text) return;

            agregarMensaje(text, 'user');
            input.value = '';
            mostrarCargando(true);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]')?.value;

            fetch(form.dataset.messageUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message: text })
            })
                .then((res) => {
                    if (!res.ok) throw new Error('Http error ' + res.status);
                    return res.json();
                })
                .then((data) => {
                    mostrarCargando(false);
                    agregarMensaje(data.reply || 'No se recibió respuesta.', 'bot');
                })
                .catch(() => {
                    mostrarCargando(false);
                    agregarMensaje('Tuve un pequeño contratiempo. ¿Puedes intentarlo de nuevo?', 'bot');
                });
        }

        form?.addEventListener('submit', (event) => {
            event.preventDefault();
            enviarMensaje();
        });

        document.querySelectorAll('[data-question]').forEach((button) => {
            button.addEventListener('click', () => enviarMensaje(button.getAttribute('data-question')));
        });

        document.addEventListener('DOMContentLoaded', scrollAlFinal);
    </script>
@endpush

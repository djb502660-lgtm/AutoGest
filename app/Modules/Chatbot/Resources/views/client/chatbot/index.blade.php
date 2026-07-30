@extends('layouts.client')

@section('title', 'Chatbot')
@section('heading', 'AutoGest Bot')
@section('subheading')
    Asistente inteligente para consultas frecuentes.
@endsection

@section('content')
    <div class="grid-2">
        <div class="panel" style="display:flex;flex-direction:column;height:480px;">
            <div id="chatMessages" style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:8px;margin-bottom:12px;">
                @forelse ($messages as $msg)
                    <div class="chat-msg {{ $msg->sender === 'user' ? 'user' : 'bot' }}" style="padding:8px 12px;border-radius:12px;font-size:0.84rem;max-width:85%;{{ $msg->sender === 'user' ? 'background:rgba(56,189,248,0.2);align-self:flex-end;' : 'background:rgba(8,15,29,0.9);align-self:flex-start;' }}">
                        {{ $msg->message }}
                    </div>
                @empty
                    <div class="chat-msg bot" style="padding:8px 12px;border-radius:12px;font-size:0.84rem;background:rgba(8,15,29,0.9);align-self:flex-start;">
                        ¡Hola! Pregúntame sobre el estado de tu vehículo, horarios o servicios.
                    </div>
                @endforelse
            </div>
            <form id="chatForm" style="display:flex;gap:8px;">
                @csrf
                <input type="text" id="chatInput" placeholder="Escribe tu pregunta..." style="flex:1;border-radius:10px;border:1px solid rgba(96,165,250,0.25);background:rgba(2,6,23,0.92);color:var(--text);padding:10px 12px;">
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        </div>

        <div class="panel">
            <h3 style="margin:0 0 12px;">Preguntas frecuentes</h3>
            @foreach ($faqs as $faq)
                <div class="notif-item" style="cursor:pointer;" onclick="askFaq(this)" data-q="{{ $faq->question }}">
                    <strong>{{ $faq->question }}</strong>
                    <span style="font-size:0.78rem;color:var(--muted);">{{ Str::limit($faq->answer, 80) }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const form = document.getElementById('chatForm');
    const input = document.getElementById('chatInput');
    const messages = document.getElementById('chatMessages');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await sendMessage(input.value.trim());
        input.value = '';
    });
    function askFaq(el) { sendMessage(el.dataset.q); }
    async function sendMessage(text) {
        if (!text) return;
        appendMsg(text, 'user');
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
                appendMsg('Error del servidor (' + res.status + ').', 'bot');
                return;
            }
            const data = await res.json();
            appendMsg(data.reply || 'Sin respuesta.', 'bot');
        } catch {
            appendMsg('Error de conexión.', 'bot');
        }
    }
    function appendMsg(text, type) {
        const el = document.createElement('div');
        el.className = 'chat-msg ' + type;
        el.style.cssText = 'padding:8px 12px;border-radius:12px;font-size:0.84rem;max-width:85%;' + (type === 'user' ? 'background:rgba(56,189,248,0.2);align-self:flex-end;' : 'background:rgba(8,15,29,0.9);align-self:flex-start;');
        el.textContent = text;
        messages.appendChild(el);
        messages.scrollTop = messages.scrollHeight;
    }
    messages.scrollTop = messages.scrollHeight;
</script>
@endpush

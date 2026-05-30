@extends('layouts.client')

@section('title', 'Notificaciones')
@section('heading', 'Notificaciones')
@section('subheading')
    Alertas y recordatorios de tus vehículos.
@endsection

@section('content')
    <div class="panel">
        @forelse ($alerts as $alert)
            <div class="notif-item {{ $alert->is_read ? '' : 'unread' }}">
                <strong>{{ $alert->title }}</strong>
                <span style="display:block;font-size:0.84rem;color:var(--muted);margin:4px 0;">{{ $alert->message }}</span>
                <small>
                    @if ($alert->vehicle) {{ $alert->vehicle->plate }} · @endif
                    {{ $alert->created_at->format('d/m/Y H:i') }}
                    @if (! $alert->is_read)
                        · <form method="POST" action="{{ route('client.notifications.read', $alert) }}" style="display:inline;">
                            @csrf @method('PUT')
                            <button type="submit" style="background:none;border:0;color:#7dd3fc;cursor:pointer;font-size:0.72rem;padding:0;">Marcar leída</button>
                        </form>
                    @endif
                </small>
            </div>
        @empty
            <p style="color:var(--muted);">No tienes notificaciones.</p>
        @endforelse
        <div style="margin-top:12px;">{{ $alerts->links('pagination.simple') }}</div>
    </div>
@endsection

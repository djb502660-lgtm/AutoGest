@extends('layouts.panel')

@section('theme', 'admin')
@section('nav-partial', 'layouts.partials.nav-admin')
@section('brand-subtitle', 'Centro de operaciones')
@section('sidebar-id', 'adminSidebar')
@section('offcanvas-title', 'AutoGest')
@section('sidebar-footer-mode', 'session')

@section('mobile-extra')
    <a href="{{ route('profile.edit') }}" class="btn btn-secondary">Perfil</a>
@endsection

@section('top-actions-extra')
    <a href="{{ route('profile.edit') }}" class="btn btn-secondary desktop-only">Mi perfil</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-secondary logout">Cerrar sesión</button>
    </form>
@endsection

@section('notifications')
    <div class="dropdown">
        <a href="#" class="notification-btn" title="Notificaciones" id="adminNotifDropdown" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
            <i class="fa-regular fa-bell"></i>
            @php
                $adminAlerts = auth()->user()->alerts()->where('is_read', false)->latest()->take(5)->get();
                $adminPendingChatbot = \App\Models\AppointmentRequest::where('status', 'pendiente')->count();
                $adminRecibidas = \App\Models\ServiceOrder::where('status', 'recibida')->count();
                $adminTotalNotifs = $adminAlerts->count() + $adminPendingChatbot + $adminRecibidas;
            @endphp
            @if($adminTotalNotifs > 0)
                <span class="notification-badge"></span>
            @endif
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminNotifDropdown">
            <li><h6 class="dropdown-header">Notificaciones del Sistema</h6></li>
            @if($adminTotalNotifs > 0)
                @foreach($adminAlerts as $alert)
                    <li>
                        <a class="dropdown-item" href="{{ $alert->appointment_request_id ? route('admin.chatbot-appointments.show', $alert->appointment_request_id) : route('admin.chatbot-appointments.index') }}">
                            <strong>{{ $alert->title }}</strong>
                            <span class="muted">{{ \Illuminate\Support\Str::limit($alert->message, 70) }}</span>
                        </a>
                    </li>
                @endforeach
                @if($adminPendingChatbot > 0)
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.chatbot-appointments.index') }}">
                            <strong>Solicitudes Chatbot</strong>
                            <span class="muted">Tienes {{ $adminPendingChatbot }} cita(s) pendiente(s).</span>
                        </a>
                    </li>
                @endif
                @if($adminRecibidas > 0)
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.orders.index') }}">
                            <strong>Órdenes Recibidas</strong>
                            <span class="muted">Tienes {{ $adminRecibidas }} orden(es) en estado recibida.</span>
                        </a>
                    </li>
                @endif
            @else
                <li><span class="dropdown-item muted">Sin notificaciones</span></li>
            @endif
        </ul>
    </div>
@endsection

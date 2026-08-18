@extends('layouts.panel')

@section('theme', 'advisor')
@section('nav-partial', 'layouts.partials.nav-advisor')
@section('brand-subtitle', 'Asesor de servicio')
@section('sidebar-id', 'advisorSidebar')
@section('offcanvas-title', 'Asesor de servicio')
@section('role-label', 'Asesor de servicio')

@section('notifications')
    <div class="dropdown">
        <a href="#" class="notification-btn" title="Notificaciones" id="advisorNotifDropdown" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
            <i class="fa-regular fa-bell"></i>
            @php
                $advisorAlerts = auth()->user()->alerts()->where('is_read', false)->latest()->take(5)->get();
                $pendingChatbot = \App\Models\AppointmentRequest::where('source', 'chatbot')->where('status', 'pendiente')->count();
                $unassignedOrders = \App\Models\ServiceOrder::whereNull('mechanic_id')->whereIn('status', ['recibida', 'en_proceso'])->count();
                $totalNotifs = $advisorAlerts->count() + $pendingChatbot + $unassignedOrders;
            @endphp
            @if($totalNotifs > 0)
                <span class="notification-badge"></span>
            @endif
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="advisorNotifDropdown">
            <li><h6 class="dropdown-header">Notificaciones</h6></li>
            @if($totalNotifs > 0)
                @foreach($advisorAlerts as $alert)
                    <li>
                        <a class="dropdown-item" href="{{ $alert->appointment_request_id ? route('advisor.chatbot-appointments.show', $alert->appointment_request_id) : route('advisor.chatbot-appointments.index') }}">
                            <strong>{{ $alert->title }}</strong>
                            <span class="muted">{{ \Illuminate\Support\Str::limit($alert->message, 70) }}</span>
                        </a>
                    </li>
                @endforeach
                @if($pendingChatbot > 0)
                    <li>
                        <a class="dropdown-item" href="{{ route('advisor.chatbot-appointments.index') }}">
                            <strong>Nueva solicitud chatbot</strong>
                            <span class="muted">Tienes {{ $pendingChatbot }} solicitud(es) de cita por revisar.</span>
                        </a>
                    </li>
                @endif
                @if($unassignedOrders > 0)
                    <li>
                        <a class="dropdown-item" href="{{ route('advisor.orders.index', ['unassigned' => 1]) }}">
                            <strong>Nueva orden / Sin mecánico</strong>
                            <span class="muted">Tienes {{ $unassignedOrders }} orden(es) pendientes por asignar o revisar.</span>
                        </a>
                    </li>
                @endif
            @else
                <li><span class="dropdown-item muted">Sin notificaciones</span></li>
            @endif
        </ul>
    </div>
@endsection

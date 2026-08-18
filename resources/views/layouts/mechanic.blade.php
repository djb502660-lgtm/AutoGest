@extends('layouts.panel')

@section('theme', 'mechanic')
@section('nav-partial', 'layouts.partials.nav-mechanic')
@section('brand-subtitle', 'Taller · Mecánico')
@section('sidebar-id', 'mechanicSidebar')
@section('offcanvas-title', 'Taller · Mecánico')
@section('role-label', 'Técnico especialista')

@section('notifications')
    <div class="dropdown">
        <a href="#" class="notification-btn" title="Notificaciones" id="mechanicNotifDropdown" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
            <i class="fa-regular fa-bell"></i>
            @php
                $newOrders = \App\Models\ServiceOrder::where('mechanic_id', auth()->id())->where('status', 'recibida')->count();
            @endphp
            @if($newOrders > 0)
                <span class="notification-badge"></span>
            @endif
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mechanicNotifDropdown">
            <li><h6 class="dropdown-header">Notificaciones</h6></li>
            @if($newOrders > 0)
                <li>
                    <a class="dropdown-item" href="{{ route('mechanic.orders.index') }}">
                        <strong>Órdenes por iniciar</strong>
                        <span class="muted">Tienes {{ $newOrders }} orden(es) recibidas.</span>
                    </a>
                </li>
            @else
                <li><span class="dropdown-item muted">Sin notificaciones</span></li>
            @endif
        </ul>
    </div>
@endsection

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoGest • @yield('title', 'Panel')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @include('layouts.partials.bootstrap-head')
    @include('layouts.partials.pwa-firebase')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos responsivos simples para admin */
        @media (max-width: 991px) {
            .topbar {
                padding: 1rem !important;
                flex-wrap: wrap !important;
            }
            .topbar h2 {
                font-size: 1.25rem !important;
            }
            .content {
                padding: 1rem !important;
            }
            .stats, .stats-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
            .panel-grid, .form-grid, .grid-2 {
                grid-template-columns: 1fr !important;
            }
            .filters {
                flex-direction: column !important;
            }
            .filters input, .filters select {
                width: 100% !important;
            }
            .top-actions {
                flex-wrap: wrap !important;
                width: 100% !important;
            }
        }
        @media (max-width: 576px) {
            .stats, .stats-grid {
                grid-template-columns: 1fr !important;
            }
            .topbar {
                padding: 0.75rem 1rem !important;
            }
            .topbar h2 {
                font-size: 1.1rem !important;
            }
            .content {
                padding: 0.75rem 1rem !important;
            }
            .notification-btn {
                width: 36px !important;
                height: 36px !important;
                font-size: 1rem !important;
            }
        }
    </style>
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
<body data-theme="admin">
    <div class="container-fluid g-0 px-0">
        <!-- Mobile Topbar -->
        <div class="col-12 d-lg-none mobile-topbar border-bottom bg-white px-3 py-2 d-flex align-items-center justify-content-between sticky-top">
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                ☰ Menú
            </button>
            <span class="fw-bold">AutoGest</span>
            <a href="{{ route('profile.edit') }}" class="btn btn-link btn-sm text-decoration-none">Perfil</a>
        </div>

        <!-- Mobile Sidebar Offcanvas -->
        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="adminSidebarLabel">AutoGest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
            </div>
            <div class="offcanvas-body sidebar p-3">
                @include('layouts.partials.panel-brand', ['subtitle' => 'Centro de operaciones'])
                @include('layouts.partials.nav-admin')
                <div class="sidebar-footer mt-auto">
                    <strong>Sesión activa</strong><br>
                    {{ auth()->user()->name }} · {{ auth()->user()->role->label() }}
                </div>
            </div>
        </div>

        <!-- Desktop Layout -->
        <div class="row g-0 min-vh-100">
            <!-- Desktop Sidebar -->
            <aside class="col-lg-auto d-none d-lg-flex flex-column sidebar border-end">
                @include('layouts.partials.panel-brand', ['subtitle' => 'Centro de operaciones'])
                @include('layouts.partials.nav-admin')
                <div class="sidebar-footer mt-auto">
                    <strong>Sesión activa</strong><br>
                    {{ auth()->user()->name }} · {{ auth()->user()->role->label() }}
                </div>
            </aside>

            <!-- Main Content -->
            <main class="col min-vh-100 d-flex flex-column main">
                <header class="topbar">
                    <div class="top-copy">
                        <h2>@yield('heading')</h2>
                        <p>@yield('subheading')</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="top-actions">
                            @yield('top-actions')
                            <a href="{{ route('profile.edit') }}" class="btn btn-secondary btn-sm d-none d-lg-inline-flex">Mi perfil</a>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm logout">Cerrar sesión</button>
                            </form>
                        </div>
                        <!-- Campanita con dropdown admin -->
                        <div class="dropdown">
                            <a href="#" class="notification-btn" title="Notificaciones" id="adminNotifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-regular fa-bell"></i>
                                @php
                                    $adminPendingChatbot = \App\Models\AppointmentRequest::where('status', 'pendiente')->count();
                                    $adminRecibidas = \App\Models\ServiceOrder::where('status', 'recibida')->count();
                                    $adminTotalNotifs = $adminPendingChatbot + $adminRecibidas;
                                @endphp
                                @if($adminTotalNotifs > 0)
                                    <span class="notification-badge"></span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="adminNotifDropdown" style="min-width: 280px;">
                                <li><h6 class="dropdown-header">Notificaciones del Sistema</h6></li>
                                @if($adminTotalNotifs > 0)
                                    @if($adminPendingChatbot > 0)
                                        <li>
                                            <a class="dropdown-item d-flex flex-column py-2 border-bottom" href="{{ route('dashboard') }}">
                                                <strong class="text-primary"><i class="fa-solid fa-comments me-1"></i> Solicitudes Chatbot</strong>
                                                <span class="text-muted small">Tienes {{ $adminPendingChatbot }} cita(s) pendiente(s).</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if($adminRecibidas > 0)
                                        <li>
                                            <a class="dropdown-item d-flex flex-column py-2" href="{{ route('admin.orders.index') }}">
                                                <strong class="text-warning"><i class="fa-solid fa-wrench me-1"></i> Órdenes Recibidas</strong>
                                                <span class="text-muted small">Tienes {{ $adminRecibidas }} orden(es) en estado recibida.</span>
                                            </a>
                                        </li>
                                    @endif
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
            </main>
        </div>
    </div>

    @include('layouts.partials.bootstrap-scripts')
    @stack('scripts')
</body>
</html>

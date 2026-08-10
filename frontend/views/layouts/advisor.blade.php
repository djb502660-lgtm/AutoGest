<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AutoGest • @yield('title', 'Asesor')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @include('layouts.partials.bootstrap-head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
<body data-theme="advisor">
    <div class="container-fluid g-0 px-0">
        <div class="row g-0 min-vh-100">
            <div class="col-12 d-lg-none mobile-topbar border-bottom bg-white px-3 py-2 d-flex align-items-center justify-content-between sticky-top">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#advisorSidebar" aria-controls="advisorSidebar">
                    ☰ Menú
                </button>
                <span class="fw-bold">AutoGest</span>
                <span class="small text-muted">{{ auth()->user()->name }}</span>
            </div>

            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="advisorSidebar" aria-labelledby="advisorSidebarLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title" id="advisorSidebarLabel">Asesor de servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                </div>
                <div class="offcanvas-body sidebar p-3 d-flex flex-column">
                    @include('layouts.partials.panel-brand', ['subtitle' => 'Asesor de servicio'])
                    <div class="user-box">
                        <strong>{{ auth()->user()->name }}</strong>
                        Asesor de servicio
                    </div>
                    @include('layouts.partials.nav-advisor')
                    <div class="sidebar-footer mt-auto">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn logout w-100">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </div>

            <aside class="col-lg-auto d-none d-lg-flex flex-column sidebar border-end">
                @include('layouts.partials.panel-brand', ['subtitle' => 'Asesor de servicio'])
                <div class="user-box">
                    <strong>{{ auth()->user()->name }}</strong>
                    Asesor de servicio
                </div>
                @include('layouts.partials.nav-advisor')
                <div class="sidebar-footer mt-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn logout w-100">Cerrar sesión</button>
                    </form>
                </div>
            </aside>

            <main class="col min-vh-100 d-flex flex-column main">
                <header class="topbar">
                    <div class="top-copy">
                        <h2>@yield('heading')</h2>
                        @hasSection('subheading')<p>@yield('subheading')</p>@endif
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="top-actions">@yield('top-actions')</div>
                        <!-- Campanita con dropdown -->
                        <div class="dropdown">
                            <a href="#" class="notification-btn" title="Notificaciones" id="advisorNotifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-regular fa-bell"></i>
                                @php
                                    $pendingChatbot = \App\Models\AppointmentRequest::where('status', 'pendiente')->count();
                                    $unassignedOrders = \App\Models\ServiceOrder::whereNull('mechanic_id')->whereIn('status', ['recibida', 'en_proceso'])->count();
                                    $totalNotifs = $pendingChatbot + $unassignedOrders;
                                @endphp
                                @if($totalNotifs > 0)
                                    <span class="notification-badge"></span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="advisorNotifDropdown" style="min-width: 280px;">
                                <li><h6 class="dropdown-header">Notificaciones</h6></li>
                                @if($totalNotifs > 0)
                                    @if($pendingChatbot > 0)
                                        <li>
                                            <a class="dropdown-item d-flex flex-column py-2 border-bottom" href="{{ route('advisor.appointments.index') }}">
                                                <strong class="text-primary"><i class="fa-solid fa-comments me-1"></i> Nueva solicitud chatbot</strong>
                                                <span class="text-muted small">Tienes {{ $pendingChatbot }} solicitud(es) de cita por revisar.</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if($unassignedOrders > 0)
                                        <li>
                                            <a class="dropdown-item d-flex flex-column py-2" href="{{ route('advisor.orders.index') }}">
                                                <strong class="text-warning"><i class="fa-solid fa-wrench me-1"></i> Nueva orden / Sin mecánico</strong>
                                                <span class="text-muted small">Tienes {{ $unassignedOrders }} orden(es) pendientes por asignar o revisar.</span>
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
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
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

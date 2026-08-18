@php
    $theme = trim($__env->yieldContent('theme')) ?: 'admin';
    $nav = trim($__env->yieldContent('nav-partial'));
    $subtitle = trim($__env->yieldContent('brand-subtitle')) ?: 'Panel';
    $sidebarId = trim($__env->yieldContent('sidebar-id')) ?: 'panelSidebar';
    $offcanvasTitle = trim($__env->yieldContent('offcanvas-title')) ?: 'AutoGest';
    $footer = trim($__env->yieldContent('sidebar-footer-mode')) ?: 'logout';
    $roleLabel = trim($__env->yieldContent('role-label'));
    $sidebar = [
        'subtitle' => $subtitle,
        'nav' => $nav,
        'footer' => $footer,
        'roleLabel' => $roleLabel,
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AutoGest • @yield('title', 'Panel')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @include('layouts.partials.bootstrap-head')
    @include('layouts.partials.pwa-firebase')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body class="panel-page" data-theme="{{ $theme }}">
    <div class="mobile-topbar">
        <button class="btn btn-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#{{ $sidebarId }}" aria-controls="{{ $sidebarId }}">
            ☰ Menú
        </button>
        <span class="mobile-topbar-brand">AutoGest</span>
        <div class="mobile-topbar-extra">
            @hasSection('mobile-extra')
                @yield('mobile-extra')
            @else
                <span class="muted">{{ auth()->user()->name }}</span>
            @endif
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="{{ $sidebarId }}" aria-labelledby="{{ $sidebarId }}Label">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="{{ $sidebarId }}Label">{{ $offcanvasTitle }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body sidebar">
            @include('layouts.partials.sidebar-body', $sidebar)
        </div>
    </div>

    <div class="shell">
        <aside class="sidebar desktop-sidebar">
            @include('layouts.partials.sidebar-body', $sidebar)
        </aside>

        <main class="main @yield('main-class')">
            <header class="topbar">
                <div class="top-copy">
                    <h2>@yield('heading')</h2>
                    @hasSection('subheading')<p>@yield('subheading')</p>@endif
                </div>
                <div class="topbar-tools">
                    <div class="top-actions">
                        @yield('top-actions')
                        @yield('top-actions-extra')
                    </div>
                    @yield('notifications')
                </div>
            </header>

            <section class="content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
                @yield('content')
            </section>
            @yield('after-content')
        </main>
    </div>

    @yield('page-scripts')
    @include('layouts.partials.bootstrap-scripts')
    @include('layouts.partials.photo-lightbox')
    @include('layouts.partials.confirm-modal')
    @stack('scripts')
</body>
</html>

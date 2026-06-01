<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoGest • @yield('title', 'Panel')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @include('layouts.partials.bootstrap-head')
    @stack('styles')
</head>
<body data-theme="admin">
    <div class="container-fluid g-0 px-0">
        <div class="row g-0 min-vh-100">
            <div class="col-12 d-lg-none mobile-topbar border-bottom bg-white px-3 py-2 d-flex align-items-center justify-content-between sticky-top">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                    ☰ Menú
                </button>
                <span class="fw-bold">AutoGest</span>
                <a href="{{ route('profile.edit') }}" class="btn btn-link btn-sm text-decoration-none">Perfil</a>
            </div>

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

            <aside class="col-lg-auto d-none d-lg-flex flex-column sidebar border-end">
                @include('layouts.partials.panel-brand', ['subtitle' => 'Centro de operaciones'])
                @include('layouts.partials.nav-admin')
                <div class="sidebar-footer mt-auto">
                    <strong>Sesión activa</strong><br>
                    {{ auth()->user()->name }} · {{ auth()->user()->role->label() }}
                </div>
            </aside>

            <main class="col min-vh-100 d-flex flex-column main">
                <header class="topbar">
                    <div class="top-copy">
                        <h2>@yield('heading')</h2>
                        <p>@yield('subheading')</p>
                    </div>
                    <div class="top-actions">
                        @yield('top-actions')
                        <a href="{{ route('profile.edit') }}" class="btn btn-secondary btn-sm d-none d-lg-inline-flex">Mi perfil</a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm logout">Cerrar sesión</button>
                        </form>
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

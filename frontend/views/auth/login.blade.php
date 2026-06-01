<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoGest • Acceso</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @include('layouts.partials.bootstrap-head')
</head>
<body class="auth-page" data-theme="admin">
    <div class="auth-shell">
        <section class="brand-panel">
            <div>
                <img src="{{ asset('images/logo-istae.png') }}" alt="Instituto Superior Tecnológico Alberto Enríquez" class="auth-istae-logo">
                <div class="brand-badge">AutoGest</div>
                <h1>Seguimiento inteligente del mantenimiento vehicular.</h1>
                <p>Plataforma web para administrar vehículos, órdenes de servicio, alertas técnicas y reportes en tiempo real.</p>
                <p class="auth-institute">Instituto Superior Tecnológico Alberto Enríquez · San Lorenzo</p>
            </div>
        </section>

        <section class="login-panel">
            <div class="login-box">
                <div>
                    <div class="eyebrow">Acceso seguro</div>
                    <h2>Inicia sesión</h2>
                    <p>Bienvenido de nuevo. Ingresa tus credenciales para continuar.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="auth-back-home"><a href="{{ route('home') }}">← Volver al inicio</a></p>

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required autocomplete="email">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                        <label class="form-check-label" for="remember">Recordarme</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-login">Entrar al sistema</button>
                </form>
            </div>
        </section>
    </div>
    @include('layouts.partials.bootstrap-scripts')
</body>
</html>

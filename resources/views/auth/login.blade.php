<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoGest • Acceso</title>
    <style>
        :root {
            --bg:#030712;
            --bg-soft:#071126;
            --panel:#071326;
            --panel-border:rgba(59,130,246,0.25);
            --text:#f8fafc;
            --muted:#94a3b8;
            --accent:#22c55e;
            --accent-strong:#0f9d58;
            --danger:#fb7185;
            --shadow:0 24px 80px rgba(3,7,18,0.55);
        }
        * { box-sizing: border-box; }
        body {
            margin:0;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            font-family: Inter, system-ui, sans-serif;
            background:
                radial-gradient(circle at top, rgba(14,116,144,0.28), transparent 30%),
                linear-gradient(160deg, #020617, #01040d 40%, #020617);
            color:var(--text);
        }
        .auth-shell {
            width:min(100%, 980px);
            display:grid;
            grid-template-columns:1.15fr 0.85fr;
            gap:0;
            border:1px solid rgba(148,163,184,0.14);
            border-radius:28px;
            overflow:hidden;
            box-shadow:var(--shadow);
            backdrop-filter: blur(2px);
            background:rgba(7,19,38,0.72);
        }
        .brand-panel {
            padding:32px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            background:
                linear-gradient(180deg, rgba(7,18,34,0.95), rgba(3,9,19,0.94)),
                radial-gradient(circle at top left, rgba(14,116,144,0.55), transparent 25%);
            border-right:1px solid rgba(148,163,184,0.1);
        }
        .brand-badge {
            display:inline-flex;
            align-items:center;
            gap:10px;
            width:fit-content;
            padding:8px 12px;
            border-radius:999px;
            background:rgba(34,197,94,0.14);
            border:1px solid rgba(34,197,94,0.28);
            color:#86efac;
            font-size:11px;
            font-weight:700;
            letter-spacing:0.16em;
            text-transform:uppercase;
        }
        .brand-panel h1 { margin:18px 0 12px; font-size:2.6rem; line-height:1; }
        .brand-panel p { margin:0; color:var(--muted); line-height:1.55; max-width:36rem; }
        .feature-list { display:flex; flex-direction:column; gap:10px; margin-top:24px; }
        .feature-item {
            display:flex;
            align-items:center;
            gap:10px;
            padding:12px 14px;
            border-radius:14px;
            background:rgba(8,16,29,0.72);
            border:1px solid rgba(148,163,184,0.08);
            color:var(--muted);
        }
        .feature-item span.dot {
            width:10px;
            height:10px;
            border-radius:999px;
            background:linear-gradient(180deg, #22c55e, #0ea5e9);
            box-shadow:0 0 0 4px rgba(34,197,94,0.14);
        }
        .login-panel {
            padding:32px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:rgba(7,19,38,0.88);
        }
        .login-box {
            width:min(100%, 420px);
            display:flex;
            flex-direction:column;
            gap:18px;
        }
        .eyebrow {
            color:#67e8f9;
            font-size:11px;
            font-weight:800;
            letter-spacing:0.2em;
            text-transform:uppercase;
        }
        .login-box h2 { margin:0; font-size:1.9rem; }
        .login-box p { margin:0; color:var(--muted); }
        form { display:flex; flex-direction:column; gap:12px; }
        .field-group { display:flex; flex-direction:column; gap:8px; }
        .field-group label { font-size:0.78rem; font-weight:700; color:#d0e1ff; text-transform:uppercase; letter-spacing:0.12em; }
        .field-group input {
            border-radius:12px;
            border:1px solid rgba(96,165,250,0.25);
            background:rgba(2,6,23,0.92);
            color:var(--text);
            padding:13px 14px;
            font-size:0.96rem;
        }
        .field-group input::placeholder { color:#64748b; }
        .remember-row {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            color:var(--muted);
            font-size:0.88rem;
        }
        .remember-row label { display:flex; align-items:center; gap:8px; }
        .btn-primary {
            border:0;
            border-radius:12px;
            background:linear-gradient(180deg, #22c55e, #16a34a);
            color:#021b0d;
            font-weight:800;
            padding:13px 14px;
            cursor:pointer;
            box-shadow:0 10px 28px rgba(34,197,94,0.22);
        }
        .error-list {
            margin:0;
            padding:0;
            list-style:none;
            color:#fecdd3;
            background:rgba(127,29,29,0.6);
            border:1px solid rgba(252,165,165,0.22);
            border-radius:12px;
            padding:10px 12px;
            font-size:0.86rem;
        }
        .meta {
            border-top:1px solid rgba(148,163,184,0.1);
            padding-top:14px;
            color:var(--muted);
            font-size:0.82rem;
            line-height:1.4;
        }
        @media (max-width: 800px) {
            .auth-shell { grid-template-columns:1fr; }
            .brand-panel { border-right:0; border-bottom:1px solid rgba(148,163,184,0.1); }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <section class="brand-panel">
            <div>
                <div class="brand-badge">AutoGest Enterprise</div>
                <h1>Seguimiento inteligente del mantenimiento vehicular.</h1>
                <p>Plataforma SaaS moderna para administrar vehículos, órdenes de servicio, alertas técnicas y reportes tácticos en tiempo real.</p>

                <div class="feature-list">
                    <div class="feature-item"><span class="dot"></span> Panel ejecutivo con métricas en vivo</div>
                    <div class="feature-item"><span class="dot"></span> Control de mantenimientos y alertas críticas</div>
                    <div class="feature-item"><span class="dot"></span> Gestión de usuarios, roles y bitácoras</div>
                </div>
            </div>

            <div class="meta">
                <strong>Modo operativo:</strong> administración centralizada, seguridad de sesiones, visualización empresarial.
            </div>
        </section>

        <section class="login-panel">
            <div class="login-box">
                <div>
                    <div class="eyebrow">Acceso seguro</div>
                    <h2>Inicia sesión</h2>
                    <p>Bienvenido de nuevo. Accede al dashboard administrativo con tu cuenta.</p>
                </div>

                @if ($errors->any())
                    <ul class="error-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

                <form method="POST" action="{{ route('login.submit') }}">
                    @csrf
                    <div class="field-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="admin@autogest.test" required autocomplete="email">
                    </div>

                    <div class="field-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    </div>

                    <div class="remember-row">
                        <label><input type="checkbox" name="remember" value="1"> Recordarme</label>
                        <span>Soporte técnico 24/7</span>
                    </div>

                    <button type="submit" class="btn-primary">Entrar al dashboard</button>
                </form>

                <div class="meta">
                    <strong>Credenciales de demo:</strong><br>
                    Admin: admin@autogest.test · Mecánico: mecanico1@autogest.test · Cliente: cliente1@autogest.test<br>
                    Contraseña: <code>password</code>
                </div>
            </div>
        </section>
    </div>
</body>
</html>

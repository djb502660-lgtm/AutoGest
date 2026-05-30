<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AutoGest — Plataforma de gestión de mantenimiento vehicular. Proyecto académico ISTAE San Lorenzo.">
    <title>AutoGest — Mantenimiento vehicular inteligente</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/autogest-ui.css') }}">
</head>
<body class="home-page" data-theme="admin">
    <header class="home-header">
        <a href="{{ route('home') }}" class="home-logo home-logo-istae">
            <img src="{{ asset('images/logo-istae.png') }}" alt="Instituto Superior Tecnológico Alberto Enríquez" class="home-istae-logo">
            <span class="home-logo-text">
                <span class="home-logo-app">AutoGest</span>
                <span class="home-logo-inst">ISTAE · San Lorenzo</span>
            </span>
        </a>
        <nav class="home-nav" aria-label="Principal">
            <a href="#funciones">Funciones</a>
            <a href="#beneficios">Beneficios</a>
            <a href="#institucion">Institución</a>
        </nav>
        <a href="{{ route('login') }}" class="btn btn-primary">Iniciar sesión</a>
    </header>

    <main>
        <section class="home-hero">
            <div class="home-hero-content">
                <div class="home-hero-brand">
                    <img src="{{ asset('images/logo-istae.png') }}" alt="Logo ISTAE" class="home-hero-istae">
                    <p class="home-eyebrow">Instituto Superior Tecnológico Alberto Enríquez · San Lorenzo</p>
                </div>
                <h1>AutoGest — Mantenimiento vehicular <span>claro, rápido y bajo control</span></h1>
                <p class="home-lead">
                    Plataforma web para centralizar vehículos, órdenes de servicio, mantenimientos programados y alertas en un solo sistema moderno y fácil de usar.
                </p>
                <div class="home-hero-actions">
                    <a href="#funciones" class="btn btn-secondary btn-lg">Ver funciones</a>
                </div>
                <ul class="home-hero-stats">
                    <li><strong>Web</strong><span>acceso desde cualquier dispositivo</span></li>
                    <li><strong>MySQL</strong><span>datos centralizados</span></li>
                    <li><strong>UI/UX</strong><span>interfaz clara y profesional</span></li>
                </ul>
            </div>
            <div class="home-hero-visual" aria-hidden="true">
                <div class="home-mockup">
                    <div class="home-mockup-bar">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="home-mockup-body">
                        <div class="home-mockup-sidebar"></div>
                        <div class="home-mockup-main">
                            <div class="home-mockup-cards">
                                <div class="home-mockup-card"></div>
                                <div class="home-mockup-card"></div>
                                <div class="home-mockup-card"></div>
                                <div class="home-mockup-card"></div>
                            </div>
                            <div class="home-mockup-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="funciones" class="home-section">
            <div class="home-section-head">
                <p class="home-eyebrow">Funciones principales</p>
                <h2>Todo lo que tu taller necesita en un solo lugar</h2>
                <p>Desde la recepción del vehículo hasta el historial del cliente, con reportes y calendario integrados.</p>
            </div>
            <div class="home-features-grid">
                <article class="home-feature-card">
                    <span class="home-feature-icon">🚗</span>
                    <h3>Gestión de flota</h3>
                    <p>Registro de vehículos, placas, kilometraje y estado operativo con historial completo.</p>
                </article>
                <article class="home-feature-card">
                    <span class="home-feature-icon">🛠️</span>
                    <h3>Órdenes y mantenimientos</h3>
                    <p>Órdenes de servicio, trabajos preventivos y correctivos con costos y seguimiento.</p>
                </article>
                <article class="home-feature-card">
                    <span class="home-feature-icon">📅</span>
                    <h3>Calendario inteligente</h3>
                    <p>Programación visual de citas y servicios con alertas de vencimiento.</p>
                </article>
                <article class="home-feature-card">
                    <span class="home-feature-icon">📈</span>
                    <h3>Reportes y métricas</h3>
                    <p>Dashboard ejecutivo, gastos, pendientes y salud de la flota en tiempo real.</p>
                </article>
                <article class="home-feature-card">
                    <span class="home-feature-icon">🔔</span>
                    <h3>Alertas y notificaciones</h3>
                    <p>Avisos sobre mantenimientos críticos o próximos programados.</p>
                </article>
                <article class="home-feature-card">
                    <span class="home-feature-icon">🤖</span>
                    <h3>Portal y asistente</h3>
                    <p>Consulta de estado, gastos y servicios desde el portal del cliente.</p>
                </article>
            </div>
        </section>

        <section id="beneficios" class="home-section home-section-alt">
            <div class="home-benefits">
                <div class="home-benefits-copy">
                    <p class="home-eyebrow">Por qué AutoGest</p>
                    <h2>Diseño profesional pensado en personas</h2>
                    <p>Interfaz clara, tonos suaves y flujos intuitivos para reducir errores y acelerar el trabajo del equipo.</p>
                    <ul class="home-checklist">
                        <li>Acceso seguro al sistema</li>
                        <li>Interfaz responsive y moderna</li>
                        <li>Trazabilidad de cada servicio</li>
                        <li>Base de datos centralizada (MySQL)</li>
                    </ul>
                </div>
                <div class="home-benefits-panel">
                    <div class="home-benefit-item">
                        <strong>−40%</strong>
                        <span>menos tiempo buscando información</span>
                    </div>
                    <div class="home-benefit-item">
                        <strong>+1</strong>
                        <span>fuente de verdad para todo el taller</span>
                    </div>
                    <div class="home-benefit-item">
                        <strong>100%</strong>
                        <span>gestión digital del mantenimiento</span>
                    </div>
                </div>
            </div>
        </section>

        <section id="institucion" class="home-section home-istae-section">
            <div class="home-istae-banner">
                <img src="{{ asset('images/logo-istae.png') }}" alt="Instituto Superior Tecnológico Alberto Enríquez" class="home-istae-banner-logo">
                <div>
                    <p class="home-eyebrow">Proyecto académico</p>
                    <h2>Instituto Superior Tecnológico Alberto Enríquez</h2>
                    <p class="home-istae-location">San Lorenzo · 2022</p>
                    <p class="home-istae-desc">
                        AutoGest es una solución de software desarrollada en el marco de la formación tecnológica del ISTAE,
                        orientada a la gestión eficiente del mantenimiento vehicular en talleres y flotas.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <footer class="home-footer">
        <div class="home-footer-inner">
            <div class="home-footer-brand">
                <img src="{{ asset('images/logo-istae.png') }}" alt="ISTAE" class="home-footer-istae">
                <div>
                    <p class="home-footer-app"><strong>AutoGest</strong></p>
                    <p>Instituto Superior Tecnológico Alberto Enríquez · San Lorenzo</p>
                </div>
            </div>
            <p class="home-footer-copy">&copy; {{ date('Y') }} AutoGest — Proyecto académico ISTAE.</p>
        </div>
    </footer>
</body>
</html>

# Frontend — AutoGest

Interfaz de usuario basada en **Blade** + **Bootstrap 5.3** (CDN) y estilos propios en `public/css/autogest-ui.css`.

## Carpetas

- `views/admin/` — Dashboard, usuarios, vehículos, mantenimientos, reportes, calendario
- `views/mechanic/` — Órdenes, mantenimientos, vehículos, historial
- `views/client/` — Dashboard, gastos, chatbot, perfil, notificaciones
- `views/auth/` — Pantalla de login
- `views/layouts/` — `admin`, `mechanic`, `client` y partials Bootstrap (`bootstrap-head`, `bootstrap-scripts`, navegación)
- `css/`, `js/` — Assets compilados con Vite (`npm run dev`)

## Configuración

Las vistas se cargan desde `config/view.php` → `frontend/views`.

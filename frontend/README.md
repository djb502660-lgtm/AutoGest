# Frontend — AutoGest

Interfaz de usuario basada en **Blade** (sin SPA separada).

## Carpetas

- `views/admin/` — Dashboard, usuarios, vehículos, mantenimientos, reportes, calendario
- `views/mechanic/` — Órdenes, mantenimientos, vehículos, historial
- `views/client/` — Dashboard, gastos, chatbot, perfil, notificaciones
- `views/auth/` — Pantalla de login
- `views/layouts/` — `admin`, `mechanic`, `client`
- `css/`, `js/` — Assets compilados con Vite (`npm run dev`)

## Configuración

Las vistas se cargan desde `config/view.php` → `frontend/views`.

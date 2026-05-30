# Estructura del proyecto AutoGest

## Backend (`app/`, `backend/`, `config/`, `database/`)

| Ruta | Descripción |
|------|-------------|
| `app/Http/Controllers/Admin/` | Usuarios, vehículos, mantenimientos, reportes, calendario |
| `app/Http/Controllers/Mechanic/` | Órdenes, mantenimientos, vehículos asignados |
| `app/Http/Controllers/Client/` | Portal cliente, gastos, chatbot, notificaciones |
| `app/Http/Controllers/Auth/` | Login y logout |
| `app/Models/` | Modelos Eloquent |
| `app/Policies/` | Autorización por recurso |
| `app/Enums/` | Roles de usuario |
| `backend/routes/` | Definición de rutas por módulo |
| `database/migrations/` | Esquema MySQL |
| `database/seeders/` | Datos de demostración |

## Frontend (`frontend/`, `public/`)

| Ruta | Descripción |
|------|-------------|
| `frontend/views/admin/` | Vistas del administrador |
| `frontend/views/mechanic/` | Vistas del mecánico |
| `frontend/views/client/` | Vistas del cliente |
| `frontend/views/auth/` | Login |
| `frontend/views/layouts/` | Layouts compartidos |
| `frontend/css/`, `frontend/js/` | Assets (Vite) |
| `public/` | `index.php`, favicon, `.htaccess` |

## Rutas principales

- Admin: `/dashboard`, `/users`, `/vehicles`, …
- Mecánico: `/mecanico`, `/mecanico/ordenes`, …
- Cliente: `/cliente`, `/cliente/vehiculos`, …

# Estructura del proyecto AutoGest

## Backend (`app/`, `routes/`, `config/`, `database/`)

| Ruta | Descripción |
|------|-------------|
| `app/Http/Controllers/Admin/` | Usuarios, vehículos, mantenimientos, reportes, calendario |
| `app/Http/Controllers/Advisor/` | Órdenes, citas, clientes, preórdenes |
| `app/Http/Controllers/Mechanic/` | Órdenes, mantenimientos, vehículos asignados |
| `app/Http/Controllers/Client/` | Portal cliente, gastos, chatbot, notificaciones |
| `app/Http/Controllers/Auth/` | Login y logout |
| `app/Models/` | Modelos Eloquent |
| `app/Policies/` | Autorización por recurso |
| `app/Enums/` | Roles de usuario |
| `routes/` | Definición de rutas por módulo (`web`, `auth`, `admin`, `advisor`, `mechanic`, `client`) |
| `database/migrations/` | Esquema MySQL |
| `database/seeders/` | Datos de demostración |

## Frontend (`resources/`, `public/`)

| Ruta | Descripción |
|------|-------------|
| `resources/views/admin/` | Vistas del administrador |
| `resources/views/advisor/` | Vistas del asesor |
| `resources/views/mechanic/` | Vistas del mecánico |
| `resources/views/client/` | Vistas del cliente |
| `resources/views/auth/` | Login |
| `resources/views/layouts/` | Layouts compartidos |
| `resources/css/`, `resources/js/` | Assets (Vite) |
| `public/` | `index.php`, favicon, `.htaccess` |

## Rutas principales (localhost)

- App: `http://localhost:8000`
- Admin: `/dashboard`, `/users`, `/vehicles`, …
- Asesor: `/asesor`, `/asesor/ordenes`, …
- Mecánico: `/mecanico`, `/mecanico/ordenes`, …
- Cliente: `/cliente`, `/cliente/vehiculos`, …

# Backend — AutoGest

Contiene las **rutas HTTP** del sistema. La lógica de negocio está en `app/` (raíz del proyecto).

## Archivos de rutas

- `web.php` — Redirección inicial y carga de módulos
- `auth.php` — Login / logout
- `admin.php` — Panel administrador
- `mechanic.php` — Módulo mecánico
- `client.php` — Portal cliente
- `console.php` — Comandos Artisan

## Relación con `app/`

Los controladores referenciados viven en:

- `App\Http\Controllers\Admin\*`
- `App\Http\Controllers\Mechanic\*`
- `App\Http\Controllers\Client\*`
- `App\Http\Controllers\Auth\*`

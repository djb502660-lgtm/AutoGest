# AutoGest

Plataforma web de gestión de mantenimiento vehicular (Laravel 12, PHP 8.2+, MySQL).

## Estructura del proyecto

```
AutoGest/
├── app/                     # Modelos, controladores, políticas, middleware
│   └── Http/Controllers/
│       ├── Admin/
│       ├── Advisor/
│       ├── Mechanic/
│       ├── Client/
│       └── Auth/
├── routes/                  # Rutas HTTP por módulo
├── resources/
│   ├── views/               # Plantillas Blade
│   ├── css/
│   └── js/
├── config/
├── database/                # Migraciones y seeders
├── docs/
└── public/                  # Punto de entrada web
```

## Requisitos

- [Laragon](https://laragon.org/) (MySQL; Apache opcional)
- PHP **8.2+** (en Laragon: *Menu → PHP → Version*)
- Composer
- Node.js (Vite)

Ruta del proyecto en disco: `C:\laragon\www\AutoGest`

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan autogest:reset-demo-passwords
npm install
```

Si el login falla tras migrar, ejecuta `php artisan autogest:reset-demo-passwords` para restablecer las contraseñas demo.

## Arranque (localhost)

Con MySQL en marcha:

```bash
php artisan serve --host=localhost --port=8000
npm run dev
```

| Uso | URL |
|-----|-----|
| Inicio | `http://localhost:8000` |
| Login | `http://localhost:8000/login` |
| Admin | `http://localhost:8000/dashboard` |
| Asesor | `http://localhost:8000/asesor` |
| Mecánico | `http://localhost:8000/mecanico` |
| Cliente | `http://localhost:8000/cliente` |

> **Error 500 / MySQL 1130:** En `.env` usa `DB_HOST=localhost` (no `127.0.0.1`). Reinicia Laragon si MySQL no está en marcha.

| Rol      | Correo                   | Contraseña |
|----------|--------------------------|------------|
| Admin    | admin@autogest.test      | password   |
| Asesor   | asesor1@autogest.test    | password   |
| Mecánico | mecanico1@autogest.test  | password   |
| Cliente  | cliente1@autogest.test   | password   |

## Redis (opcional)

El proyecto soporta Redis para cache y cola. Por defecto el driver de cola es `database`, pero si deseas usar Redis cambia en `.env`:

```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis
```

Asegúrate de tener instalada la extensión PHP Redis (`phpredis`) y un servidor Redis en `REDIS_HOST`.

## Base de datos

MySQL, base `autogest` (ver `.env`).

## Documentación

- **[Manual operativo](docs/MANUAL_GUIA.md)** — Laragon, Expo Go, APK, Render, Aiven y troubleshooting
- [Casos de uso](docs/CASOS_DE_USO.md)
- [Esquema de base de datos](docs/ESQUEMA_BASE_DATOS.md)
- [Estructura detallada](docs/ESTRUCTURA_PROYECTO.md)

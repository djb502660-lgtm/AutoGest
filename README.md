# AutoGest

Plataforma web de gestión de mantenimiento vehicular (Laravel 12, PHP 8.2+, MySQL).

## Estructura del proyecto

```
AutoGest/
├── backend/                 # Lógica del servidor
│   └── routes/              # Rutas web (admin, mecánico, cliente, auth)
├── frontend/                # Interfaz de usuario
│   ├── views/               # Plantillas Blade por módulo
│   ├── css/
│   └── js/
├── app/                     # Modelos, controladores, políticas, middleware
│   └── Http/Controllers/
│       ├── Admin/           # Panel administrador
│       ├── Mechanic/        # Módulo mecánico
│       ├── Client/          # Portal cliente
│       └── Auth/
├── config/                  # Configuración Laravel
├── database/                # Migraciones y seeders
├── docs/                    # Documentación (casos de uso, ER)
└── public/                  # Punto de entrada web
```

## Requisitos

- [Laragon](https://laragon.org/) (Apache + MySQL)
- PHP **8.2+** (en Laragon: *Menu → PHP → Version*)
- Composer

Ruta del proyecto en disco: `C:\laragon\www\AutoGest`

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan autogest:reset-demo-passwords
```

Si el login falla tras migrar, ejecuta `php artisan autogest:reset-demo-passwords` para restablecer las contraseñas demo.

## Redis (opcional)

El proyecto soporta Redis para cache y cola. Por defecto el driver de cola es `database`, pero si deseas usar Redis cambia en `.env`:

```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis
```

Asegúrate de tener instalada la extensión PHP Redis (`phpredis`) y un servidor Redis en `REDIS_HOST`.

## Acceso (Laragon)

1. En Laragon: **Start All** (Apache y MySQL).
2. Asegúrate de que ningún otro Apache (p. ej. XAMPP) use el puerto 80.
3. Abre la app:

| Uso | URL |
|-----|-----|
| Inicio | `http://AutoGest.test` |
| Login | `http://AutoGest.test/login` |
| Alternativa | `http://localhost/AutoGest/public` |

El virtual host de Laragon apunta a `C:\laragon\www\AutoGest\public` (`AutoGest.test` en el archivo `hosts`).

> **Error 500 / MySQL 1130:** En `.env` usa `DB_HOST=localhost` (no `127.0.0.1`). Reinicia Laragon si MySQL no está en marcha.

| Rol      | Correo                   | Contraseña |
|----------|--------------------------|------------|
| Admin    | admin@autogest.test      | password   |
| Asesor   | asesor1@autogest.test    | password   |
| Mecánico | mecanico1@autogest.test  | password   |
| Cliente  | cliente1@autogest.test   | password   |

## Base de datos

MySQL, base `autogest` (ver `.env`).

## Documentación

- [Casos de uso](docs/CASOS_DE_USO.md)
- [Esquema de base de datos](docs/ESQUEMA_BASE_DATOS.md)
- [Estructura detallada](docs/ESTRUCTURA_PROYECTO.md)

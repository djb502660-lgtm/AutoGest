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

- PHP 8.2+
- MySQL 8.x (Laragon: iniciar MySQL con *Start All*)
- Composer

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

## Acceso

**Inicio:** `http://localhost/AutoGest/public`  
**Login:** `http://localhost/AutoGest/public/login`

| Rol      | Correo                   | Contraseña |
|----------|--------------------------|------------|
| Admin    | admin@autogest.test      | password   |
| Mecánico | mecanico1@autogest.test  | password   |
| Cliente  | cliente1@autogest.test   | password   |

## Base de datos

MySQL, base `autogest` (ver `.env`).

## Documentación

- [Casos de uso](docs/CASOS_DE_USO.md)
- [Esquema de base de datos](docs/ESQUEMA_BASE_DATOS.md)
- [Estructura detallada](docs/ESTRUCTURA_PROYECTO.md)

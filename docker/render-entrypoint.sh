#!/bin/sh
set -e

echo "[render-entrypoint] Aplicando migraciones..."
php artisan migrate --force --no-interaction

if [ "${AUTOGEST_RESET_DEMO_PASSWORDS:-false}" = "true" ]; then
    echo "[render-entrypoint] Restableciendo contraseñas demo..."
    php artisan autogest:reset-demo-passwords --no-interaction
fi

echo "[render-entrypoint] Iniciando servidor en puerto ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"

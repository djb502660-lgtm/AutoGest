# Plan exacto de despliegue en Render para AutoGest

## 1. Objetivo

Desplegar `AutoGest` en `Render` como aplicacion Laravel estable, evitando los problemas de runtime serverless encontrados en Vercel.

## 2. Arquitectura recomendada en Render

### Minimo viable

- 1 `Web Service` para Laravel
- 1 base de datos `MySQL` externa o administrada fuera de Render

### Recomendado para produccion

- 1 `Web Service` para Laravel
- 1 `Cron Job` para `php artisan schedule:run`
- 1 `Worker` si luego activas colas reales
- almacenamiento externo para fotos:
  - Amazon S3
  - Cloudinary
  - Backblaze B2

## 3. Prerrequisitos antes del deploy

- repo conectado a GitHub/GitLab
- `APP_KEY` generada
- base de datos remota lista
- credenciales `DB_*`
- dominio temporal de Render aceptado inicialmente

## 4. Tipo de servicio en Render

Crear un `Web Service` con estas opciones:

- `Environment`: `PHP`
- `Branch`: la rama que quieres publicar
- `Region`: la mas cercana a tus usuarios

## 5. Build command recomendado

Usa este comando:

```bash
composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan config:clear && php artisan route:clear && php artisan view:clear
```

### Opcional despues de estabilizar

Cuando todo funcione bien, puedes endurecer el build con:

```bash
composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan config:cache && php artisan route:cache
```

No recomiendo activar caches al primer deploy.

## 6. Start command recomendado

Usa este comando:

```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

Es la forma mas simple para salir rapido.

### Alternativa mas profesional

Si luego quieres mas control, se puede migrar a Docker + Nginx + PHP-FPM, pero no es necesario para el primer release.

## 7. Variables de entorno obligatorias

Configura en Render:

```env
APP_NAME=AutoGest
APP_ENV=production
APP_DEBUG=false
APP_URL=https://TU-SERVICIO.onrender.com
APP_KEY=base64:TU_CLAVE

LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=TU_HOST
DB_PORT=3306
DB_DATABASE=TU_DATABASE
DB_USERNAME=TU_USER
DB_PASSWORD=TU_PASSWORD

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log

FILESYSTEM_DISK=public
```

## 8. Variables recomendadas segun crecimiento

Si mas adelante activas servicios reales:

```env
MAIL_MAILER=smtp
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
```

Eso solo despues de validar migraciones y tablas necesarias.

## 9. Base de datos

Render no es ideal si vas a depender de MySQL administrado fuera de su stack principal, asi que tienes dos rutas:

### Ruta simple

Usar MySQL externo:

- PlanetScale
- Aiven
- Railway
- Hostinger/VPS/MySQL externo

### Ruta compatible inmediata

Mantener la base actual si ya tienes un host remoto accesible desde internet.

## 10. Migraciones

No metas migraciones destructivas en el `build command` al primer intento.

Hazlas manualmente primero:

```bash
php artisan migrate --force
```

En Render puedes correr esto desde:

- `Shell` del servicio, o
- un job puntual

Cuando confirmes estabilidad, se puede automatizar.

## 11. Storage y fotos

Este sistema usa fotos/evidencias, asi que debes tomar una decision antes de considerarlo productivo.

### Opcion temporal

Usar `public`/filesystem local del contenedor.

Problema:
- no es persistente entre deploys o reinicios en muchos escenarios

### Opcion correcta

Mover evidencias a storage externo:

- S3
- Cloudinary
- B2

### Recomendacion

No cierres produccion definitiva hasta resolver esto.

## 12. Health check

Usa como `Health Check Path`:

```text
/up
```

Si `/up` falla, Render marcará el deploy como enfermo, lo cual es correcto.

## 13. Checklist de primer deploy

1. Subir cambios al repo.
2. Crear `Web Service` en Render.
3. Configurar variables de entorno.
4. Ejecutar build.
5. Levantar con `php artisan serve --host=0.0.0.0 --port=$PORT`.
6. Verificar `/up`.
7. Probar `/`.
8. Probar login.
9. Probar dashboard admin.
10. Probar lectura/escritura en base de datos.
11. Probar subida de fotos.

## 14. Smoke test post deploy

Debes probar al menos:

- login administrador
- login cliente
- dashboard
- listado de ordenes
- chatbot cliente
- generacion de PDF
- carga de fotos
- consultas a base de datos

## 15. Riesgos conocidos del proyecto en Render

- si `APP_KEY` falta, Laravel no arranca bien
- si la base de datos no es accesible, muchas pantallas fallaran
- si dejas fotos en disco local, puedes perder archivos
- si activas colas sin worker, algunos procesos quedaran pendientes

## 16. Recomendacion de rollout

### Fase 1

Deploy basico:

- web service
- DB remota
- sin colas reales
- sin cron
- logs a stderr

### Fase 2

Estabilizacion:

- migraciones controladas
- smoke tests
- ajuste de mail real

### Fase 3

Produccion seria:

- storage externo
- scheduler
- worker
- dominio propio
- SSL y observabilidad

## 17. Comandos finales recomendados

### Build

```bash
composer install --no-dev --optimize-autoloader && npm ci && npm run build && php artisan config:clear && php artisan route:clear && php artisan view:clear
```

### Start

```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

### Health check

```text
/up
```

## 18. Decision recomendada para AutoGest

Si quieres publicar pronto y bien:

- desplegar en `Render`
- usar base de datos remota
- mantener `QUEUE_CONNECTION=sync`
- mantener `LOG_CHANNEL=stderr`
- dejar fotos en storage externo lo antes posible

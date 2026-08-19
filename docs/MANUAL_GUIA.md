# Manual operativo — AutoGest

Guía única para desarrollo local, app móvil, producción en Render y publicación del APK.

**Versión app móvil actual:** 1.3.0 (`versionCode` 5)

---

## 1. Arquitectura rápida

| Componente | Dónde vive | Para qué |
|------------|------------|----------|
| Backend Laravel | `C:\laragon\www\AutoGest` | Web + API REST |
| App móvil | `AutoGestExpo/` | Expo SDK 52 + Expo Router |
| Producción web/API | [Render](https://autogest-jlm7.onrender.com) | APK y usuarios fuera de casa |
| Base de datos prod | [Aiven MySQL](https://console.aiven.io) | MySQL remoto con SSL |
| Descarga APK | `/downloads/AutoGest-1.3.0.apk` | Botón en la home de Laravel |

### URLs de API según entorno

| Entorno | URL API |
|---------|---------|
| **Expo Go** (PC + celular misma Wi‑Fi) | `http://192.168.1.9/AutoGest/public/api` |
| **APK / producción** | `https://autogest-jlm7.onrender.com/api` |

> En Laragon la ruta correcta incluye `/AutoGest/public/api`. Sin `public` devuelve 404.

---

## 2. Requisitos locales

- [Laragon](https://laragon.org/) con **Apache + MySQL** encendidos
- PHP 8.2+
- Composer, Node.js
- Para APK local: [Android Studio](https://developer.android.com/studio) + SDK

IP de referencia del PC en red local: **192.168.1.9** (ajusta si cambia).

---

## 3. Backend local (Laragon)

### Instalación inicial

```bash
cd C:\laragon\www\AutoGest
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan autogest:reset-demo-passwords
npm install
npm run build
```

### Arranque diario

1. Abrir Laragon → **Start All** (Apache + MySQL).
2. Web: `http://autogest.test` o `http://localhost:8000` con `php artisan serve`.
3. API móvil: `http://192.168.1.9/AutoGest/public/api`.

### Cuentas demo (contraseña `password`)

| Rol | Correo |
|-----|--------|
| Admin | `admin@autogest.test` |
| Asesor | `asesor1@autogest.test` |
| Mecánico | `mecanico1@autogest.test` |
| Cliente | `cliente1@autogest.test` |

Si el login falla tras migrar:

```bash
php artisan autogest:reset-demo-passwords
```

---

## 4. App móvil — Expo Go (desarrollo)

### Configuración

```bash
cd AutoGestExpo
npm install
cp .env.example .env
```

En `.env`:

```env
EXPO_PUBLIC_API_URL=http://192.168.1.9/AutoGest/public/api
```

### Arrancar

```powershell
cd AutoGestExpo
$env:REACT_NATIVE_PACKAGER_HOSTNAME='192.168.1.9'
npx expo start --lan
```

- QR en el navegador: `http://localhost:8081`
- URL directa: `exp://192.168.1.9:8081`
- Celular y PC deben estar en la **misma Wi‑Fi**.

### Si el puerto 8081 está ocupado

Cierra procesos Node/Expo anteriores y reinicia `npx expo start --lan`.

### Roles en la app

| Rol | Pantallas principales |
|-----|------------------------|
| Cliente | Inicio, vehículos, órdenes, chat, gastos |
| Asesor | Inicio, órdenes, solicitudes |
| Mecánico | Inicio, órdenes, evidencias (cámara/galería) |
| Admin | Inicio, flota, órdenes, solicitudes, equipo |

---

## 5. Matriz de regresión (Fase 0)

Comprueba login, dashboard, tab principal y logout de los 4 roles.

```bash
cd AutoGestExpo
npm run fase0
```

Contra Render:

```powershell
.\scripts\fase0-matriz.ps1 -BaseUrl "https://autogest-jlm7.onrender.com/api"
```

Alcance congelado en `AutoGestExpo/fase0-alcance.json`.

---

## 6. Generar APK Android

### Opción A — Build local (recomendada, sin cuota EAS)

Requisitos: Android Studio instalado.

```powershell
cd AutoGestExpo
npm run build:apk:local
```

Salida:

- `AutoGestExpo/AutoGest-release-1.3.0.apk`
- `public/downloads/AutoGest-1.3.0.apk` (para la web)

El script usa `GRADLE_USER_HOME=C:\gradle` para evitar rutas >260 caracteres en Windows.

**Alternativa si Gradle falla:** Android Studio → Open → `AutoGestExpo/android` → **Build → Build APK(s)**.

Variables útiles:

```powershell
$env:JAVA_HOME = "C:\Program Files\Android\Android Studio\jbr"
$env:ANDROID_HOME = "$env:LOCALAPPDATA\Android\Sdk"
```

### Opción B — EAS Build (nube)

```bash
cd AutoGestExpo
npm run build:apk
```

Cuenta Expo: `kagarrutax`. Si la cuota free de Android está agotada, espera al reset mensual o usa build local.

### Tras generar un APK nuevo

1. Copia a `public/downloads/AutoGest-X.Y.Z.apk`.
2. Actualiza `ANDROID_APK_URL` en `.env`, Render y `.env.example`.
3. Sube versión en `AutoGestExpo/app.json` (`version` + `versionCode`).
4. Deploy en Render para publicar el archivo.

---

## 7. Producción — Render + Aiven

### Servicios

- **Web:** [autogest-jlm7.onrender.com](https://autogest-jlm7.onrender.com)
- **Repo:** GitHub conectado a Render (rama `master`)
- **Docker:** `Dockerfile` + `docker/render-entrypoint.sh`

Al arrancar, el contenedor ejecuta automáticamente:

```bash
php artisan migrate --force --no-interaction
```

### Variables en Render (Environment)

Copia la plantilla desde `.env.render.example`:

```env
APP_NAME=AutoGest
APP_ENV=production
APP_DEBUG=false
APP_URL=https://autogest-jlm7.onrender.com
APP_KEY=base64:...

ANDROID_APK_URL=https://autogest-jlm7.onrender.com/downloads/AutoGest-1.3.0.apk

DB_CONNECTION=mysql
DB_HOST=autogest-mysql-djb502660-95f9.a.aivencloud.com
DB_PORT=23663
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=<desde Aiven>
MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=false

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
VIEW_COMPILED_PATH=/tmp/framework/views

AUTOGEST_RESET_DEMO_PASSWORDS=false
```

### Base de datos Aiven

1. Entra a [console.aiven.io](https://console.aiven.io).
2. Servicio MySQL → **Información de conexión**.
3. Copia la URI `mysql://...` y extrae host, puerto, usuario, contraseña y base.

**Importante:** el servicio MySQL debe estar **Running** (encendido). Si está apagado:

- Render no conecta → deploy falla o login devuelve 500.
- El host no resuelve en DNS.

Comprobar desde Windows:

```powershell
nslookup autogest-mysql-djb502660-95f9.a.aivencloud.com
```

Debe devolver una IP (ej. `134.209.54.23`).

### Deploy en Render

1. Push al repo conectado.
2. Render → servicio **AutoGest** → **Manual Deploy**.
3. Revisa logs: debe aparecer `[render-entrypoint] Aplicando migraciones...`.

### Primera vez o base vacía

En **Shell** de Render:

```bash
php artisan db:seed --force
php artisan autogest:reset-demo-passwords
```

O activa temporalmente `AUTOGEST_RESET_DEMO_PASSWORDS=true`, redeploya y vuelve a `false`.

### Probar API en producción

```powershell
Invoke-WebRequest `
  -Uri "https://autogest-jlm7.onrender.com/api/login" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"email":"cliente1@autogest.test","password":"password"}' `
  -UseBasicParsing
```

Respuesta esperada: **200** con `token` y `user`.

### Descarga del APK en producción

URL directa:

```
https://autogest-jlm7.onrender.com/downloads/AutoGest-1.3.0.apk
```

El botón de la home usa `ANDROID_APK_URL` de Laravel (`config/app.php`).

---

## 8. Checklist — publicar versión móvil

- [ ] Cambiar `version` y `versionCode` en `AutoGestExpo/app.json`.
- [ ] Actualizar `extra.apiUrl` si cambia el backend de producción.
- [ ] `npm run build:apk:local` (o EAS).
- [ ] Copiar APK a `public/downloads/`.
- [ ] Actualizar `ANDROID_APK_URL` en `.env`, `.env.example`, `.env.render.example` y Render.
- [ ] Deploy en Render.
- [ ] Probar login API (200).
- [ ] Instalar APK en celular.
- [ ] Probar 4 roles: cliente, asesor, mecánico, admin.
- [ ] `npm run fase0` contra Render.

---

## 9. Solución de problemas

### Expo Go no conecta / login falla en dev

- Laragon encendido (Apache + MySQL).
- Misma Wi‑Fi en PC y celular.
- URL con `/AutoGest/public/api`, no solo `/api`.
- `REACT_NATIVE_PACKAGER_HOSTNAME` = IP del PC.

### Render deploy falla al arrancar

Log típico:

```
getaddrinfo for autogest-mysql-....a.aivencloud.com failed: Name does not resolve
```

**Causa:** MySQL de Aiven apagado o host incorrecto.  
**Solución:** encender el servicio en Aiven, verificar `DB_*` en Render, redeploy.

### Login API devuelve 500 en Render

1. Comprobar Aiven **Running** y DNS (`nslookup`).
2. Verificar `DB_PASSWORD` en Render.
3. Shell: `php artisan migrate:status` → `migrate --force`.
4. Si no hay usuarios: `db:seed` + `autogest:reset-demo-passwords`.

### `/up` responde 200 pero `/api/login` falla

`/up` no usa base de datos. El login sí. Revisar conexión Aiven y migraciones.

### Gradle — `Filename longer than 260 characters`

Usar `npm run build:apk:local` (configura `GRADLE_USER_HOME=C:\gradle`) o compilar desde Android Studio.

### Gradle — `hermesEnabled` unknown property

El script local restaura propiedades en `android/gradle.properties`. Si falta, añade `hermesEnabled=true`.

### EAS — cuota Android agotada

Usar build local (`npm run build:apk:local`) o esperar reset mensual en Expo billing.

### Contraseñas demo no funcionan

```bash
php artisan autogest:reset-demo-passwords
```

En Render: Shell o `AUTOGEST_RESET_DEMO_PASSWORDS=true` temporal.

---

## 10. Archivos de referencia

| Archivo | Contenido |
|---------|-----------|
| `.env.example` | Laravel local |
| `.env.render.example` | Plantilla Render + Aiven |
| `render.yaml` | Blueprint Render |
| `Dockerfile` | Imagen de producción |
| `docker/render-entrypoint.sh` | Migraciones al arrancar |
| `AutoGestExpo/.env.example` | API para Expo Go |
| `AutoGestExpo/app.json` | Versión APK y `extra.apiUrl` |
| `AutoGestExpo/scripts/build-apk-local.ps1` | Build APK Windows |
| `AutoGestExpo/scripts/fase0-matriz.ps1` | Matriz 4 roles |
| `AutoGestExpo/fase0-alcance.json` | Alcance congelado |

---

## 11. Resumen de comandos frecuentes

```powershell
# Laragon + Expo Go
cd C:\laragon\www\AutoGest\AutoGestExpo
$env:REACT_NATIVE_PACKAGER_HOSTNAME='192.168.1.9'
npx expo start --lan

# Matriz regresión
npm run fase0

# Build APK
npm run build:apk:local

# Probar Render
.\scripts\fase0-matriz.ps1 -BaseUrl "https://autogest-jlm7.onrender.com/api"
```

```bash
# Laravel local
php artisan migrate --force
php artisan autogest:reset-demo-passwords

# Render Shell
php artisan migrate --force
php artisan db:seed --force
php artisan autogest:reset-demo-passwords
```

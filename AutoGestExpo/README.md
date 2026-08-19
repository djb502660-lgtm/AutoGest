# AutoGest Expo

App Android de AutoGest (Expo SDK 52). Desarrollo con **Expo Go**; instalable con **EAS Build** (APK preview).

## API según entorno

| Entorno | URL |
|---------|-----|
| **Expo Go** (Laragon, misma Wi‑Fi) | `http://192.168.1.9/AutoGest/public/api` |
| **APK** (EAS preview / producción) | `https://autogest-jlm7.onrender.com/api` |

Copia `.env.example` → `.env` para Expo Go. El APK usa `extra.apiUrl` en `app.json` y `eas.json` (Render).

## Probar con Expo Go

```bash
cd AutoGestExpo
npm install
# Celular y PC en la misma Wi‑Fi:
$env:REACT_NATIVE_PACKAGER_HOSTNAME='192.168.1.9'
npx expo start --lan
```

Escanea el QR en http://localhost:8081 o abre `exp://192.168.1.9:8081`.

**Cuentas demo** (contraseña `password`):

| Rol | Correo |
|-----|--------|
| Cliente | `cliente1@autogest.test` |
| Asesor | `asesor1@autogest.test` |
| Mecánico | `mecanico1@autogest.test` |
| Admin | `admin@autogest.test` |

## Roles en la app

- **Cliente:** inicio, vehículos, órdenes, chat, gastos
- **Asesor:** inicio, órdenes, solicitudes (chatbot)
- **Mecánico:** inicio, órdenes, evidencias (cámara/galería)
- **Admin:** inicio, flota, órdenes, solicitudes, equipo (lectura)

## Matriz de regresión (Fase 0)

```bash
npm run fase0
```

Comprueba login, dashboard, tab principal y logout de los 4 roles contra Laragon. Para Render:

```powershell
.\scripts\fase0-matriz.ps1 -BaseUrl "https://autogest-jlm7.onrender.com/api"
```

## Generar APK 1.3.0

### Opción A — EAS (nube, requiere cuota)

```bash
npm run build:apk
```

Si la cuenta free agotó builds Android, espera al reset mensual o upgrade en Expo billing.

### Opción B — Build local (sin cuota EAS)

Requisitos: Android Studio instalado.

```powershell
npm run build:apk:local
```

Genera `AutoGest-release-1.3.0.apk` en esta carpeta y copia a `../public/downloads/AutoGest-1.3.0.apk`.

**Windows:** si Gradle falla con `Filename longer than 260 characters`, el script usa `GRADLE_USER_HOME=C:\gradle`. También puedes abrir `android/` en Android Studio → **Build → Build APK(s)**.

Tras el build, actualiza `ANDROID_APK_URL` en Laravel (local: `http://autogest.test/downloads/AutoGest-1.3.0.apk`; Render: `https://autogest-jlm7.onrender.com/downloads/AutoGest-1.3.0.apk`) y haz deploy para que el botón de descarga en la web apunte al APK 1.3.0.

# AutoGest Expo

App nativa Android de AutoGest. Se prueba en **Expo Go** y se publica como APK con **EAS Build**.

Expo Go no genera la APK: es el visor de desarrollo. La APK instalable se crea después con EAS.

## API

```
EXPO_PUBLIC_API_URL=https://autogest-jlm7.onrender.com/api
```

Copia `.env.example` a `.env` si no existe.

**Modo compatible (producción actual):** login, vehículos, órdenes y cambio de estado legacy (`pendiente`, `en_proceso`, `completado`).

**Modo completo (API v1 desplegada):** dashboard, chatbot, citas, gastos, fotos multipart y progreso de orden.

Para activar el modo completo, sube el backend a GitHub y deja que Render redeploye (incluye `routes/api.php` y `app/Http/Controllers/Api/V1/`).

## Probar con Expo Go

1. Instala Expo Go en el Android.
2. En esta carpeta:

```bash
cd AutoGestExpo
npm install
npx expo start
```

3. Escanea el QR (contra Render no hace falta la misma red Wi‑Fi).
4. Login demo:
   - Cliente: `cliente1@autogest.test` / `password`
   - Asesor: `asesor1@autogest.test` / `password`
   - Mecánico: `mecanico1@autogest.test` / `password`

## Roles

- Cliente: inicio, vehículos, órdenes, chatbot y gastos
- Asesor: solicitudes chatbot (confirmar/rechazar) y órdenes
- Mecánico: órdenes, estado, comentario técnico y fotos (cámara o galería)

## Generar APK (EAS preview)

Perfil `preview` en `eas.json` (APK interna, no Play Store):

```bash
npm run build:apk
```

Proyecto EAS: https://expo.dev/accounts/kagarrutax/projects/autogest

APK preview (build 1.1.0):
- Instalación: https://expo.dev/accounts/kagarrutax/projects/autogest/builds/7f1bfa74-be6b-4bf6-9bb1-c07216971e1c
- Descarga directa: https://expo.dev/artifacts/eas/mwYgrC9F_Arauxv7qfLgHoUYKjhb4SWAPa65njJKiyQ.apk

El perfil `production` (AAB / Play Store) queda para más adelante.

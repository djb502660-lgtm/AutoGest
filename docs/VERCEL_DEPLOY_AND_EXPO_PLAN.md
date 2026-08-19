# Vercel deploy + plan Expo

## 1. Configuracion de Vercel aplicada

Se agregaron estos archivos:

- `api/index.php`: reenvia todas las peticiones a `public/index.php`
- `vercel.json`: define runtime PHP, `buildCommand` y `outputDirectory`

### Configuracion clave

- `buildCommand`: `npm run build`
- `outputDirectory`: `public`
- runtime PHP: `vercel-php@0.9.0`
- rutas estaticas:
  - `/build/*` -> `public/build/*`
  - `/storage/*` -> `public/storage/*`
  - `/favicon.svg`
  - `/firebase-messaging-sw.js`

### Variables recomendadas en Vercel

Configurar en el panel de Vercel, no en el repositorio:

- `APP_NAME=AutoGest`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://tu-dominio.vercel.app`
- `APP_KEY=...`
- `LOG_CHANNEL=stderr`
- `CACHE_STORE=array`
- `SESSION_DRIVER=cookie`
- `QUEUE_CONNECTION=sync`
- `VIEW_COMPILED_PATH=/tmp`
- `APP_CONFIG_CACHE=/tmp/config.php`
- `APP_EVENTS_CACHE=/tmp/events.php`
- `APP_PACKAGES_CACHE=/tmp/packages.php`
- `APP_ROUTES_CACHE=/tmp/routes.php`
- `APP_SERVICES_CACHE=/tmp/services.php`
- `DB_CONNECTION=...`
- `DB_HOST=...`
- `DB_PORT=...`
- `DB_DATABASE=...`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`

### Observaciones importantes

- Si Vercel seguia buscando `dist`, con `vercel.json` ahora queda forzado `outputDirectory: public`.
- Si la app despliega pero no carga estilos, verificar que el build haya generado `public/build`.
- En Vercel conviene agregar `VERCEL_FORCE_NO_BUILD_CACHE=1` si algun deploy empieza a fallar por cache.
- `public/storage` depende de que los archivos esten disponibles en el entorno de despliegue; para produccion estable conviene usar almacenamiento externo.

## 2. Plan para app movil con Expo

La app movil viva esta en `AutoGestExpo/` (Expo + Expo Router).

## 3. Objetivo de la app movil

Crear una app Expo para tres casos de uso:

- cliente: consultar vehiculos, citas, ordenes y gastos
- asesor: revisar solicitudes del chatbot, crear ordenes y subir fotos
- mecanico: ver ordenes asignadas, actualizar estados y adjuntar evidencias

## 4. Arquitectura recomendada

- stack: Expo + React Native + Expo Router
- estado remoto: TanStack Query
- autenticacion: Laravel Sanctum con tokens para mobile
- almacenamiento local: `@react-native-async-storage/async-storage`
- formularios: React Hook Form + Zod
- UI: NativeWind o tamagui; si se busca simplicidad, NativeWind
- notificaciones push: Expo Notifications
- camara/subida de fotos: Expo Image Picker o Expo Camera

## 5. Backend necesario antes de escalar

Aunque ya existe `routes/api.php`, antes de construir la app completa conviene:

1. Separar claramente el login web del login mobile.
2. Confirmar que Sanctum emita tokens para app nativa.
3. Versionar API como `/api/v1`.
4. Normalizar respuestas JSON y errores.
5. Agregar endpoints faltantes para:
   - dashboard resumido por rol
   - citas del chatbot
   - comentarios y fotos de ordenes
   - gastos del cliente
   - notificaciones

## 6. Fases sugeridas

### Fase 0. Decision tecnica

- tomar `AutoGestExpo/` como punto de partida
- definir si la primera entrega sera solo Android

### Fase 1. Base Expo

- actualizar `AutoGestExpo/` a la estructura objetivo
- instalar Expo Router
- definir navegacion autenticada y publica
- crear configuracion por ambiente: local, staging, production
- definir cliente API centralizado con Axios

### Fase 2. Autenticacion

- login
- persistencia de sesion
- logout
- recuperacion de perfil
- guards por rol

### Fase 3. MVP cliente

- mis vehiculos
- detalle del vehiculo
- mis citas
- detalle de orden
- historial de mantenimientos
- gastos/resumen

### Fase 4. MVP asesor

- bandeja de solicitudes chatbot
- detalle de solicitud
- confirmar/rechazar solicitud
- crear o editar orden
- ver fotos y comentarios

### Fase 5. MVP mecanico

- ordenes asignadas
- detalle de orden
- cambio de estado
- subir fotos
- agregar comentario tecnico

### Fase 6. Capacidades nativas

- push notifications
- camara
- galeria
- subida en background si aplica
- deep links

### Fase 7. Calidad y release

- testing de componentes y flujos criticos
- build EAS para Android
- beta interna
- metricas y crash reporting

## 7. Primer sprint recomendado

Entregar en 1 sprint:

1. login mobile
2. listado de vehiculos del cliente
3. detalle de orden de servicio
4. persistencia de sesion
5. configuracion de API por ambiente

Eso da una base real sin entrar todavia en fotos, push ni paneles internos.

## 8. Riesgos

- algunas rutas API actuales parecen pensadas para backoffice, no para mobile
- Vercel no es ideal para cargas pesadas, sesiones complejas ni archivos persistentes
- subida de fotos y archivos requerira revisar almacenamiento y limites
- si se mezclan WebView, Capacitor y Expo al mismo tiempo, el mantenimiento se vuelve mas caro

## 9. Siguiente paso recomendado

1. validar primero el deploy web en Vercel
2. luego elegir `AutoGestExpo/` como base oficial
3. despues implementar autenticacion mobile y modulo cliente MVP

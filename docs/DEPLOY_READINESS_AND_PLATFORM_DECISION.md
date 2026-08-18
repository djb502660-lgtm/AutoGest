# AutoGest: readiness de despliegue y decision de plataforma

## 1. Resumen ejecutivo

Estado actual del proyecto para despliegue:

- `build frontend`: listo
- `runtime Laravel en Vercel`: no listo
- `variables de entorno productivas`: bloqueante
- `filesystem/logs/views para serverless`: parcialmente corregido
- `base de datos productiva`: no validada
- `storage persistente para fotos/archivos`: no listo
- `plataforma recomendada`: Render o Railway antes que Vercel

Conclusion:

`AutoGest` todavia no esta listo para considerarse "production-ready" en `Vercel` sin mas ajustes. Si el objetivo es desplegar correctamente y con menos friccion, la mejor ruta es `Render` o `Railway`.

## 2. Auditoria con semaforo

### Listo

- `npm run build` genera `public/build`
- existe `vercel.json`
- existe `api/index.php`
- el deploy de Vercel compila correctamente
- los assets Vite se construyen correctamente

### Riesgo

- `config/view.php` fue adaptado a `/tmp`, pero no se confirmo runtime estable
- `config/logging.php` fue adaptado para `stderr` en Vercel
- `.vercelignore` ya excluye caches peligrosos de `bootstrap/cache`
- hay componentes moviles y web mixtos en el repo, lo cual complica el pipeline si no se separa el deployment scope

### Bloqueante

- runtime en Vercel sigue devolviendo `500`
- falta validar `APP_KEY` real en Vercel
- falta validar `DB_*` productivos
- falta definir `SESSION_DRIVER` compatible para serverless
- falta definir `CACHE_STORE` compatible para serverless
- falta definir almacenamiento persistente para fotos y archivos
- no hay validacion completa de health check real en produccion
- Laravel trae caches/local artifacts que no deben contaminar el entorno de deploy

## 3. Hallazgos tecnicos

### Hallazgo 1. El build no es el problema

El proyecto compila bien con Vite y Vercel acepta el deploy. El fallo ocurre despues, cuando Laravel arranca en runtime.

Impacto:
- el error no se resuelve "solo" tocando `vite.config.js`
- el problema esta en configuracion de entorno y arquitectura serverless

### Hallazgo 2. Vercel no se comporta como un servidor PHP tradicional

En Vercel:

- el filesystem del deploy es read-only
- `storage/logs` no es escribible
- `storage/framework/views` no debe usarse como en hosting tradicional
- archivos de cache generados en local pueden romper produccion

Impacto:
- Laravel necesita adaptaciones explicitas

### Hallazgo 3. AutoGest usa capacidades que piden backend mas estable

El proyecto no parece un landing page ni una app estatica. Tiene:

- autenticacion
- roles
- ordenes
- mantenimientos
- reportes PDF
- fotos
- chatbot
- posibles sesiones, colas y almacenamiento

Impacto:
- Vercel puede usarse, pero no es la plataforma mas natural

## 4. Checklist minimo para declarar “listo para desplegar”

### Aplicacion

- [ ] `APP_KEY` configurada en produccion
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` correcta
- [ ] timezone y locale validados

### Base de datos

- [ ] base de datos productiva creada
- [ ] credenciales `DB_*` validadas
- [ ] conectividad desde la plataforma validada
- [ ] migraciones probadas en entorno remoto

### Estado serverless / runtime

- [ ] `LOG_CHANNEL=stderr`
- [ ] `SESSION_DRIVER=cookie` o alternativa compatible
- [ ] `CACHE_STORE=array` o alternativa remota compatible
- [ ] `QUEUE_CONNECTION=sync` o worker real fuera de Vercel
- [ ] `VIEW_COMPILED_PATH=/tmp/framework/views`

### Archivos y storage

- [ ] definir storage persistente para fotos
- [ ] validar subida/lectura de archivos
- [ ] no depender de `storage/app/public` local

### Deploy

- [ ] excluir caches de `bootstrap/cache`
- [ ] verificar que `public/build` se sirva bien
- [ ] endpoint de health check respondiendo 200
- [ ] smoke test funcional despues del deploy

## 5. Comparacion de plataforma

## Vercel

### Bueno para

- despliegue rapido
- proyectos con frontend dominante
- sitios con baja complejidad de backend
- experiencias serverless simples

### Malo para

- Laravel con estado
- logs en filesystem
- storage persistente
- sesiones clasicas
- procesos largos, colas y jobs
- apps con mucha logica de backoffice

### Veredicto para AutoGest

`Posible, pero no recomendado como primera opcion`.

Solo lo elegiria si quieres mantener un despliegue muy barato/rapido y aceptas adaptar Laravel al modelo serverless.

## Render

### Bueno para

- Laravel casi sin trucos
- deploy desde Git
- web services persistentes
- background workers
- cron jobs
- logs estables

### Malo para

- un poco mas de configuracion que Vercel
- cold start y performance dependen del plan

### Veredicto para AutoGest

`Muy recomendable`.

Es probablemente la mejor combinacion entre facilidad, compatibilidad con Laravel y costo razonable.

## Railway

### Bueno para

- despliegue rapido de apps full-stack
- experiencia sencilla con base de datos
- menos friccion que Vercel para backends

### Malo para

- menor previsibilidad de costos a medida que crece
- menos “tradicional” que un VPS/Forge para setups mas complejos

### Veredicto para AutoGest

`Recomendable`.

Ideal si quieres rapidez de salida y no quieres pelear con las restricciones de Vercel.

## 6. Recomendacion final

### Opcion recomendada

Desplegar `AutoGest` en `Render` o `Railway`.

### Opcion aceptable

Continuar en `Vercel`, pero solo despues de:

1. completar variables de entorno
2. cerrar storage/logs/session/cache
3. validar base de datos remota
4. confirmar que `/up` responde correctamente

## 7. Ruta sugerida

### Ruta A. Despliegue correcto y rapido

1. elegir `Render`
2. configurar servicio web PHP
3. conectar repo
4. setear variables
5. apuntar a base de datos remota
6. correr migraciones
7. smoke test funcional

### Ruta B. Persistir con Vercel

1. revisar variables de entorno una por una
2. forzar config compatible con serverless
3. validar DB
4. corregir el runtime `500`
5. probar `/up`, `/`, login y dashboard

## 8. Decision recomendada hoy

Si tu prioridad es “publicar bien el sistema”, yo moveria este proyecto a `Render`.

Si tu prioridad es “intentar mantenerlo en Vercel”, se puede seguir, pero sera una ruta mas lenta y con mas ajustes especificos de Laravel serverless.

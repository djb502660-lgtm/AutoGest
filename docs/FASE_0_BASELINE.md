# FASE 0 - VALIDACIÓN INICIAL DEL PROYECTO (BASELINE)
**AutoGest - Sistema de Gestión de Mantenimiento Vehicular**
**Fecha:** 2026-08-04
**Estado:** Validación de Estado Actual
**Tiempo Estimado:** 4-6 horas (aumentado por inventario completo y matriz de dependencias)

---

## OBJETIVO

Confirmar que la auditoría coincide con el estado real del código antes de empezar a modificar archivos, estableciendo una línea base (baseline) del proyecto.

---

## ACTIVIDADES DE VALIDACIÓN

### 1. Verificación del Entorno

#### 1.1 Verificación de PHP
**Comando:** `php --version`
**Esperado:** PHP 8.2+
**Evidencia:** Guardar versión de PHP

#### 1.2 Verificación de Composer
**Comando:** `composer --version`
**Esperado:** Composer 2.0+
**Evidencia:** Guardar versión de Composer

#### 1.3 Verificación de Node.js
**Comando:** `node --version`
**Esperado:** Node.js 18+
**Evidencia:** Guardar versión de Node.js

#### 1.4 Verificación de npm
**Comando:** `npm --version`
**Esperado:** npm 9+
**Evidencia:** Guardar versión de npm

#### 1.5 Verificación de MySQL
**Comando:** `mysql --version`
**Esperado:** MySQL 8.0+
**Evidencia:** Guardar versión de MySQL

#### 1.6 Verificación de Laravel
**Comando:** `php artisan --version`
**Esperado:** Laravel 12.0
**Evidencia:** Guardar versión de Laravel

#### 1.7 Verificación de Vite
**Comando:** `npm list vite`
**Esperado:** Vite 7.0+
**Evidencia:** Guardar versión de Vite

---

### 2. Verificación de Dependencias

#### 2.1 Composer Install
**Comando:** `composer install`
**Esperado:** Instalación sin errores
**Evidencia:** 
- Guardar salida de composer install
- Verificar que vendor/ existe
- Verificar que composer.json.lock existe

#### 2.2 Composer Validate
**Comando:** `composer validate`
**Esperado:** Validación sin errores
**Evidencia:** Guardar salida de composer validate

#### 2.3 Composer Dump-Autoload
**Comando:** `composer dump-autoload`
**Esperado:** Generación sin errores
**Evidencia:** Guardar salida de composer dump-autoload

#### 2.4 NPM Install
**Comando:** `npm install`
**Esperado:** Instalación sin errores
**Evidencia:**
- Guardar salida de npm install
- Verificar que node_modules/ existe
- Verificar que package-lock.json existe

#### 2.5 NPM Audit
**Comando:** `npm audit`
**Esperado:** Sin vulnerabilidades críticas
**Evidencia:** Guardar salida de npm audit

#### 2.6 NPM Build
**Comando:** `npm run build`
**Esperado:** Compilación sin errores
**Evidencia:**
- Guardar salida de npm run build
- Verificar que public/build/ existe
- Verificar que assets se compilaron

---

### 3. Validación del Proyecto

#### 3.1 Verificación de .env
**Comando:** `ls -la .env`
**Esperado:** Archivo .env existe
**Evidencia:**
- Guardar existencia de .env
- Si existe, guardar contenido (sensible redactado)
- Si no existe, documentar que debe crearse

#### 3.2 Verificación de APP_URL
**Comando:** `grep APP_URL .env`
**Esperado:** APP_URL=http://autogest.test (no localhost)
**Evidencia:**
- Guardar valor actual de APP_URL
- Documentar si necesita corrección

#### 3.3 Laravel About
**Comando:** `php artisan about`
**Esperado:** Información completa del entorno Laravel
**Evidencia:**
- Guardar salida completa de php artisan about
- Verificar versión de Laravel
- Verificar versión de PHP
- Verificar entorno (local/production)

#### 3.4 Route List
**Comando:** `php artisan route:list`
**Esperado:** Listado completo de rutas
**Evidencia:**
- Guardar salida de php artisan route:list
- Verificar número total de rutas
- Verificar que no hay rutas duplicadas
- Documentar rutas por módulo

#### 3.5 Migrate Status
**Comando:** `php artisan migrate:status`
**Esperado:** Estado de migraciones
**Evidencia:**
- Guardar salida de php artisan migrate:status
- Verificar qué migraciones están pendientes
- Verificar qué migraciones están ejecutadas
- Documentar estado de base de datos

#### 3.6 Test Execution
**Comando:** `php artisan test`
**Esperado:** Ejecución de tests existentes
**Evidencia:**
- Guardar salida de php artisan test
- Verificar cuántos tests existen
- Verificar cuántos tests pasan
- Verificar cuántos tests fallan
- Documentar estado de tests

#### 3.7 Storage Link
**Comando:** `ls -la public/storage`
**Esperado:** Enlace simbólico de storage
**Evidencia:**
- Guardar estado de public/storage
- Verificar si es enlace simbólico
- Documentar si storage:link necesita ejecutarse

---

### 4. Generación de Evidencias

#### 4.1 Listado de Rutas
**Archivo:** `BASELINE/route-list.txt`
**Contenido:** Salida completa de `php artisan route:list`

#### 4.2 Listado de Migraciones
**Archivo:** `BASELINE/migrate-status.txt`
**Contenido:** Salida completa de `php artisan migrate:status`

#### 4.3 Listado de Paquetes Composer
**Archivo:** `BASELINE/composer-packages.txt`
**Contenido:** Salida de `composer show`

#### 4.4 Listado de Paquetes NPM
**Archivo:** `BASELINE/npm-packages.txt`
**Contenido:** Salida de `npm list`

#### 4.5 Versión de Laravel
**Archivo:** `BASELINE/laravel-version.txt`
**Contenido:** Salida de `php artisan --version`

#### 4.6 Versión de PHP
**Archivo:** `BASELINE/php-version.txt`
**Contenido:** Salida de `php --version`

#### 4.7 Estructura de Carpetas
**Archivo:** `BASELINE/directory-structure.txt`
**Contenido:** Salida de `tree -L 3` o equivalente

#### 4.8 Capturas del Funcionamiento Actual
**Directorio:** `BASELINE/screenshots/`
**Contenido:**
- Captura de página de inicio
- Captura de página de login
- Captura de dashboard admin (si accesible)
- Captura de cualquier otra vista accesible

---

### 5. Baseline del Repositorio (INVENTARIO COMPLETO)

**Objetivo:** Generar un inventario completo del proyecto antes de modificar cualquier archivo, para medir el impacto de las modificaciones.

#### 5.1 Conteo de Controladores
**Comando:** `find app/Modules -name "*Controller.php" | wc -l`
**Archivo:** `BASELINE/controllers-count.txt`
**Detalle:** Guardar listado completo de todos los controladores con sus rutas

#### 5.2 Conteo de Modelos
**Comando:** `find app/Modules -name "*.php" -path "*/Models/*" | wc -l`
**Archivo:** `BASELINE/models-count.txt`
**Detalle:** Guardar listado completo de todos los modelos con sus relaciones

#### 5.3 Conteo de Migraciones
**Comando:** `find database/migrations -name "*.php" | wc -l`
**Archivo:** `BASELINE/migrations-count.txt`
**Detalle:** Guardar listado completo de todas las migraciones con sus tablas

#### 5.4 Conteo de Vistas Blade
**Comando:** `find app/Modules -name "*.blade.php" | wc -l`
**Archivo:** `BASELINE/views-count.txt`
**Detalle:** Guardar listado completo de todas las vistas Blade organizadas por módulo

#### 5.5 Conteo de Rutas Web
**Comando:** `php artisan route:list --path="" | grep "GET\|POST\|PUT\|DELETE" | wc -l`
**Archivo:** `BASELINE/web-routes-count.txt`
**Detalle:** Guardar listado de rutas web organizadas por módulo

#### 5.6 Conteo de Rutas API
**Comando:** `php artisan route:list --path="api" | wc -l`
**Archivo:** `BASELINE/api-routes-count.txt`
**Detalle:** Guardar listado de rutas API organizadas por módulo

#### 5.7 Conteo de Middleware
**Comando:** `find app/Http/Middleware -name "*.php" | wc -l`
**Archivo:** `BASELINE/middleware-count.txt`
**Detalle:** Guardar listado de todos los middleware con su uso

#### 5.8 Conteo de Policies
**Comando:** `find app/Policies -name "*.php" | wc -l`
**Archivo:** `BASELINE/policies-count.txt`
**Detalle:** Guardar listado de todas las policies con sus modelos asociados

#### 5.9 Conteo de Servicios
**Comando:** `find app/Modules -name "*Service.php" | wc -l`
**Archivo:** `BASELINE/services-count.txt`
**Detalle:** Guardar listado de todos los servicios con sus responsabilidades

#### 5.10 Conteo de Jobs
**Comando:** `find app/Jobs -name "*.php" | wc -l`
**Archivo:** `BASELINE/jobs-count.txt`
**Detalle:** Guardar listado de todos los jobs con sus colas

#### 5.11 Conteo de Eventos
**Comando:** `find app/Events -name "*.php" | wc -l`
**Archivo:** `BASELINE/events-count.txt`
**Detalle:** Guardar listado de todos los eventos con sus listeners

#### 5.12 Conteo de Componentes Blade
**Comando:** `find resources/views/components -name "*.blade.php" | wc -l`
**Archivo:** `BASELINE/components-count.txt`
**Detalle:** Guardar listado de todos los componentes Blade con su uso

#### 5.13 Conteo de Pruebas Existentes
**Comando:** `find tests -name "*.php" | wc -l`
**Archivo:** `BASELINE/tests-count.txt`
**Detalle:** Guardar listado de todas las pruebas organizadas por tipo (Feature, Unit)

#### 5.14 Resumen del Inventario
**Archivo:** `BASELINE/inventory-summary.md`
**Contenido:**
```markdown
# Inventario del Repositorio - Baseline

## Resumen de Componentes
- Total Controladores: [X]
- Total Modelos: [X]
- Total Migraciones: [X]
- Total Vistas Blade: [X]
- Total Rutas Web: [X]
- Total Rutas API: [X]
- Total Middleware: [X]
- Total Policies: [X]
- Total Servicios: [X]
- Total Jobs: [X]
- Total Eventos: [X]
- Total Componentes Blade: [X]
- Total Pruebas: [X]

## Distribución por Módulo
- Vehicles: [X controladores, X modelos, X vistas]
- Maintenance: [X controladores, X modelos, X vistas]
- Chatbot: [X controladores, X modelos, X vistas]
- [Resto de módulos]
```

---

### 6. Matriz de Dependencias

**Objetivo:** Documentar qué componentes dependen de otros antes de tocar cualquier módulo, para evitar romper funcionalidades durante la refactorización.

#### 6.1 Análisis de Dependencias por Módulo
**Archivo:** `BASELINE/dependency-matrix.md`
**Contenido:**

```markdown
# Matriz de Dependencias - Baseline

## Estructura de la Matriz
| Componente     | Depende de         | Utilizado por                | Riesgo     | Impacto   |
| -------------- | ------------------ | ---------------------------- | ---------- | --------- |
| Vehicle        | User               | Maintenance, Chatbot, Orders | Alto       | Crítico   |
| ServiceOrder   | Vehicle            | Mechanic, Reports            | Alto       | Alto      |
| ChatbotService | AppointmentService | Cliente                      | Alto       | Alto      |
```

#### 6.2 Metodología de Análisis
**Pasos:**
1. Analizar cada modelo para identificar relaciones (belongsTo, hasMany, etc.)
2. Analizar cada controlador para identificar dependencias de servicios
3. Analizar cada servicio para identificar dependencias de otros servicios
4. Analizar cada vista para identificar componentes utilizados
5. Analizar cada ruta para identificar controladores y middleware
6. Documentar el riesgo de cambio (Alto/Medio/Bajo)
7. Documentar el impacto funcional (Crítico/Alto/Medio/Bajo)

#### 6.3 Mapa de Dependencias Visuales
**Archivo:** `BASELINE/dependency-graph.md`
**Contenido:** Representación visual de las dependencias usando Mermaid o ASCII art

#### 6.4 Componentes Críticos Identificados
**Archivo:** `BASELINE/critical-components.md`
**Contenido:**
- Lista de componentes con riesgo ALTO
- Lista de componentes con impacto CRÍTICO
- Lista de componentes que deben cambiarse con extrema precaución

---

### 7. Checklist de Implementación

**Objetivo:** Cada tarea del roadmap debe responder obligatoriamente a estas preguntas para garantizar trazabilidad y facilitar el control del proyecto.

#### 7.1 Plantilla de Checklist
**Archivo:** `BASELINE/implementation-checklist-template.md`
**Contenido:**

```markdown
# Checklist de Implementación

## Tarea: [Nombre de la Tarea]
**Fase:** [Fase X]
**Fecha:** [YYYY-MM-DD]
**Responsable:** [Nombre]

## 1. Archivos a Modificar
- [ ] Archivo: [ruta/archivo.php]
  - Tipo de cambio: [refactor/nuevo/eliminación]
  - Líneas afectadas: [estimación]
  - Justificación: [por qué se modifica]

## 2. Archivos Nuevos a Crear
- [ ] Archivo: [ruta/nuevo-archivo.php]
  - Propósito: [qué hace]
  - Dependencias: [qué requiere]

## 3. Archivos a Eliminar/Desusar
- [ ] Archivo: [ruta/archivo-obsoleto.php]
  - Motivo: [por qué se elimina]
  - Impacto: [qué afecta]
  - Plan de migración: [cómo se migra]

## 4. Pruebas a Ejecutar
- [ ] Prueba unitaria: [test específico]
- [ ] Prueba funcional: [qué funcionalidad]
- [ ] Prueba de integración: [qué sistema]
- [ ] Prueba de regresión: [qué no debe romperse]

## 5. Análisis de Riesgo
- **Nivel de Riesgo:** [Alto/Medio/Bajo]
- **Impacto si falla:** [Crítico/Alto/Medio/Bajo]
- **Probabilidad de fallo:** [Alta/Media/Baja]
- **Mitigación:** [cómo se reduce el riesgo]

## 6. Plan de Reversión
- **Comando de reversión Git:** [git revert / git reset]
- **Base de datos:** [cómo se revierten cambios de migración]
- **Configuración:** [cómo se revierte configuración]
- **Tiempo estimado de reversión:** [X minutos]

## 7. Criterios de Validación
- [ ] Código compila sin errores
- [ ] Tests pasan
- [ ] Funcionalidad verificada manualmente
- [ ] No hay warnings
- [ ] Performance no degradada
- [ ] No hay vulnerabilidades introducidas

## 8. Documentación
- [ ] Changelog actualizado
- [ ] Comentarios en código agregados si necesario
- [ ] README actualizado si aplica
- [ ] Technical debt documentado si aplica

## 9. Validación Post-Implementación
- [ ] Review de código completado
- [ ] Aprobación obtenida
- [ ] Baseline actualizado
- [ ] Inventario actualizado
```

#### 7.2 Requisito Obligatorio
**REGLA:** Ninguna tarea de implementación puede comenzar sin que este checklist esté completo y aprobado.

---

### 8. Criterios de Salida de FASE 0

**Objetivo:** La fase no debe cerrarse hasta cumplir todos estos puntos.

#### 8.1 Criterios de Ejecución
- [ ] Proyecto ejecuta correctamente con `php artisan serve` o el entorno definido
- [ ] Dependencias instaladas sin errores (Composer y NPM)
- [ ] Base de datos accesible y configurada
- [ ] Migraciones verificadas y en estado conocido
- [ ] Seeders verificados (si existen)
- [ ] Rutas cargadas correctamente sin errores
- [ ] Autenticación funcional (puede loguearse)
- [ ] Chatbot responde (puede probarse)
- [ ] Evidencias del estado inicial almacenadas en BASELINE/
- [ ] Baseline documentado y validado

#### 8.2 Criterios de Documentación
- [ ] Inventario completo del repositorio generado
- [ ] Matriz de dependencias documentada
- [ ] Plantilla de checklist de implementación creada
- [ ] Reporte de estado del entorno generado
- [ ] Reporte de estado del proyecto generado
- [ ] Reporte de discrepancias generado
- [ ] Directorio BASELINE/ completo y organizado

#### 8.3 Criterios de Validación
- [ ] Auditoría coincide con estado real del código
- [ ] No hay cambios no documentados
- [ ] Estado del proyecto es consistente
- [ ] No hay archivos corruptos o faltantes
- [ ] Sistema está en estado conocido y reproducible
- [ ] Matriz de dependencias completa
- [ ] Plan de implementación confirmado o ajustado

#### 8.4 Criterios de Aprobación
- [ ] Todos los criterios anteriores cumplidos
- [ ] Reporte final de FASE 0 generado
- [ ] Firma de aprobación del baseline
- [ ] FASE 0 oficialmente cerrada
- [ ] FASE 1 autorizada para comenzar

---

## PLAN DE CONTINGENCIA

### Si Composer Install Falla
- **Acción:** Verificar versión de PHP y Composer
- **Acción:** Limpiar caché de Composer: `composer clear-cache`
- **Acción:** Eliminar vendor/ y.lock y reinstalar
- **Evidencia:** Documentar error y solución

### Si NPM Install Falla
- **Acción:** Verificar versión de Node.js y npm
- **Acción:** Limpiar caché de npm: `npm cache clean`
- **Acción:** Eliminar node_modules/ y.lock y reinstalar
- **Evidencia:** Documentar error y solución

### Si .env No Existe
- **Acción:** Documentar que .env debe crearse
- **Acción:** Usar .env.example como referencia
- **Evidencia:** Documentar configuración requerida

### Si Migrate Status Muestra Errores
- **Acción:** Documentar errores de migración
- **Acción:** Verificar conexión a base de datos
- **Acción:** Verificar credenciales en .env
- **Evidencia:** Documentar estado de base de datos

### Si Tests Fallan
- **Acción:** Documentar tests que fallan
- **Acción:** Verificar que tests sean válidos
- **Acción:** Documentar si tests necesitan actualización
- **Evidencia:** Documentar estado de tests

---

## REPORTES GENERADOS

### Reporte de Estado del Entorno
**Archivo:** `BASELINE/environment-report.md`
**Contenido:**
- Versiones de todas las herramientas
- Estado de instalación
- Compatibilidad verificada
- Recomendaciones si aplica

### Reporte de Estado del Proyecto
**Archivo:** `BASELINE/project-status-report.md`
**Contenido:**
- Estado de dependencias
- Estado de configuración
- Estado de base de datos
- Estado de tests
- Discrepancias con auditoría

### Reporte de Discrepancias
**Archivo:** `BASELINE/discrepancies-report.md`
**Contenido:**
- Diferencias entre auditoría y estado real
- Cambios no documentados
- Archivos faltantes o adicionales
- Recomendaciones de corrección

---

## CRITERIOS DE ÉXITO DE FASE 0

### Éxito Técnico
- [ ] Entorno completamente validado
- [ ] Dependencias instaladas y funcionando
- [ ] Proyecto Laravel en estado conocido
- [ ] Evidencias completas generadas
- [ ] Baseline establecido

### Éxito de Documentación
- [ ] Reportes generados completos
- [ ] Discrepancias documentadas
- [ ] Estado actual completamente caracterizado
- [ ] Referencia clara para comparación posterior

### Éxito de Verificación
- [ ] Auditoría validada contra estado real
- [ ] Proyecto en estado reproducible
- [ ] No hay sorpresas ocultas
- [ ] Plan de implementación confirmado

---

## TIEMPO ESTIMADO

**Tiempo Total:** 2-3 horas

**Desglose:**
- Verificación del entorno: 30 minutos
- Verificación de dependencias: 45 minutos
- Validación del proyecto: 45 minutos
- Generación de evidencias: 30 minutos
- Generación de reportes: 30 minutos

---

## PRÓXIMOS PASOS

### Si FASE 0 es Exitosa
1. Revisar reportes de discrepancias
2. Ajustar plan de implementación si es necesario
3. Proceder con FASE 1: Configuración del Entorno
4. Usar baseline como referencia durante implementación

### Si FASE 0 Encuentra Problemas
1. Documentar problemas encontrados
2. Evaluar impacto en plan de implementación
3. Ajustar estimaciones si es necesario
4. Resolver problemas críticos antes de continuar
5. Repetir FASE 0 después de correcciones

---

## CONCLUSIÓN

Esta FASE 0 establece una línea base crítica del proyecto antes de comenzar cualquier modificación, asegurando que el plan de implementación se basa en el estado real del código y no en suposiciones.

**Fase 0 completada** = Proyecto en estado conocido, reproducible, y listo para implementación controlada.

---

**Fase 0 preparada por:** Equipo Multidisciplinario de Ingeniería de Software  
**Fecha:** 2026-08-04  
**Versión:** 1.0  
**Estado:** Aprobada para ejecución inmediata

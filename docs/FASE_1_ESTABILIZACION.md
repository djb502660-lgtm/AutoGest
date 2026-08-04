# FASE 1 - ESTABILIZACIÓN DEL SISTEMA
**AutoGest - Sistema de Gestión de Mantenimiento Vehicular**
**Baseline:** v0.0-baseline
**Fecha estimada inicio:** Pendiente
**Duración estimada:** Pendiente

---

## OBJETIVO DE LA FASE

Estabilizar el sistema antes de realizar refactorizaciones arquitectónicas o implementaciones de nuevas funcionalidades. El foco es eliminar vulnerabilidades, resolver inconsistencias y alcanzar el 100% de tests pasando.

---

## SPRINT 1 - SEGURIDAD Y CONSISTENCIA

### Objetivo Principal
Alcanzar 56/56 pruebas aprobadas y eliminar vulnerabilidades críticas conocidas.

### Tareas de Seguridad

#### 1. Corregir bypass de CSRF del chatbot
- **Prioridad:** P0
- **Archivo afectado:** app/Modules/Chatbot/Http/Controllers/Client/ChatbotController.php
- **Acción:** Implementar protección CSRF para rutas de chatbot
- **Validación:** Tests de seguridad CSRF
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

#### 2. Implementar Rate Limiting
- **Prioridad:** P1
- **Archivos afectados:** routes/*.php
- **Acción:** Implementar throttling para rutas públicas
- **Validación:** Tests de rate limiting
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

#### 3. Revisar validaciones (FormRequest)
- **Prioridad:** P1
- **Archivos afectados:** app/Http/Requests/*.php
- **Acción:** Crear/actualizar FormRequests para todos los controladores
- **Validación:** Tests de validación
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

#### 4. Revisar Policies y Gates
- **Prioridad:** P1
- **Archivos afectados:** app/Policies/*.php, app/Providers/AuthServiceProvider.php
- **Acción:** Verificar que todas las rutas sensibles tengan autorización
- **Validación:** Tests de autorización
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

#### 5. Eliminar rutas inseguras
- **Prioridad:** P0
- **Archivos afectados:** routes/*.php
- **Acción:** Eliminar o proteger rutas sin autenticación apropiada
- **Validación:** Auditoría de rutas
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

### Tareas de Consistencia

#### 6. Unificar maintenance_type / service_type
- **Prioridad:** P1
- **Archivos afectados:** database/migrations/*.php, app/Models/*.php
- **Acción:** Estandarizar nomenclatura de tipos de servicio
- **Validación:** Tests de consistencia de datos
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

#### 7. Revisar migraciones
- **Prioridad:** P1
- **Archivos afectados:** database/migrations/*.php
- **Acción:** Verificar integridad y orden de migraciones
- **Validación:** migrate:fresh + tests
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

#### 8. Revisar seeders
- **Prioridad:** P2
- **Archivos afectados:** database/seeders/*.php
- **Acción:** Crear seeders para datos de prueba consistentes
- **Validación:** db:seed + tests
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

#### 9. Revisar factories
- **Prioridad:** P2
- **Archivos afectados:** database/factories/*.php
- **Acción:** Verificar que factories generen datos válidos
- **Validación:** Tests con factories
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

### Tareas de Calidad

#### 10. Resolver test ChatbotAppointmentManageTest
- **Prioridad:** P2
- **Archivo afectado:** tests/Feature/ChatbotAppointmentManageTest.php
- **Acción:** Corregir assertion de "cancelada correctamente"
- **Validación:** php artisan test
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

---

## CRITERIOS DE ACEPTACIÓN - SPRINT 1

### Criterios Técnicos
- [ ] 56/56 pruebas aprobadas (100%)
- [ ] Sin vulnerabilidades críticas conocidas
- [ ] Sin inconsistencias de base de datos
- [ ] Sin rutas inseguras
- [ ] Sin errores de compilación
- [ ] CSRF protection implementada en todas las rutas POST
- [ ] Rate limiting implementado en rutas públicas
- [ ] FormRequests implementados para todos los formularios
- [ ] Policies verificadas en todas las rutas sensibles

### Criterios de Seguridad
- [ ] Bypass CSRF corregido
- [ ] Rate limiting activo
- [ ] Validaciones de input implementadas
- [ ] Authorization verificada
- [ ] Rutas inseguras eliminadas/protegidas

### Criterios de Calidad
- [ ] Nomenclatura consistente (maintenance_type vs service_type)
- [ ] Migraciones ordenadas correctamente
- [ ] Seeders funcionales
- [ ] Factories generan datos válidos
- [ ] Test ChatbotAppointmentManageTest corregido

---

## SPRINT 2 - REFACTORIZACIÓN ARQUITECTÓNICA

### Objetivo Principal
Mejorar la arquitectura del sistema para hacerlo más mantenible y escalable.

### Tareas de Arquitectura

#### 1. Implementar Repository Pattern
- **Prioridad:** P1
- **Archivos nuevos:** app/Repositories/*.php
- **Archivos afectados:** app/Http/Controllers/*.php
- **Acción:** Crear repositories para modelos principales
- **Validación:** Tests existentes deben seguir pasando
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

#### 2. Mejorar Service Layer
- **Prioridad:** P1
- **Archivos afectados:** app/Services/*.php
- **Acción:** Mover lógica de negocio de controllers a services
- **Validación:** Tests existentes deben seguir pasando
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

#### 3. Optimizar consultas (eliminar N+1)
- **Prioridad:** P1
- **Archivos afectados:** app/Http/Controllers/*.php
- **Acción:** Implementar eager loading donde sea necesario
- **Validación:** Laravel Debug Bar / Telescope
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

#### 4. Refactorizar controllers pesados
- **Prioridad:** P2
- **Archivos afectados:** app/Http/Controllers/*.php
- **Acción:** Extraer lógica a services y repositories
- **Validación:** Tests existentes deben seguir pasando
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

#### 5. Implementar DTOs donde sea apropiado
- **Prioridad:** P2
- **Archivos nuevos:** app/DTOs/*.php
- **Archivos afectados:** app/Services/*.php
- **Acción:** Crear DTOs para transferencia de datos compleja
- **Validación:** Tests existentes deben seguir pasando
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md

---

## CRITERIOS DE ACEPTACIÓN - SPRINT 2

### Criterios de Arquitectura
- [ ] Repository Pattern implementado para modelos principales
- [ ] Service Layer mejorada
- [ ] Consultas N+1 eliminadas
- [ ] Controllers refactorizados
- [ ] DTOs implementados donde apropiado

### Criterios de Calidad
- [ ] 56/56 pruebas siguen pasando
- [ ] Performance no degradada
- [ ] Código más mantenible
- [ ] Separación de responsabilidades clara

### Criterios de Métricas
- [ ] Complejidad ciclomática reducida
- [ ] Acoplamiento reducido
- [ ] Cohesión mejorada
- [ ] Code coverage mantenido o mejorado

---

## CRITERIOS DE SALIDA - FASE 1

### Estado General
- [ ] Sprint 1 completado (Seguridad y Consistencia)
- [ ] Sprint 2 completado (Refactorización Arquitectónica)
- [ ] Todos los criterios de aceptación cumplidos

### Métricas Finales
- [ ] 56/56 pruebas aprobadas (100%)
- [ ] 0 vulnerabilidades críticas
- [ ] 0 inconsistencias de base de datos
- [ ] 0 rutas inseguras
- [ ] Performance mejorada o mantenida
- [ ] Arquitectura mejorada

### Documentación
- [ ] Dashboard actualizado
- [ ] Matriz de dependencias actualizada
- [ ] Checklists completados
- [ ] Lecciones aprendidas documentadas

---

## PLAN DE REVERSIÓN

### Si Sprint 1 falla
- Revertir a tag v0.0-baseline
- Revisar auditorías de seguridad
- Ajustar enfoque según hallazgos

### Si Sprint 2 falla
- Revertir cambios de arquitectura
- Mantener mejoras de Sprint 1
- Ajustar alcance de refactorización

---

## RIESGOS Y MITIGACIÓN

### Riesgos Identificados
1. **Complejidad de refactorización:** Puede introducir bugs
   - **Mitigación:** Tests exhaustivos, cambios incrementales

2. **Dependencias ocultas:** Pueden romper funcionalidad
   - **Mitigación:** Matriz de dependencias, análisis de impacto

3. **Performance degradation:** Refactorización puede afectar performance
   - **Mitigación:** Benchmarking antes/después, profiling

---

## REFERENCIAS

- **Baseline:** docs/BASELINE/
- **Dashboard:** docs/DASHBOARD_AVANCE.md
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md
- **Matriz Dependencias:** docs/BASELINE/dependency-matrix.md
- **Git Branch:** baseline-estable
- **Git Tag:** v0.0-baseline

---

## NOTAS

**Estado Actual:** ⬜ FASE 1 NO INICIADA
**Próxima Acción:** Esperar aprobación para iniciar Sprint 1
**Dependencias:** FASE 0 completada, baseline congelado

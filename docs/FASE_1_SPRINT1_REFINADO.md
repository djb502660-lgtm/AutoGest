# SPRINT 1 - SEGURIDAD Y CONSISTENCIA (REFINADO)
**FASE 1 - Estabilización del Sistema**
**Baseline:** v0.0-baseline
**Enfoque:** CORRECCIÓN Y ESTABILIZACIÓN (sin funcionalidades nuevas)

---

## OBJETIVO MUY CONCRETO

Eliminar vulnerabilidades críticas y alcanzar 56/56 pruebas aprobadas para estabilizar el sistema antes de cualquier refactorización arquitectónica.

---

## DURACIÓN ESTIMADA
3-5 días

---

## TAREAS PRIORITARIAS (Enfoque Concreto)

### 1. Corregir bypass CSRF del chatbot 🔴 CRÍTICO
- **Prioridad:** P0
- **Archivo afectado:** app/Modules/Chatbot/Http/Controllers/Client/ChatbotController.php
- **Acción:** Implementar protección CSRF para rutas de chatbot
- **Validación:** Tests de seguridad CSRF
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md
- **Tiempo estimado:** 2-3 horas

### 2. Resolver test ChatbotAppointmentManageTest 🔴 CRÍTICO
- **Prioridad:** P0
- **Archivo afectado:** tests/Feature/ChatbotAppointmentManageTest.php
- **Objetivo:** Alcanzar 56/56 tests (100%)
- **Acción:** Corregir assertion de "cancelada correctamente"
- **Validación:** php artisan test (debe pasar 56/56)
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md
- **Tiempo estimado:** 1-2 horas

### 3. Unificar maintenance_type / service_type 🟡 IMPORTANTE
- **Prioridad:** P1
- **Archivos afectados:** database/migrations/*.php, app/Models/*.php
- **Acción:** Estandarizar nomenclatura de tipos de servicio
- **Validación:** Tests de consistencia de datos
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md
- **Tiempo estimado:** 3-4 horas

### 4. Revisar todas las rutas protegidas 🔴 CRÍTICO
- **Prioridad:** P0
- **Archivos afectados:** routes/*.php
- **Acción:** Verificar y proteger rutas sin autenticación apropiada
- **Validación:** Auditoría de rutas + tests de autorización
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md
- **Tiempo estimado:** 2-3 horas

### 5. Implementar Rate Limiting 🟡 IMPORTANTE
- **Prioridad:** P1
- **Archivos afectados:** routes/*.php
- **Acción:** Implementar throttling para rutas públicas
- **Validación:** Tests de rate limiting
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md
- **Tiempo estimado:** 2-3 horas

### 6. Verificar ejecución sin regresiones 🔴 CRÍTICO
- **Prioridad:** P0
- **Archivos afectados:** Todo el proyecto
- **Acción:** Ejecutar suite completa de tests después de cada cambio
- **Validación:** php artisan test (56/56 pasando)
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md
- **Tiempo estimado:** Continuo durante sprint

---

## TAREAS SECUNDARIAS (Solo si tiempo lo permite)

⚠️ **Estas tareas solo se abordarán si las tareas prioritarias se completan antes del tiempo estimado.**

- [ ] Revisar validaciones (FormRequest) (P2)
- [ ] Revisar Policies y Gates (P2)
- [ ] Revisar migraciones (P2)
- [ ] Revisar seeders y factories (P2)

---

## REGLAS DE ORO PARA ESTE SPRINT

### ✅ LO QUE SE DEBE HACER
- Corregir vulnerabilidades identificadas
- Resolver tests fallidos
- Unificar nomenclatura inconsistente
- Verificar seguridad de rutas
- Implementar protecciones básicas
- Validar que no hay regresiones

### ❌ LO QUE NO SE DEBE HACER
- **NO añadir funcionalidades nuevas**
- **NO refactorizar código que funciona**
- **NO implementar patrones de diseño**
- **NO optimizar prematuramente**
- **NO cambiar arquitectura**
- **NO modificar UI/UX**

---

## CRITERIOS DE ACEPTACIÓN - SPRINT 1

### Criterios Obligatorios (Para Completar Sprint)
- [ ] 56/56 pruebas aprobadas (100%)
- [ ] Bypass CSRF corregido
- [ ] Rate limiting implementado
- [ ] Rutas protegidas verificadas
- [ ] Nomenclatura unificada
- [ ] 0 regresiones funcionales
- [ ] 0 vulnerabilidades críticas

### Criterios de Calidad
- [ ] Sin errores de compilación
- [ ] Sin warnings en Laravel
- [ ] Performance no degradada
- [ ] Dashboard técnico actualizado

### Criterios de Documentación
- [ ] Checklist de implementación completado
- [ ] Cambios documentados en CHANGELOG
- [ ] Dashboard técnico actualizado
- [ ] Lecciones aprendidas registradas

---

## MÉTRICAS DE ÉXITO

### Antes del Sprint
- Tests: 55/56 (98.2%)
- Vulnerabilidades críticas: 1 (CSRF bypass)
- Rutas inseguras: No verificadas
- Nomenclatura: Inconsistente

### Después del Sprint (Objetivo)
- Tests: 56/56 (100%)
- Vulnerabilidades críticas: 0
- Rutas inseguras: 0
- Nomenclatura: Consistente

---

## PLAN DE EJECUCIÓN

### Día 1: Seguridad Crítica
- Mañana: Corregir bypass CSRF
- Tarde: Resolver test ChatbotAppointmentManageTest
- Validación: Tests deben pasar 56/56

### Día 2: Rutas y Protección
- Mañana: Revisar rutas protegidas
- Tarde: Implementar Rate Limiting
- Validación: Tests de seguridad

### Día 3: Consistencia
- Mañana: Unificar nomenclatura
- Tarde: Verificar consistencia de datos
- Validación: Tests de integridad

### Día 4-5: Buffer y Validación
- Validación completa del sistema
- Corrección de problemas emergentes
- Documentación y actualización de dashboards
- Preparación para Sprint 2

---

## RIESGOS Y MITIGACIÓN

### Riesgo 1: Corrección de CSRF rompe chatbot
- **Mitigación:** Tests exhaustivos de chatbot antes/después
- **Plan B:** Revertir y enfoque alternativo

### Riesgo 2: Unificación de nomenclatura rompe datos
- **Mitigación:** Backup de base de datos, migración de datos
- **Plan B:** Revertir migración, enfoque gradual

### Riesgo 3: Rate limiting afecta usuarios legítimos
- **Mitigación:** Configuración conservadora inicial
- **Plan B:** Ajustar límites basado en monitoreo

---

## REFERENCIAS

- **Baseline:** v0.0-baseline
- **Política Refactorización:** docs/POLITICA_REFACTORIZACION.md
- **Dashboard Técnico:** docs/DASHBOARD_TECNICO.md
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md
- **ADRs:** docs/ADR/

---

## NOTA IMPORTANTE

**Este sprint tiene un alcance muy concreto y limitado. El objetivo es estabilizar el sistema existente, no introducir cambios arquitectónicos ni funcionalidades nuevas. Cualquier desviación de este alcance requiere aprobación explícita del Technical Lead.**

---

**Estado:** ⬜ PENDIENTE DE INICIO
**Próxima Acción:** Esperar aprobación para comenzar Sprint 1
**Dependencias:** FASE 0 completada, baseline congelado, controles técnicos establecidos

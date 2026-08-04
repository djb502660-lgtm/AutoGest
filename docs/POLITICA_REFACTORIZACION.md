# POLÍTICA DE REFACTORIZACIÓN - AutoGest
**Technical Lead Guidelines**
**Fecha:** 2026-08-04
**Versión:** 1.0

---

## PRINCIPIO FUNDAMENTAL

> **No refactorizar código que no se vaya a modificar.**

Esta regla evita introducir riesgos innecesarios. Si una clase funciona correctamente y no forma parte del alcance del sprint actual, se deja intacta.

---

## CRITERIOS PARA REFACTORIZACIÓN

### CUANDO REFACTORIZAR ✅

1. **Código en el alcance del sprint actual**
   - Se va a modificar funcionalidad relacionada
   - Se van a agregar nuevas features relacionadas
   - Se están corrigiendo bugs en el módulo

2. **Deuda técnica crítica**
   - Bloquea desarrollo de nuevas funcionalidades
   - Causa problemas de performance significativos
   - Representa riesgo de seguridad
   - Aprobado por Technical Lead como prioridad

3. **Violación de principios SOLID graves**
   - Impide testing adecuado
   - Crea acoplamiento crítico
   - Dificulta mantenimiento continuo

4. **Duplicación de código significativa**
   - Mismo código en 3+ lugares
   - Impacta mantenibilidad
   - Aprobado por Technical Lead

### CUANDO NO REFACTORIZAR ❌

1. **Código fuera del alcance del sprint**
   - Funciona correctamente
   - No tiene bugs conocidos
   - No bloquea desarrollo actual

2. **Optimizaciones prematuras**
   - Performance aceptable
   - Sin quejas de usuarios
   - Sin métricas que indiquen problema

3. **Preferencias personales**
   - "No me gusta cómo se ve"
   - "Yo lo haría diferente"
   - Sin justificación técnica

4. **Refactorización especulativa**
   - "Podría necesitarlo en el futuro"
   - "Es mejor prepararse"
   - Sin requisito actual

---

## PROCESO DE REFACTORIZACIÓN

### 1. Evaluación Inicial
- [ ] El código está en el alcance del sprint actual?
- [ ] Hay un problema técnico identificado?
- [ ] La refactorización tiene ROI claro?
- [ ] Está aprobado por Technical Lead?

### 2. Análisis de Impacto
- [ ] Identificar todos los archivos afectados
- [ ] Verificar tests existentes
- [ ] Evaluar riesgo de regresión
- [ ] Estimar tiempo de refactorización

### 3. Preparación
- [ ] Crear branch específico para refactorización
- [ ] Asegurar que tests pasan (baseline)
- [ ] Documentar estado actual
- [ ] Preparar plan de reversión

### 4. Ejecución
- [ ] Refactorizar incrementalmente
- [ ] Ejecutar tests después de cada cambio
- [ ] Mantener tests pasando siempre
- [ ] Documentar cambios significativos

### 5. Validación
- [ ] Todos los tests pasan
- [ ] Sin regresiones funcionales
- [ ] Performance mantenida o mejorada
- [ ] Code review completado

### 6. Documentación
- [ ] Actualizar matriz de dependencias
- [ ] Crear/actualizar ADR si es decisión arquitectónica
- [ ] Actualizar baseline si es necesario
- [ ] Documentar lecciones aprendidas

---

## NIVELES DE REFACTORIZACIÓN

### Nivel 1: Refactorización Local (Bajo Riesgo)
- **Alcance:** Archivo individual o función
- **Tiempo:** <1 hora
- **Aprobación:** Auto-aprobación por desarrollador
- **Ejemplos:**
  - Renombrar variables para claridad
  - Extraer método pequeño
  - Simplificar condicional
  - Agregar type hints

### Nivel 2: Refactorización de Módulo (Riesgo Medio)
- **Alcance:** Múltiples archivos en mismo módulo
- **Tiempo:** 1-4 horas
- **Aprobación:** Revisión por peer
- **Ejemplos:**
  - Extraer clase de servicio
  - Reorganizar métodos en controller
  - Consolidar código duplicado
  - Mejorar estructura de modelo

### Nivel 3: Refactorización Arquitectónica (Alto Riesgo)
- **Alcance:** Múltiples módulos o capas
- **Tiempo:** 1-3 días
- **Aprobación:** Technical Lead + ADR
- **Ejemplos:**
  - Implementar Repository Pattern
  - Reestructurar arquitectura de chatbot
  - Cambiar patrón de diseño significativo
  - Modificar estructura de base de datos

---

## REGLAS ESPECÍFICAS POR TIPO DE CÓDIGO

### Controladores
- **Mantener simple si funciona:** No agregar Service Layer si no es necesario
- **Solo si se modifica:** Refactorizar controller que se va a tocar
- **Evitar sobre-ingeniería:** No extraer servicios de trivialidades

### Modelos
- **Solo si hay problema:** No refactorizar relaciones existentes
- **Cambios estructurales:** Requieren ADR
- **Optimizaciones:** Solo con métricas que justifiquen

### Vistas
- **Mínimo cambio:** Solo si afecta UX actual
- **Responsive:** Solo si en alcance del sprint
- **Componentes:** Extraer solo si reutilizable inmediatamente

### Services
- **Consolidar:** Si hay duplicación clara
- **No dividir:** Si la división no agrega valor
- **Simplificar:** Si complejidad es innecesaria

### Migraciones
- **Nunca refactorizar:** Migraciones ejecutadas son inmutables
- **Nueva migración:** Para cambios estructurales
- **Rollback:** Siempre mantener capacidad de rollback

---

## MÉTRICAS DE DECISIÓN

### Antes de Refactorizar
- **Cobertura de tests:** >70% del código a refactorizar
- **Tiempo estimado:** <20% del tiempo del sprint
- **ROI claro:** Beneficio > costo
- **Riesgo aceptable:** Sin bloqueo crítico posible

### Después de Refactorizar
- **Tests:** Mismo número o más tests pasando
- **Performance:** Igual o mejor
- **Complejidad:** Reducida o mantenida
- **Mantenibilidad:** Mejorada

---

## EXCEPCIONES

### Excepciones Requieren Aprobación Especial
1. **Technical Debt Crítico:** Aprobado por Technical Lead
2. **Security Issues:** Aprobado inmediatamente
3. **Performance Blocker:** Con métricas que justifiquen
4. **Client Critical Bug:** Aprobado inmediatamente

### Proceso de Excepción
1. Documentar justificación técnica
2. Presentar a Technical Lead
3. Evaluar riesgo vs beneficio
4. Aprobar/rechazar con rationale
5. Documentar decisión en ADR si aplica

---

## HERRAMIENTAS Y TÉCNICAS

### Herramientas de Soporte
- **Laravel Pint:** Para formateo estándar
- **PHPStan:** Para análisis estático
- **Laravel Debug Bar:** Para análisis de performance
- **PHPUnit:** Para regresión testing

### Técnicas Seguras
- **Refactorización Roja:** Escribir test primero, luego refactorizar
- **Branch por Feature:** Aislamiento de cambios
- **Commits Pequeños:** Reversión fácil
- **Code Review:** Validación por peer

---

## MONITOREO Y SEGUIMIENTO

### Indicadores de Refactorización Exitosa
- Tests mantienen 100% de paso
- Sin incidencias P0/P1 introducidas
- Performance mantenida o mejorada
- Code time reducido en maintenance

### Indicadores de Problema
- Tests fallando después de refactorización
- Regresiones en funcionalidad
- Performance degradada
- Tiempo excedido del estimado

### Acción Correctiva
Si refactorización causa problemas:
1. Revertir inmediatamente
2. Análisis de causa raíz
3. Ajustar enfoque
4. Documentar lección aprendida
5. Reevaluar necesidad

---

## REFERENCIAS

- **ADR-001:** Repository Pattern Decision
- **Baseline:** docs/BASELINE/dependency-matrix.md
- **Checklist:** docs/BASELINE/checklist-implementacion-autogest.md
- **Dashboard:** docs/DASHBOARD_TECNICO.md

---

## APROBACIÓN

**Technical Lead:** AutoGest Project
**Fecha:** 2026-08-04
**Versión:** 1.0
**Estado:** Activo

---

## REVISIONES

| Versión | Fecha | Cambios | Autor |
|---------|-------|--------|-------|
| 1.0 | 2026-08-04 | Política inicial | Technical Lead |

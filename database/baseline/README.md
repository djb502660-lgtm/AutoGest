# BASELINE DE BASE DE DATOS - AutoGest
**Fecha:** 2026-08-04
**Git Tag:** v0.0-baseline
**Laravel Version:** 12.61.0

---

## CONTENIDO

### schema.sql
Esquema completo de la base de datos generado con `php artisan schema:dump`
- Estructura de tablas
- Índices
- Relaciones
- Timestamp de baseline

### migrations-status.txt
Estado de migraciones al momento del baseline
- 34 migraciones ejecutadas
- Todas en estado "Ran"
- Organizadas por batch

### seeders-status.txt
Estado de seeders disponibles
- Seeders identificados
- Estado de ejecución
- Recomendaciones

---

## CÓMO USAR ESTE BASELINE

### Restaurar esquema desde baseline
```bash
# Desde el archivo schema.sql
mysql -u usuario -p base_de_datos < database/baseline/schema.sql

# O usando Laravel
php artisan schema:dump --load
```

### Verificar estado actual vs baseline
```bash
# Comparar migraciones
php artisan migrate:status

# Comparar con baseline
diff database/baseline/migrations-status.txt <(php artisan migrate:status)
```

### Restaurar desde backup completo
Si existe un backup completo de la base de datos:
```bash
mysql -u usuario -p base_de_datos < database/baseline/backup.sql
```

---

## CONTROL DE VERSIONES

Este baseline debe actualizarse:
- Después de cambios estructurales importantes
- Antes de migraciones complejas
- Al finalizar cada sprint mayor
- Antes de releases

---

## REFERENCIAS

- **Git Baseline:** v0.0-baseline
- **Documentación:** docs/BASELINE/
- **Dashboard:** docs/DASHBOARD_AVANCE.md
- **Migraciones:** database/migrations/

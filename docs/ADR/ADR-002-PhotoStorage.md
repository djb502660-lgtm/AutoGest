# ADR-002: Estrategia de Almacenamiento de Fotografías Transversal

## Estado
Propuesta

## Contexto y Problema
El módulo de fotografías actualmente está implementado principalmente para el módulo de mecánicos, pero las fotografías tienen potencial uso en múltiples contextos:
- Mecánicos (fotos de servicio)
- Clientes (fotos de vehículos)
- Asesores (documentación de citas)
- Inventario (fotos de productos)
- Siniestros (fotos de reclamaciones)

Problemas actuales:
- Acoplamiento fuerte al módulo de mecánicos
- Dificultad para reutilizar en otros módulos
- Falta de estrategia de almacenamiento unificada
- Sin política de acceso y permisos transversal

## Decisiones Consideradas

### Opción 1: Mantener fotos específicas por módulo
- **Ventajas:**
  - Simple implementación
  - Cada módulo maneja sus propias fotos
  - Sin dependencias complejas
- **Desventajas:**
  - Código duplicado
  - Difícil mantenimiento
  - Sin política unificada de almacenamiento
  - Problemas de consistencia

### Opción 2: Sistema de fotos transversal con polimorfismo
- **Ventajas:**
  - Un solo sistema para todos los módulos
  - Política de almacenamiento unificada
  - Reutilización de código
  - Fácil expansión a nuevos módulos
  - Permisos centralizados
- **Desventajas:**
  - Mayor complejidad inicial
  - Requiere diseño de relaciones polimórficas
  - Necesita migración de fotos existentes

### Opción 3: Sistema híbrido (core transversal + específicos)
- **Ventajas:**
  - Balance entre flexibilidad y simplicidad
  - Core compartido para funcionalidad común
  - Módulos pueden extender según necesidades
- **Desventajas:**
  - Arquitectura más compleja
  - Difícil definir límites
  - Posible inconsistencia

## Decisión
Implementar un sistema de fotos transversal con relaciones polimórficas, siguiendo esta arquitectura:

### Modelo central Photo
```php
class Photo extends Model {
    // Relación polimórfica
    public function photographable() {
        return $this->morphTo();
    }
    
    // Tipos soportados
    const TYPES = [
        'service_order',
        'vehicle', 
        'appointment',
        'product',
        'claim',
        'inventory_item'
    ];
}
```

### Estructura de relaciones
```
Photo (transversal)
    ↓ (morphTo)
    ├── ServiceOrder (fotos de servicio)
    ├── Vehicle (fotos de vehículo)
    ├── Appointment (fotos de cita)
    ├── Product (fotos de producto)
    ├── Claim (fotos de siniestro)
    └── InventoryItem (fotos de inventario)
```

### Servicios especializados
- `PhotoStorageService`: Manejo de almacenamiento (S3/local)
- `PhotoAccessService`: Control de permisos por tipo
- `PhotoValidationService`: Validación de archivos
- `PhotoCompressionService`: Optimización de imágenes

## Consecuencias

### Positivas
- Arquitectura escalable y reutilizable
- Política de almacenamiento unificada
- Fácil adición de nuevos tipos de fotos
- Permisos centralizados y consistentes
- Mejor organización del código
- Preparado para expansión futura

### Negativas
- Mayor complejidad inicial
- Requiere migración de fotos existentes
- Curva de aprendizaje para el equipo
- Necesita planificación de permisos

### Riesgos
- Complejidad de relaciones polimórficas
- Performance si no se optimiza correctamente
- Dificultad en queries complejas
- Migración de datos existentes puede ser compleja

## Implementación

### Archivos afectados
- `app/Models/Photo.php` (refactorizar para transversal)
- `app/Models/ServicePhoto.php` (deprecar o eliminar)
- `app/Services/PhotoStorageService.php` (nuevo)
- `app/Services/PhotoAccessService.php` (nuevo)
- `database/migrations/` (nueva migración para Photo transversal)
- `app/Policies/PhotoPolicy.php` (nuevo)

### Esfuerzo estimado
- FASE 2 o FASE 3
- 4-5 días de desarrollo
- 2 días de migración de datos
- 1 día de testing

### Dependencias
- Completar FASE 1 (estabilización)
- Definir política de almacenamiento (S3 vs local)
- ADR-001 aprobado (Repository Pattern)

### Política de almacenamiento
```php
class PhotoStorageService {
    public function store(UploadedFile $file, string $type, int $id): Photo {
        // Validación
        $this->validatePhoto($file);
        
        // Compresión
        $compressed = $this->compress($file);
        
        // Almacenamiento (configurable: local/S3)
        $path = $this->storage->put("photos/{$type}/{$id}", $compressed);
        
        // Crear registro
        return Photo::create([
            'path' => $path,
            'photographable_type' => $type,
            'photographable_id' => $id,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }
}
```

### Política de permisos
```php
class PhotoAccessService {
    public function canAccess(User $user, Photo $photo): bool {
        return match($photo->photographable_type) {
            'service_order' => $this->canAccessServiceOrderPhoto($user, $photo),
            'vehicle' => $this->canAccessVehiclePhoto($user, $photo),
            'appointment' => $this->canAccessAppointmentPhoto($user, $photo),
            default => false,
        };
    }
}
```

## Referencias
- [Laravel Polymorphic Relations](https://laravel.com/docs/12.x/eloquent-relationships#polymorphic-relations)
- [Best Practices for File Uploads](https://owasp.org/www-community/vulnerabilities/Unrestricted_File_Upload)
- Baseline actual: docs/BASELINE/dependency-matrix.md

## Fecha
2026-08-04

## Autor
Technical Lead - AutoGest Project

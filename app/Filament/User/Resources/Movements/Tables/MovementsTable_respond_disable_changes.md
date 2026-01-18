# Documentación de Cambios - Deshabilitar Acción de Responder

## Descripción General
Este documento detalla la mejora implementada para deshabilitar la acción de responder en el archivo `MovementsTable.php` después de que se haya realizado la acción de respuesta.

## Cambio Realizado

### Deshabilitar Acción de Responder
- **Problema**: La acción de responder estaba disponible incluso después de que ya se había respondido al documento.
- **Solución**: Se agregaron condiciones para deshabilitar la acción de responder cuando:
  1. El documento ya ha sido respondido (existe un movimiento con acción `RESPUESTA`)
  2. El estado del documento ya es `COMPLETED`

### Código Agregado

```php
->visible(fn(Movement $record) => $record->document->status !== DocumentStatus::COMPLETED)
->disabled(fn(Movement $record) => $record->document->movements()
    ->where('action', MovementAction::RESPUESTA->value)
    ->exists())
```

Estas líneas se agregaron a la definición de la acción `getRespondAction()` para:

1. `visible`: Hacer que la acción esté visible solo si el estado del documento no es `COMPLETED`
2. `disabled`: Deshabilitar la acción si ya existe un movimiento con la acción `RESPUESTA`

## Resultado
- La acción de responder ahora se deshabilita automáticamente después de que se ha respondido al documento
- Se mejora la experiencia del usuario al evitar acciones innecesarias o repetidas
- Se mantiene la integridad del flujo de trabajo del sistema de documentos

## Archivo Modificado
- `/app/Filament/User/Resources/Movements/Tables/MovementsTable.php`

## Validación
La funcionalidad ha sido implementada y el archivo se encuentra completo y funcional.
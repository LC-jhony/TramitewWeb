# Documentación de Corrección - DocumentsTable.php

## Descripción del Error
El archivo `DocumentsTable.php` contenía un error de tipo en la acción `getForwardAction()`:
```
Argument #1 ($record) must be of type App\Models\Movement, App\Models\Document given
```

## Causa del Error
- La acción `getForwardAction()` estaba definida para recibir un parámetro `$record` de tipo `Movement`
- Sin embargo, la tabla `DocumentsTable` muestra registros de tipo `Document`, no `Movement`
- Por lo tanto, cuando se ejecutaba la acción, recibía un `Document` en lugar de un `Movement`

## Solución Aplicada
- Se cambió el tipo del parámetro `$record` de `Movement` a `Document` en la definición de la acción
- Se ajustó la lógica interna para trabajar con el modelo `Document` en lugar de acceder a través de `$record->document`
- Se corrigió la referencia al office del usuario autenticado en lugar del office del registro

## Cambios Realizados

### Antes:
```php
->action(function (Movement $record, array $data) {
    try {
        DB::transaction(function () use ($record, $data) {
            // Aquí se debería llamar a un servicio o método del modelo
            // para encapsular la lógica de negocio
            $document = $record->document;  // Acceso incorrecto

            $document->movements()->create([
                'document_id' => $record->id,
                'origin_office_id' => $data['origin_office_id'] ?? $record->office_id, // Referencia incorrecta
                // ... resto del código
            ]);

            $document->update([
                // ... actualización del documento
            ]);
        });
    }
});
```

### Después:
```php
->action(function (Document $record, array $data) {
    try {
        DB::transaction(function () use ($record, $data) {
            // Aquí se debería llamar a un servicio o método del modelo
            // para encapsular la lógica de negocio

            $record->movements()->create([
                'document_id' => $record->id,
                'origin_office_id' => $data['origin_office_id'] ?? Auth::user()->office_id, // Referencia correcta
                // ... resto del código
            ]);

            $record->update([
                // ... actualización del documento
            ]);
        });
    }
});
```

## Resultado
- El error ha sido corregido
- La acción de derivación ahora funciona correctamente en la tabla de documentos
- El código ahora es coherente con el tipo de modelo que maneja la tabla

## Archivo Modificado
- `/app/Filament/User/Resources/Documents/Tables/DocumentsTable.php`
# Documentación de Corrección - MovementsTable.php

## Descripción del Error
El archivo `MovementsTable.php` contenía referencias a una columna `status` en la tabla `users` que no existe, causando el siguiente error:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'status' in 'WHERE' 
```

## Causa del Error
- El código intentaba filtrar usuarios por una columna `status` que no existe en la tabla `users`
- La tabla `users` no tiene una columna `status` según su migración (`create_users_table.php`)
- Las consultas `User::where('office_id', $get('destination_office_id'))->where('status', true)` fallaban

## Soluciones Aplicadas

### 1. Eliminación de la condición `where('status', true)`
- Se eliminaron todas las instancias de `->where('status', true)` en las consultas de usuarios
- Se mantuvo solo la condición por `office_id` que sí existe

### 2. Actualización de mensajes de error
- Se modificaron los mensajes de validación para reflejar que ya no se verifica el estado del usuario
- Se actualizaron los mensajes para ser precisos sobre las condiciones de validación

## Cambios Realizados

### Antes:
```php
Select::make('destination_user_id')
    ->options(
        fn(callable $get) =>
        $get('destination_office_id')
            ? User::where('office_id', $get('destination_office_id'))
            ->where('status', true)  // ← Esta línea causaba el error
            ->pluck('name', 'id')
            : []
    )

// Y en la validación:
if (!$destinationUser || !$destinationUser->status || $destinationUser->office_id != $validatedData['destination_office_id']) {
    throw ValidationException::withMessages([
        'destination_user_id' => 'El usuario de destino no existe, no está activo o no pertenece a la oficina seleccionada.'
    ]);
}
```

### Después:
```php
Select::make('destination_user_id')
    ->options(
        fn(callable $get) =>
        $get('destination_office_id')
            ? User::where('office_id', $get('destination_office_id'))
            ->pluck('name', 'id')
            : []
    )

// Y en la validación:
if (!$destinationUser || $destinationUser->office_id != $validatedData['destination_office_id']) {
    throw ValidationException::withMessages([
        'destination_user_id' => 'El usuario de destino no existe o no pertenece a la oficina seleccionada.'
    ]);
}
```

## Resultado
- El error de columna desconocida ha sido resuelto
- Las consultas a la tabla `users` ahora son válidas
- El código funciona correctamente sin intentar acceder a columnas inexistentes
- Las acciones de derivación y respuesta ahora funcionan correctamente

## Archivos Modificados
- `/app/Filament/User/Resources/Movements/Tables/MovementsTable.php`
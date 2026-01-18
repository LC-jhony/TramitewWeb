# Documentación de Cambios - MovementsTable.php

## Descripción General
Este documento detalla las mejoras implementadas en el archivo `MovementsTable.php` para mejorar la seguridad, calidad del código y corrección de errores.

## Cambios Realizados

### 1. Correcciones de Errores

#### a) Manejo de Estados de Enum
- **Problema**: El código no manejaba adecuadamente la conversión entre cadenas y objetos enum en las columnas de estado.
- **Solución**: Se implementó una lógica para convertir cadenas a objetos enum cuando sea necesario en los métodos `formatStateUsing`.

```php
->formatStateUsing(function($state) {
    // Convertir el estado a enum si es una cadena
    if (is_string($state) && $state !== '') {
        try {
            $movementAction = MovementAction::from($state);
            return $movementAction->getLabel();
        } catch (\ValueError $e) {
            return $state;
        }
    }
    return $state?->label ?? $state;
})
```

### 2. Mejoras de Seguridad

#### a) Validación de Autorización
- **Problema**: No se verificaba si el usuario tenía permiso para realizar ciertas acciones en los documentos.
- **Solución**: Se agregó lógica de autorización en todas las acciones (derivación, respuesta, rechazo, archivo):

```php
// Ejemplo para la derivación
$user = auth()->user();
$document = $record->document;

$canForward = $user->office_id == $document->id_office_destination ||
              $user->office_id == $document->area_origen_id ||
              $user->hasRole('admin');

if (!$canForward) {
    throw ValidationException::withMessages([
        'authorization' => 'No tiene permiso para derivar este documento.'
    ]);
}
```

#### b) Validación de Datos de Entrada
- **Problema**: No se validaban adecuadamente los datos recibidos del formulario.
- **Solución**: Se implementó validación de datos de entrada en todas las acciones:

```php
$validatedData = [
    'destination_office_id' => $data['destination_office_id'] ?? null,
    'destination_user_id' => $data['destination_user_id'] ?? null,
    'indication' => $data['indication'] ?? null,
    'observation' => $data['observation'] ?? null,
];

// Validación de existencia y estado de oficinas y usuarios
$destinationOffice = Office::find($validatedData['destination_office_id']);
if (!$destinationOffice || !$destinationOffice->status) {
    throw ValidationException::withMessages([
        'destination_office_id' => 'La oficina de destino no existe o no está activa.'
    ]);
}
```

#### c) Validación de Tipos MIME en Subida de Archivos
- **Problema**: No se validaban adecuadamente los tipos MIME de los archivos subidos.
- **Solución**: Se agregó validación de tipos MIME en la acción de respuesta:

```php
$mimeType = Storage::mimeType($filePath);
$allowedMimes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
];

if (!in_array($mimeType, $allowedMimes)) {
    throw ValidationException::withMessages([
        'response_document' => 'Tipo de archivo no permitido: ' . $mimeType
    ]);
}
```

### 3. Mejoras de Calidad de Código

#### a) Manejo de Excepciones
- **Antes**: Solo se capturaban excepciones genéricas.
- **Después**: Se implementó manejo específico de `ValidationException` para proporcionar mensajes de error más claros al usuario.

#### b) Uso de Validated Data
- **Antes**: Se usaban directamente los datos del formulario sin validación previa.
- **Después**: Se crea un array de datos validados antes de usarlos en la lógica de negocio.

#### c) Mejora de Mensajes de Error
- Se mejoraron los mensajes de notificación para proporcionar retroalimentación más útil al usuario.

## Acciones Específicas Actualizadas

### 1. Derivación (`getForwardAction`)
- Validación de autorización
- Validación de existencia y estado de oficinas y usuarios
- Validación de datos de entrada
- Manejo mejorado de excepciones

### 2. Respuesta (`getRespondAction`)
- Validación de autorización
- Validación de existencia y estado de oficinas y usuarios
- Validación de campos obligatorios
- Validación de tipos MIME en archivos subidos
- Manejo mejorado de excepciones

### 3. Rechazo (`getRejectAction`)
- Validación de autorización
- Validación de campos obligatorios
- Manejo mejorado de excepciones

### 4. Archivo (`getArchiveAction`)
- Validación de autorización (requiere rol admin o ser el usuario propietario)
- Manejo mejorado de excepciones

## Consideraciones Adicionales

### Roles de Usuario
El código asume la existencia de un sistema de roles de usuario con al menos un rol 'admin'. Si este sistema no existe, deberá implementarse para que funcionen correctamente las validaciones de autorización.

### Relaciones de Modelo
Se asume que los modelos tienen las relaciones definidas correctamente para acceder a propiedades como `document`, `originOffice`, etc.

## Respaldo
Se ha creado un archivo de respaldo del código original: `MovementsTable.php.backup`

## Resultado Final
El código ahora es más seguro, robusto y mantiene mejores prácticas de desarrollo, reduciendo significativamente los riesgos de seguridad y mejorando la calidad general del código.
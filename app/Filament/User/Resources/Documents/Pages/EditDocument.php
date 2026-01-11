<?php

namespace App\Filament\User\Resources\Documents\Pages;

use App\Filament\User\Resources\Documents\DocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Guardar los archivos para procesarlos después
        $filesData = $data['files'] ?? [];
        unset($data['files']);
        
        // Guardar los archivos en el estado para usarlos después
        $this->cacheFilesForProcessing($filesData);
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Procesar los archivos después de actualizar el documento
        $cachedFiles = $this->getCachedFiles();
        
        if (!empty($cachedFiles)) {
            // Obtener los archivos actuales para comparar
            $currentFilePaths = $this->record->files->pluck('path')->toArray();
            
            // Procesar nuevos archivos
            foreach ($cachedFiles as $file) {
                if ($file instanceof TemporaryUploadedFile) {
                    // Nuevo archivo subido
                    $originalName = $file->getClientOriginalName();
                    $path = $file->store('documents', 'public');
                    
                    $this->record->files()->create([
                        'filename' => $originalName,
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => auth()->id(),
                    ]);
                } elseif (is_string($file)) {
                    // Verificar si el archivo ya existe en la lista actual
                    if (!in_array($file, $currentFilePaths)) {
                        // Es un archivo nuevo (posiblemente de edición previa o recién subido)
                        $fileName = basename($file);
                        $newPath = str_replace(basename($file), uniqid() . '_' . $fileName, $file);
                        
                        if (Storage::exists($file)) {
                            Storage::copy($file, $newPath);
                            
                            $this->record->files()->create([
                                'filename' => $fileName,
                                'path' => $newPath,
                                'mime_type' => Storage::mimeType($newPath),
                                'size' => Storage::size($newPath),
                                'uploaded_by' => auth()->id(),
                            ]);
                        }
                    }
                }
            }
            
            // Eliminar archivos que ya no están en el formulario
            $formFilePaths = $cachedFiles;
            $filesToDelete = collect($currentFilePaths)->diff($formFilePaths);
            
            foreach ($filesToDelete as $filePath) {
                $documentFile = $this->record->files()->where('path', $filePath)->first();
                if ($documentFile) {
                    // Eliminar el archivo físico
                    if (Storage::exists($documentFile->path)) {
                        Storage::delete($documentFile->path);
                    }
                    // Eliminar el registro de la base de datos
                    $documentFile->delete();
                }
            }
        } else {
            // Si no hay archivos en el formulario, eliminar todos los archivos existentes
            foreach ($this->record->files as $documentFile) {
                // Eliminar el archivo físico
                if (Storage::exists($documentFile->path)) {
                    Storage::delete($documentFile->path);
                }
                // Eliminar el registro de la base de datos
                $documentFile->delete();
            }
        }
    }

    private function cacheFilesForProcessing($files): void
    {
        session()->put('document_files_' . $this->record?->getKey(), $files);
    }

    private function getCachedFiles()
    {
        return session()->get('document_files_' . $this->record?->getKey());
    }
}
<?php

namespace App\Filament\User\Resources\Documents\Pages;

use App\Filament\User\Resources\Documents\DocumentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Guardar los archivos para procesarlos después
        $filesData = $data['files'] ?? [];
        unset($data['files']);
        
        // Guardar los archivos en el estado para usarlos después
        $this->cacheFilesForProcessing($filesData);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Procesar los archivos después de crear el documento
        $cachedFiles = $this->getCachedFiles();
        
        if (!empty($cachedFiles)) {
            foreach ($cachedFiles as $file) {
                if ($file instanceof TemporaryUploadedFile) {
                    // Archivo temporal subido recientemente
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
                    // Archivo ya almacenado, posiblemente de edición previa
                    // En este caso, simplemente copiarlo al nuevo documento
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
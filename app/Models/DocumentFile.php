<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentFile extends Model
{
    protected $fillable = [
        'document_id',
        'filename',
        'path',
        'mime_type',
        'size',
        'uploaded_by',
    ];
    public function document(): BelongsTo
    {
        return $this->belongsTo(
            related: Document::class,
            foreignKey: 'document_id',
        );
    }
}

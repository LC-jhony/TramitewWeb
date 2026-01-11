<?php

namespace App\Models;

use App\Enum\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'customer_id',
        'document_number',
        'case_number',
        'subject',
        'origen',
        'document_type_id',
        'area_origen_id',
        'gestion_id',
        'user_id',
        'folio',
        'reception_date',
        'response_deadline',
        'condition',
        'status',
        'priority_id',
        'priority_id',
        'id_office_destination',
    ];

    protected function casts(): array
    {
        return [
            'status' => DocumentStatus::class,
            'reception_date' => 'date',
            'response_deadline' => 'date',
        ];
    }
    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function officeOrigen(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'area_origen_id');
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(Administration::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(
            related: DocumentFile::class,
            foreignKey: 'document_id',
        );
    }
}

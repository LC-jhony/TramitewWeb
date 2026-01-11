<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movement extends Model
{
    protected $fillable = [
        'document_id',
        'origin_office_id',
        'origin_user_id',
        'destination_office_id',
        'destination_user_id',
        'action',
        'indication',
        'observation',
        'receipt_date',
        'status',
    ];
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
    public function originOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'origin_office_id');
    }
    public function destinationOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'destination_office_id');
    }
    public function originUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'origin_user_id');
    }
    public function destinationUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destination_user_id');
    }
}

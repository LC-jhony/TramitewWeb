<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory;
    protected $fillable = [
        'representation',
        'full_name',
        'first_name',
        'last_name',
        'dni',
        'phone',
        'email',
        'address',
        'ruc',
        'company',
    ];
}

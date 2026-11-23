<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoKiosco extends Model
{
    protected $table = 'productos_kiosco';

    protected $fillable = [
        'categoria',
        'nombre',
        'precio',
        'stock',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock'  => 'integer',
        'activo' => 'boolean',
    ];
}
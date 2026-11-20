<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    protected $table = 'insumos';

    protected $fillable = [
        'turno',
        'categoria', // 👈 Indispensable para que guarde lo que elijas
        'nombre',
        'cantidad',
        'precio',
        'unidad',
        'stock_minimo',
    ];
}
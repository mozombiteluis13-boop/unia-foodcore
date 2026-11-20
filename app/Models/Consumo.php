<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consumo extends Model
{
    use HasFactory;

    protected $fillable = [
        'estudiante_id',
        'pedido',
        'precio',        // 👈 ¡Añadido aquí para habilitar la asignación masiva del precio!
        'fecha_consumo',
        'estado',        // Si utilizas esta columna
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }
}
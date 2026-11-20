<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $fillable = [
        'dni',
        'nombres',
        'apellidos',
        'carrera_id',
        'activo',
        'saldo',
    ];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }
}
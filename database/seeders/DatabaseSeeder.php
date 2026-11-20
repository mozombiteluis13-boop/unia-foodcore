<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carrera;
use App\Models\Estudiante;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear una carrera de prueba
        $carrera = Carrera::create([
            'nombre' => 'Ingeniería de Software con IA',
            'codigo' => 'ISAI-01'
        ]);

        // Crear un estudiante de prueba
        Estudiante::create([
            'dni' => '71234567',
            'nombres' => 'Luis Fernando',
            'apellidos' => 'Mozombite',
            'carrera_id' => $carrera->id,
            'activo' => true
        ]);
    }
}
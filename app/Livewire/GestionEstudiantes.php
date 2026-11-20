<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Estudiante;

class GestionEstudiantes extends Component
{
    public $busqueda = ''; // Guardará el DNI a buscar

    public function cambiarEstado($id)
    {
        $estudiante = Estudiante::find($id);
        if ($estudiante) {
            $estudiante->activo = !$estudiante->activo;
            $estudiante->save();
        }
    }

    public function eliminar($id)
    {
        Estudiante::destroy($id);
    }

    public function render()
    {
        // Filtra por DNI si el usuario escribe algo en el buscador
        $estudiantes = Estudiante::with('carrera')
            ->where('dni', 'like', '%' . $this->busqueda . '%')
            ->latest()
            ->get();

        return view('livewire.gestion-estudiantes', [
            'estudiantes' => $estudiantes
        ]);
    }
}
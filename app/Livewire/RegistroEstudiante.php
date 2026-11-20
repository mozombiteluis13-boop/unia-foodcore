<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Estudiante;
use App\Models\Carrera;
use Illuminate\Support\Facades\Log;

class RegistroEstudiante extends Component
{
    public $dni;
    public $nombres;
    public $carrera_id;
    public $mensajeExito = ''; 

    // 1. Declaramos la variable pública para que Livewire la reconozca en la vista
    public $carreras = [];

    protected $rules = [
        'dni'        => 'required|digits:8|unique:estudiantes,dni',
        'nombres'    => 'required|string|max:255',
        'carrera_id' => 'required',
    ];

    // 2. Este método se ejecuta automáticamente al cargar el componente
    public function mount()
    {
        $this->carreras = Carrera::all();
    }

    public function registrar()
    {
        $this->validate();

        try {
            $partes = explode(' ', trim($this->nombres), 2);
            $nombresExtraidos = $partes[0] ?? trim($this->nombres);
            $apellidosExtraidos = $partes[1] ?? 'Sin Apellidos';

            Estudiante::create([
                'dni'        => trim($this->dni),
                'nombres'    => $nombresExtraidos,
                'apellidos'  => $apellidosExtraidos,
                'carrera_id' => (int) $this->carrera_id,
                'activo'     => 1,
            ]);

            $this->reset(['dni', 'nombres', 'carrera_id']);
            $this->mensajeExito = '¡Te registraste correctamente! Ya puedes pasar al comedor.';

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            session()->flash('error', 'Error en BD: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.registro-estudiante');
    }
}
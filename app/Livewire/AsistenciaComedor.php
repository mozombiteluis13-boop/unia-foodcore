<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Estudiante;
use App\Models\Consumo;
use Carbon\Carbon;

class AsistenciaComedor extends Component
{
    public $dni = '';
    public $mensaje = '';
    public $tipoMensaje = ''; // success o error

    public function registrarAsistencia()
    {
        $this->validate([
            'dni' => 'required|string|size:8',
        ]);

        $estudiante = Estudiante::where('dni', $this->dni)->first();

        // 1. Validar que exista el estudiante
        if (!$estudiante) {
            $this->mensaje = 'Estudiante no encontrado o DNI incorrecto.';
            $this->tipoMensaje = 'error';
            return;
        }

        // 2. Validar que esté activo
        if (!$estudiante->activo) {
            $this->mensaje = 'El estudiante está inactivo en el sistema.';
            $this->tipoMensaje = 'error';
            return;
        }

        // 3. Validar si ya marcó asistencia HOY
        $yaConsumioHoy = Consumo::where('estudiante_id', $estudiante->id)
            ->whereDate('fecha_consumo', Carbon::today())
            ->exists();

        if ($yaConsumioHoy) {
            $this->mensaje = "¡Atención! {$estudiante->nombres} {$estudiante->apellidos} ya registró su asistencia hoy.";
            $this->tipoMensaje = 'error';
            $this->dni = '';
            return;
        }

        // 4. Registrar el consumo si todo está correcto
        Consumo::create([
            'estudiante_id' => $estudiante->id,
            'fecha_consumo' => now(),
        ]);

        $this->mensaje = "¡Asistencia registrada con éxito para: {$estudiante->nombres} {$estudiante->apellidos}!";
        $this->tipoMensaje = 'success';
        $this->dni = ''; // Limpiar el input
    }

    public function render()
    {
        // Traer los consumos registrados hoy con los datos del estudiante
        $consumosHoy = Consumo::with('estudiante')
            ->whereDate('fecha_consumo', Carbon::today())
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.asistencia-comedor', [
            'consumosHoy' => $consumosHoy
        ]);
    }
}
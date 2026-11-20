<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Consumo;
use App\Models\Estudiante;
use App\Models\Insumo;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $comedorAbierto;

    // Variables del modal de recarga (igual que antes)
    public $dniRecarga = '';
    public $nombreEstudianteRecarga = '';
    public $abrirModalRecarga = false;

    public function mount()
    {
        $this->comedorAbierto = cache('comedor_abierto', true);
    }

    public function toggleEstadoComedor()
    {
        $this->comedorAbierto = !$this->comedorAbierto;
        cache(['comedor_abierto' => $this->comedorAbierto]);
    }

    public function updatedDniRecarga($value)
    {
        $dniLimpio = trim((string) $value);

        if (strlen($dniLimpio) === 8) {
            $estudiante = Estudiante::where('dni', $dniLimpio)->first();

            if ($estudiante) {
                $this->nombreEstudianteRecarga = $estudiante->nombres . ' ' . $estudiante->apellidos;
            } else {
                $this->nombreEstudianteRecarga = '❌ Estudiante no encontrado';
            }
        } else {
            $this->nombreEstudianteRecarga = '';
        }
    }

    public function abrirModal()
    {
        $this->abrirModalRecarga = true;
    }

    public function cerrarModal()
    {
        $this->abrirModalRecarga = false;
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'almuerzosHoy'        => Consumo::whereDate('created_at', Carbon::today())->count(),
            'estudiantesActivos'  => Estudiante::where('activo', true)->count(),
            'totalInsumos'        => Insumo::count(),
            'menuDelDia'          => Insumo::latest()->get(),
            'ultimasAsistencias'  => Consumo::whereDate('created_at', Carbon::today())
                                        ->latest()
                                        ->take(10)
                                        ->get(),
        ]);
    }
}   
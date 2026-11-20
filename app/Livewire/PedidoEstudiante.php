<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Estudiante;
use App\Models\Insumo;
use App\Models\Consumo;
use Carbon\Carbon;

class PedidoEstudiante extends Component
{
    public $dni = '';
    public $seleccionados = []; 
    public $saldoActual = null;

    public $nombreEstudiante = '';

    private function turnoPermitidoParaPedir(?string $horaActual = null): ?string
    {
        $hora =$horaActual ?? Carbon::now('America/Lima')->format('H:i');

        // Desayuno: pedidos permitidos hasta las 09:40 AM (cierra turno a las 10:00 AM)
        if ($hora >= '06:00' &&$hora <= '09:40') {
            return 'Desayuno';
        }

        // Almuerzo: pedidos permitidos hasta las 03:40 PM / 15:40 (cierra turno a las 16:00)
        if ($hora >= '11:00' &&$hora <= '15:40') {
            return 'Almuerzo';
        }

        return null;
    }

    private function turnoVisible(?string $horaActual = null): ?string
    {
        $hora =$horaActual ?? Carbon::now('America/Lima')->format('H:i');

        // Muestra menú de Desayuno hasta las 10:00 AM
        if ($hora >= '06:00' &&$hora < '10:00') {
            return 'Desayuno';
        }

        // Muestra menú de Almuerzo hasta las 04:00 PM (16:00)
        if ($hora >= '11:00' &&$hora <= '16:00') {
            return 'Almuerzo';
        }

        return null;
    }

    public function agregarItem($insumoId)
    {
        if (!$this->turnoPermitidoParaPedir()) {
            session()->flash('error', '⏰ El tiempo para realizar pedidos de este turno ya cerró (desayuno hasta las 09:40 AM y almuerzo hasta las 03:40 PM).');
            return;
        }

        if (empty(trim($this->dni))) {
            session()->flash('error', 'Por favor, ingresa tu DNI primero.');
            return;
        }

        $estudiante = Estudiante::where('dni', trim($this->dni))->first();
        if (!$estudiante) {
            session()->flash('error', 'El DNI ingresado no está registrado.');
            return;
        }

        $item = Insumo::find($insumoId);
        if ($item && $item->cantidad > 0) {$this->seleccionados[] = [
                'id'     => $item->id,
                'nombre' => $item->nombre,
                'unidad' => $item->unidad ?? 'General',
                'precio' => $item->precio ?? 0  
            ];
            session()->flash('mensaje', '¡' . $item->nombre . ' agregado a tu selección!');
        } else {
            session()->flash('error', 'No hay stock disponible de este producto.');
        }
    }

    public function quitarItem($index)
    {
        unset($this->seleccionados[$index]);
        $this->seleccionados = array_values($this->seleccionados);
    }

    public function enviarPedidoFinal()
    {
        if (!$this->turnoPermitidoParaPedir()) {
            session()->flash('error', '⏰ Ya pasó la hora límite para enviar pedidos (hasta las 09:40 AM para desayuno y 03:40 PM para almuerzo).');
            return;
        }

        if (empty(trim($this->dni))) {
            session()->flash('error', 'Ingresa tu DNI para enviar el pedido.');
            return;
        }

        if (empty($this->seleccionados)) {
            session()->flash('error', 'Debes seleccionar al menos un plato o bebida.');
            return;
        }

        $estudiante = Estudiante::where('dni', trim($this->dni))->first();
        if (!$estudiante) {
            session()->flash('error', 'Estudiante no encontrado.');
            return;
        }

        // 🔴 VALIDACIÓN DE ESTADO: Bloquea si la cuenta está inactiva
        if (!$estudiante->activo) {
            session()->flash('error', '⚠️ Tu cuenta se encuentra inactiva. Acércate a la administración del comedor.');
            return;
        }

        // 1. Calcular el costo total
        $totalPagar = 0;
        foreach ($this->seleccionados as$sel) {
            $totalPagar +=$sel['precio'] ?? 0;
        }

        // 2. Verificar saldo
        if ($estudiante->saldo <$totalPagar) {
            session()->flash('error', 'Saldo insuficiente. Tu saldo actual es S/. ' . number_format($estudiante->saldo, 2) . ' y el total de tu pedido es S/. ' . number_format($totalPagar, 2));
            return;
        }

        // 3. Descontar saldo de forma correcta (con el signo menos '-')
        $estudiante->saldo = $estudiante->saldo -$totalPagar;
        $estudiante->save();

        // 4. Unir nombres
        $nombresItems = array_column($this->seleccionados, 'nombre');
        $pedidoConcatenado = implode(' + ',$nombresItems);

        // 5. Descontar stock
        foreach ($this->seleccionados as$sel) {
            $insumo = Insumo::find($sel['id']);
            if ($insumo) {$insumo->decrement('cantidad');
            }
        }

        // 6. Guardar en consumos con precio
        Consumo::create([
            'estudiante_id' => $estudiante->id,
            'pedido'        => $pedidoConcatenado,
            'precio'        => $totalPagar,
            'fecha_consumo' => now(),
        ]);

        // 🔊 Disparamos el evento para que active la voz en el monitor de cocina
        $this->dispatch('reproducir-voz');

        // Limpiar estado
        $this->reset(['seleccionados', 'dni', 'saldoActual']);
        session()->flash('exito', '¡Tu pedido fue enviado con éxito! Se descontó S/. ' . number_format($totalPagar, 2) . ' de tu saldo.');
    }

    // Búsqueda automática del saldo en el pedido normal
    public function updatedDni($value)
    {
        $dniLimpio = trim($value);

        // Solo busca si tiene los 8 dígitos del DNI
        if (strlen($dniLimpio) === 8) {
            $estudiante = Estudiante::where('dni',$dniLimpio)->first();

            if ($estudiante) {
                $this->saldoActual =$estudiante->saldo;
                $this->nombreEstudiante = $estudiante->nombres . ' ' .$estudiante->apellidos;
            } else {
                $this->saldoActual = null;
                $this->nombreEstudiante = 'Estudiante no encontrado';
            }
        } else {
            $this->saldoActual = null;
            $this->nombreEstudiante = '';
        }
    }

    public function render()
    {
        $turnoVisible =$this->turnoVisible();
        $puedePedir =$this->turnoPermitidoParaPedir() !== null;
        $insumos = collect();

        if ($turnoVisible) {
            $insumos = Insumo::where('cantidad', '>', 0) ->where('turno',$turnoVisible)
                            ->get();
        }

        return view('livewire.pedido-estudiante', [
            'insumos'    => $insumos,
            'puedePedir' => $puedePedir,
        ]);
    }
}
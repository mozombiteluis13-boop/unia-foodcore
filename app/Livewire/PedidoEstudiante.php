<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Estudiante;
use App\Models\Insumo;
use App\Models\Consumo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PedidoEstudiante extends Component
{
    public $dni = '';
    public $seleccionados = [];
    public $saldoActual = null;
    public $nombreEstudiante = '';
    public $seccionVista = 'menu'; // 'menu' o 'extras'

    private function turnoPermitidoParaPedir(?string $horaActual = null): ?string
    {
        $hora = $horaActual ?? Carbon::now('America/Lima')->format('H:i');

        // Desayuno: pedidos permitidos hasta las 09:40 AM (cierra turno a las 10:00 AM)
        if ($hora >= '06:00' && $hora <= '09:40') {
            return 'Desayuno';
        }

        // Almuerzo: pedidos permitidos hasta las 03:40 PM / 15:40 (cierra turno a las 16:00)
        if ($hora >= '11:00' && $hora <= '15:40') {
            return 'Almuerzo';
        }

        return null;
    }

    private function turnoVisible(?string $horaActual = null): ?string
    {
        $hora = $horaActual ?? Carbon::now('America/Lima')->format('H:i');

        // Muestra menú de Desayuno hasta las 10:00 AM
        if ($hora >= '06:00' && $hora < '10:00') {
            return 'Desayuno';
        }

        // Muestra menú de Almuerzo hasta las 04:00 PM (16:00)
        if ($hora >= '11:00' && $hora <= '16:00') {
            return 'Almuerzo';
        }

        return null;
    }

    public function cambiarSeccionVista(string $seccion)
    {
        if (in_array($seccion, ['menu', 'extras'], true)) {
            $this->seccionVista = $seccion;
        }
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

        if (!$estudiante->activo) {
            session()->flash('error', '⚠️ Tu cuenta se encuentra inactiva. Acércate a la administración del comedor.');
            return;
        }

        $item = Insumo::find($insumoId);
        $turnoActual = $this->turnoVisible();

        if (!$item || $item->cantidad <= 0 || ($turnoActual && $item->turno !== $turnoActual)) {
            session()->flash('error', 'No hay stock disponible o el ítem no pertenece al turno actual.');
            return;
        }

        $esExtraItem = (bool) ($item->es_extra ?? false);
        $precioReal = (float) ($item->precio ?? 0);

        // Si es extra, sí o sí debe tener precio mayor a 0
        if ($esExtraItem && $precioReal <= 0) {
            session()->flash('error', 'Este ítem extra no tiene un precio válido configurado.');
            return;
        }

        // Evita exceder el stock real contando cuántas veces ya lo puso en su carrito
        $vecesEnCarrito = collect($this->seleccionados)->where('id', $item->id)->count();
        if (($vecesEnCarrito + 1) > $item->cantidad) {
            session()->flash('error', 'Ya alcanzaste el stock disponible de ' . $item->nombre . '.');
            return;
        }

        $this->seleccionados[] = [
            'id'        => $item->id,
            'nombre'    => $item->nombre,
            'categoria' => $item->categoria ?? 'General',
            'unidad'    => $item->unidad ?? 'General',
            'es_extra'  => $esExtraItem,
            'precio'    => $precioReal,
        ];

        session()->flash('mensaje', '¡' . $item->nombre . ' agregado a tu selección!');
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

        $resultadoError = null;
        $totalCobrado = 0;

        DB::transaction(function () use (&$resultadoError, &$totalCobrado) {
            $estudiante = Estudiante::where('dni', trim($this->dni))->lockForUpdate()->first();
            if (!$estudiante) {
                $resultadoError = 'Estudiante no encontrado.';
                return;
            }

            if (!$estudiante->activo) {
                $resultadoError = '⚠️ Tu cuenta se encuentra inactiva. Acércate a la administración del comedor.';
                return;
            }

            // Agrupamos cantidades por ID de insumo y leemos el precio real desde la BD
            $cantidadesPorId = [];
            foreach ($this->seleccionados as $sel) {
                if (!empty($sel['id'])) {
                    $cantidadesPorId[$sel['id']] = ($cantidadesPorId[$sel['id']] ?? 0) + 1;
                }
            }

            if (empty($cantidadesPorId)) {
                $resultadoError = 'No se encontraron ítems válidos en tu pedido.';
                return;
            }

            $insumosDb = Insumo::whereIn('id', array_keys($cantidadesPorId))->lockForUpdate()->get()->keyBy('id');

            $totalCalculado = 0;
            $nombresItems = [];

            foreach ($cantidadesPorId as $insumoId => $cantSolicitada) {
                $insumo = $insumosDb->get($insumoId);

                if (!$insumo) {
                    $resultadoError = 'Uno de los ítems seleccionados ya no existe.';
                    return;
                }

                if ($insumo->cantidad < $cantSolicitada) {
                    $resultadoError = 'No hay suficiente stock para "' . $insumo->nombre . '" (disponibles: ' . $insumo->cantidad . ').';
                    return;
                }

                $esExtraItem = (bool) ($insumo->es_extra ?? false);
                $precioUnitario = (float) ($insumo->precio ?? 0);

                // Blindaje: un ítem extra siempre debe cobrar su precio real > 0
                if ($esExtraItem && $precioUnitario <= 0) {
                    $resultadoError = 'El extra "' . $insumo->nombre . '" no tiene precio asignado y no puede procesarse sin cobro.';
                    return;
                }

                $totalCalculado += ($precioUnitario * $cantSolicitada);

                for ($i = 0; $i < $cantSolicitada; $i++) {
                    $nombresItems[] = $esExtraItem ? ($insumo->nombre . ' (EXTRA)') : $insumo->nombre;
                }
            }

            if ((float) $estudiante->saldo < $totalCalculado) {
                $resultadoError = 'Saldo insuficiente. Tu saldo actual es S/. ' . number_format($estudiante->saldo, 2) . ' y el total de tu pedido es S/. ' . number_format($totalCalculado, 2);
                return;
            }

            // Descontar saldo y stock de forma segura
            $estudiante->saldo = (float) $estudiante->saldo - $totalCalculado;
            $estudiante->save();

            foreach ($cantidadesPorId as $insumoId => $cantSolicitada) {
                $insumo = $insumosDb->get($insumoId);
                $insumo->decrement('cantidad', $cantSolicitada);
            }

            Consumo::create([
                'estudiante_id' => $estudiante->id,
                'pedido'        => implode(' + ', $nombresItems),
                'precio'        => $totalCalculado,
                'fecha_consumo' => now(),
            ]);

            $totalCobrado = $totalCalculado;
            $this->saldoActual = $estudiante->saldo;
        });

        if ($resultadoError) {
            session()->flash('error', $resultadoError);
            return;
        }

        $this->dispatch('reproducir-voz');
        $this->reset(['seleccionados', 'dni', 'saldoActual', 'nombreEstudiante']);
        session()->flash('exito', '¡Tu pedido fue enviado con éxito! Se descontó S/. ' . number_format($totalCobrado, 2) . ' de tu saldo.');
    }

    public function updatedDni($value)
    {
        $dniLimpio = trim($value);

        if (strlen($dniLimpio) === 8) {
            $estudiante = Estudiante::where('dni', $dniLimpio)->first();

            if ($estudiante) {
                $this->saldoActual = $estudiante->saldo;
                $this->nombreEstudiante = $estudiante->nombres . ' ' . $estudiante->apellidos;
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
        $turnoVisible = $this->turnoVisible();
        $puedePedir = $this->turnoPermitidoParaPedir() !== null;
        $insumos = collect();

        if ($turnoVisible) {
            if ($turnoVisible !== 'Almuerzo' && $this->seccionVista === 'extras') {
                $this->seccionVista = 'menu';
            }

            $query = Insumo::where('cantidad', '>', 0)
                ->where('turno', $turnoVisible);

            if ($turnoVisible === 'Almuerzo') {
                $query->where('es_extra', $this->seccionVista === 'extras');
            } else {
                $query->where(function ($q) {
                    $q->where('es_extra', false)->orWhereNull('es_extra');
                });
            }

            $insumos = $query->get();
        }

        return view('livewire.pedido-estudiante', [
            'insumos'      => $insumos,
            'turnoVisible' => $turnoVisible,
            'puedePedir'   => $puedePedir,
        ]);
    }
}
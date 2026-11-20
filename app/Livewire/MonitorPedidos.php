<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Consumo;
use App\Models\Estudiante;
use App\Models\Insumo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonitorPedidos extends Component
{
    public $ultimoPedidoId = 0;

    public function mount()
    {
        $this->ultimoPedidoId = Consumo::max('id') ?? 0;
        $this->procesarDevolucionesAutomaticas();
    }

    public function verificarNuevosPedidos()
    {
        $this->procesarDevolucionesAutomaticas();

        $maxIdActual = Consumo::max('id') ?? 0;

        if ($maxIdActual > $this->ultimoPedidoId) {
            $this->ultimoPedidoId = $maxIdActual;
            $this->dispatch('nuevo-pedido-cocina');
        }
    }

    public function procesarDevolucionesAutomaticas()
    {
        $ahora = Carbon::now('America/Lima');
        $hoy = $ahora->copy()->startOfDay();
        $horaActual = $ahora->format('H:i');

        // 1. Devolver cualquier pedido pendiente de días anteriores
        $pedidosVencidos = Consumo::where(function ($query) {
                $query->whereNull('estado')
                      ->orWhereNotIn('estado', ['atendido', 'cancelado']);
            })
            ->whereDate('created_at', '<', $hoy)
            ->get();

        // 2. DESAYUNO: cierra a las 10:00 AM ('10:00')
        if ($horaActual >= '10:00') {
            $finDesayuno = $hoy->copy()->setTime(10, 0, 0);
            $vencidosDesayuno = Consumo::where(function ($query) {
                    $query->whereNull('estado')
                          ->orWhereNotIn('estado', ['atendido', 'cancelado']);
                })
                ->whereDate('created_at', $hoy->toDateString())
                ->where('created_at', '<', $finDesayuno->toDateTimeString())
                ->get();

            $pedidosVencidos = $pedidosVencidos->merge($vencidosDesayuno);
        }

        // 3. ALMUERZO: hasta las 16:00 (cierra después de las 16:00)
        if ($horaActual > '16:00') {
            $finAlmuerzo = $hoy->copy()->setTime(16, 0, 59);
            $vencidosAlmuerzo = Consumo::where(function ($query) {
                    $query->whereNull('estado')
                          ->orWhereNotIn('estado', ['atendido', 'cancelado']);
                })
                ->whereDate('created_at', $hoy->toDateString())
                ->where('created_at', '<=', $finAlmuerzo->toDateTimeString())
                ->get();

            $pedidosVencidos = $pedidosVencidos->merge($vencidosAlmuerzo);
        }

        foreach ($pedidosVencidos->unique('id') as $pedido) {
            DB::transaction(function () use ($pedido) {
                $orden = Consumo::where('id', $pedido->id)->lockForUpdate()->first();

                if ($orden && !in_array($orden->estado, ['atendido', 'cancelado'])) {
                    $this->aplicarReembolsoYCancelacion($orden);
                }
            });
        }
    }

    private function aplicarReembolsoYCancelacion(Consumo $pedido)
    {
        // 1. Devolver el saldo al estudiante usando estudiante_id
        $estudiante = Estudiante::find($pedido->estudiante_id);
        if ($estudiante) {
            $estudiante->increment('saldo', $pedido->precio);
        }

        // 2. Devolver el stock a cada insumo guardado en 'pedido' (separados por ' + ')
        if (!empty($pedido->pedido)) {
            $nombresItems = explode(' + ', $pedido->pedido);
            foreach ($nombresItems as $nombre) {
                $insumo = Insumo::where('nombre', trim($nombre))->first();
                if ($insumo) {
                    $insumo->increment('cantidad');
                }
            }
        }

        // 3. Cambiar el estado a cancelado
        $pedido->update(['estado' => 'cancelado']);
    }

    public function render()
    {
        return view('livewire.monitor-pedidos', [
            'pedidos_dashboard' => Consumo::with(['estudiante'])
                ->where(function ($query) {
                    $query->whereNull('estado')
                          ->orWhereNotIn('estado', ['atendido', 'cancelado']);
                })
                ->orderBy('created_at', 'desc')
                ->get()
        ]);
    }

    public function marcarAtendido($id)
    {
        $pedido = Consumo::find($id);
        if ($pedido) {
            $pedido->estado = 'atendido';
            $pedido->save();
        }
    }

    public function cancelarPedido($pedidoId)
    {
        DB::transaction(function () use ($pedidoId) {
            $pedido = Consumo::findOrFail($pedidoId);

            if ($pedido->estado === 'cancelado' || $pedido->estado === 'atendido') {
                return;
            }

            $this->aplicarReembolsoYCancelacion($pedido);
        });

        session()->flash('message', 'Pedido cancelado correctamente y saldo devuelto al estudiante.');
    }
}
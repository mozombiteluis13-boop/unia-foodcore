<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Estudiante;
use App\Models\Insumo;
use App\Models\ProductoKiosco;
use App\Models\Consumo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PedidoEstudiante extends Component
{
    public $dni = '';
    public $seleccionados = [];
    public $saldoActual = null;
    public $nombreEstudiante = '';
    public $seccionVista = 'menu'; // 'menu', 'extras' o 'kiosco'
    public string $categoriaKioscoActiva = 'Bebidas';
    public array $categoriasKiosco = ['Bebidas', 'Golosinas', 'Snacks', 'Galletas'];

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
        if (in_array($seccion, ['menu', 'extras', 'kiosco'], true)) {
            $this->seccionVista = $seccion;
        }
    }

    public function seleccionarCategoriaKiosco(string $categoria)
    {
        if (in_array($categoria, $this->categoriasKiosco, true)) {
            $this->categoriaKioscoActiva = $categoria;
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
        $vecesEnCarrito = collect($this->seleccionados)
            ->filter(fn ($sel) => ($sel['origen'] ?? 'comedor') === 'comedor' && (int) ($sel['id'] ?? 0) === (int) $item->id)
            ->count();

        if (($vecesEnCarrito + 1) > $item->cantidad) {
            session()->flash('error', 'Ya alcanzaste el stock disponible de ' . $item->nombre . '.');
            return;
        }

        $this->seleccionados[] = [
            'origen'    => 'comedor',
            'id'        => $item->id,
            'nombre'    => $item->nombre,
            'categoria' => $item->categoria ?? 'General',
            'unidad'    => $item->unidad ?? 'General',
            'es_extra'  => $esExtraItem,
            'precio'    => $precioReal,
        ];

        session()->flash('mensaje', '¡' . $item->nombre . ' agregado a tu selección!');
    }

    public function agregarProductoKiosco($productoId)
    {
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

        $producto = ProductoKiosco::where('activo', true)->find($productoId);
        if (!$producto || (int) $producto->stock <= 0) {
            session()->flash('error', 'No hay stock disponible para este producto del Kiosco.');
            return;
        }

        $precioReal = (float) ($producto->precio ?? 0);
        if ($precioReal <= 0) {
            session()->flash('error', 'Este producto del Kiosco no tiene un precio válido configurado.');
            return;
        }

        $vecesEnCarrito = collect($this->seleccionados)
            ->filter(fn ($sel) => ($sel['origen'] ?? 'comedor') === 'kiosco' && (int) ($sel['id'] ?? 0) === (int) $producto->id)
            ->count();

        if (($vecesEnCarrito + 1) > (int) $producto->stock) {
            session()->flash('error', 'Ya alcanzaste el stock disponible de ' . $producto->nombre . '.');
            return;
        }

        $this->seleccionados[] = [
            'origen'    => 'kiosco',
            'id'        => $producto->id,
            'nombre'    => $producto->nombre,
            'categoria' => 'KIOSCO - ' . strtoupper($producto->categoria),
            'unidad'    => $producto->categoria,
            'es_extra'  => false,
            'precio'    => $precioReal,
        ];

        session()->flash('mensaje', '¡' . $producto->nombre . ' (Kiosco) agregado a tu selección!');
    }

    public function quitarItem($index)
    {
        unset($this->seleccionados[$index]);
        $this->seleccionados = array_values($this->seleccionados);
    }

    public function enviarPedidoFinal()
    {
        if (empty(trim($this->dni))) {
            session()->flash('error', 'Ingresa tu DNI para enviar el pedido.');
            return;
        }

        if (empty($this->seleccionados)) {
            session()->flash('error', 'Debes seleccionar al menos un plato, bebida o producto.');
            return;
        }

        $tieneComedor = collect($this->seleccionados)->contains(fn ($sel) => ($sel['origen'] ?? 'comedor') === 'comedor');
        if ($tieneComedor && !$this->turnoPermitidoParaPedir()) {
            session()->flash('error', '⏰ Ya pasó la hora límite para enviar pedidos del comedor (hasta las 09:40 AM para desayuno y 03:40 PM para almuerzo).');
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

            $cantidadesComedor = [];
            $cantidadesKiosco = [];

            foreach ($this->seleccionados as $sel) {
                if (empty($sel['id'])) {
                    continue;
                }

                $origen = $sel['origen'] ?? 'comedor';
                if ($origen === 'kiosco') {
                    $cantidadesKiosco[(int) $sel['id']] = ($cantidadesKiosco[(int) $sel['id']] ?? 0) + 1;
                } else {
                    $cantidadesComedor[(int) $sel['id']] = ($cantidadesComedor[(int) $sel['id']] ?? 0) + 1;
                }
            }

            if (empty($cantidadesComedor) && empty($cantidadesKiosco)) {
                $resultadoError = 'No se encontraron ítems válidos en tu pedido.';
                return;
            }

            $insumosDb = !empty($cantidadesComedor)
                ? Insumo::whereIn('id', array_keys($cantidadesComedor))->lockForUpdate()->get()->keyBy('id')
                : collect();

            $kioscoDb = !empty($cantidadesKiosco)
                ? ProductoKiosco::where('activo', true)->whereIn('id', array_keys($cantidadesKiosco))->lockForUpdate()->get()->keyBy('id')
                : collect();

            $totalCalculado = 0;
            $nombresItems = [];

            foreach ($cantidadesComedor as $insumoId => $cantSolicitada) {
                $insumo = $insumosDb->get($insumoId);

                if (!$insumo) {
                    $resultadoError = 'Uno de los ítems del comedor seleccionados ya no existe.';
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

            foreach ($cantidadesKiosco as $productoId => $cantSolicitada) {
                $producto = $kioscoDb->get($productoId);

                if (!$producto) {
                    $resultadoError = 'Uno de los productos del Kiosco seleccionados ya no está disponible.';
                    return;
                }

                if ((int) $producto->stock < $cantSolicitada) {
                    $resultadoError = 'No hay suficiente stock en Kiosco para "' . $producto->nombre . '" (disponibles: ' . $producto->stock . ').';
                    return;
                }

                $precioUnitario = (float) ($producto->precio ?? 0);
                if ($precioUnitario <= 0) {
                    $resultadoError = 'El producto "' . $producto->nombre . '" no tiene un precio válido.';
                    return;
                }

                $totalCalculado += ($precioUnitario * $cantSolicitada);

                for ($i = 0; $i < $cantSolicitada; $i++) {
                    $nombresItems[] = $producto->nombre . ' (KIOSCO - ' . $producto->categoria . ')';
                }
            }

            if ((float) $estudiante->saldo < $totalCalculado) {
                $resultadoError = 'Saldo insuficiente. Tu saldo actual es S/. ' . number_format($estudiante->saldo, 2) . ' y el total de tu pedido es S/. ' . number_format($totalCalculado, 2);
                return;
            }

            // Descontar saldo y stock de forma segura
            $estudiante->saldo = (float) $estudiante->saldo - $totalCalculado;
            $estudiante->save();

            foreach ($cantidadesComedor as $insumoId => $cantSolicitada) {
                $insumo = $insumosDb->get($insumoId);
                $insumo->decrement('cantidad', $cantSolicitada);
            }

            foreach ($cantidadesKiosco as $productoId => $cantSolicitada) {
                $producto = $kioscoDb->get($productoId);
                $producto->decrement('stock', $cantSolicitada);
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

        $productosKiosco = $this->seccionVista === 'kiosco'
            ? ProductoKiosco::where('activo', true)
                ->where('categoria', $this->categoriaKioscoActiva)
                ->orderBy('nombre')
                ->get()
            : collect();

        return view('livewire.pedido-estudiante', [
            'insumos'         => $insumos,
            'productosKiosco' => $productosKiosco,
            'turnoVisible'    => $turnoVisible,
            'puedePedir'      => $puedePedir,
        ]);
    }
}
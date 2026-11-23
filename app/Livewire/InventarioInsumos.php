<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Insumo;
use Carbon\Carbon;

class InventarioInsumos extends Component
{
    public $turno = 'Desayuno';
    public $esExtra = false;
    public $seccionVista = 'menu'; // 'menu' o 'extras'
    public $categoria = 'BEBIDAS';
    public $nombre;
    public $cantidad;
    public $unidad = 'Porción';
    public $precio;

    private function turnoPorHora(?string $horaActual = null): ?string
    {
        $hora = $horaActual ?? Carbon::now('America/Lima')->format('H:i');

        if ($hora >= '06:00' && $hora < '10:00') {
            return 'Desayuno';
        }

        if ($hora >= '11:00' && $hora <= '16:00') {
            return 'Almuerzo';
        }

        return null;
    }

    private function turnoPublicadoActual(): ?string
    {
        $turnoHora = $this->turnoPorHora();

        if (!$turnoHora) {
            return null;
        }

        $hayPublicado = Insumo::where('cantidad', '>', 0)
            ->where('turno', $turnoHora)
            ->exists();

        return $hayPublicado ? $turnoHora : null;
    }

    protected function rules()
    {
        $esExtraAlmuerzo = ($this->turno === 'Almuerzo' && $this->esExtra);

        if ($esExtraAlmuerzo) {
            return [
                'turno'     => 'required|string',
                'esExtra'   => 'boolean',
                'categoria' => 'required|in:Plato,Bebida',
                'nombre'    => 'required|string|max:255',
                'cantidad'  => 'required|integer|min:1',
                'precio'    => 'required|numeric|min:0.10',
            ];
        }

        $esPostreOBebidaAlmuerzo = ($this->turno === 'Almuerzo' && in_array($this->categoria, ['POSTRES', 'BEBIDAS']));

        return [
            'turno'     => 'required|string',
            'esExtra'   => 'boolean',
            'categoria' => 'required|string',
            'nombre'    => 'required|string|max:255',
            'cantidad'  => 'required|integer|min:1',
            'precio'    => $esPostreOBebidaAlmuerzo ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
        ];
    }

    public function cambiarSeccionVista(string $seccion)
    {
        if (in_array($seccion, ['menu', 'extras'], true)) {
            $this->seccionVista = $seccion;
        }
    }

    public function updatedTurno($value)
    {
        if ($value === 'Desayuno') {
            $this->esExtra = false;
            $this->seccionVista = 'menu';
            $this->categoria = 'BEBIDAS';
        } else {
            $this->categoria = $this->esExtra ? 'Plato' : 'PLATOS DE FONDO';
        }
    }

    public function updatedEsExtra($value)
    {
        if ($this->turno !== 'Almuerzo') {
            $this->esExtra = false;
            $this->categoria = 'BEBIDAS';
            return;
        }

        $this->categoria = $value ? 'Plato' : 'PLATOS DE FONDO';
    }

    public function guardarInsumo()
    {
        $esExtraAlmuerzo = ($this->turno === 'Almuerzo' && $this->esExtra);

        $exigePrecio = $esExtraAlmuerzo
            || $this->turno === 'Desayuno'
            || ($this->turno === 'Almuerzo' && stripos($this->categoria, 'Fondo') !== false);

        $rules = [
            'turno'     => 'required|string',
            'esExtra'   => 'boolean',
            'categoria' => $esExtraAlmuerzo ? 'required|in:Plato,Bebida' : 'required|string',
            'nombre'    => 'required|string|max:255',
            'cantidad'  => 'required|integer|min:1',
        ];

        if ($exigePrecio) {
            $rules['precio'] = $esExtraAlmuerzo ? 'required|numeric|min:0.10' : 'required|numeric|min:0';
        }

        $this->validate($rules);

        $precioFinal = $exigePrecio ? $this->precio : 0;

        Insumo::create([
            'turno'     => $this->turno,
            'es_extra'  => $esExtraAlmuerzo,
            'categoria' => $this->categoria,
            'nombre'    => $this->nombre,
            'cantidad'  => $this->cantidad,
            'unidad'    => $this->unidad,
            'precio'    => $precioFinal,
        ]);

        // Cambia automáticamente la pestaña inferior para ver lo recién publicado
        $this->seccionVista = $esExtraAlmuerzo ? 'extras' : 'menu';

        $this->reset(['nombre', 'cantidad', 'precio', 'esExtra']);
        $this->updatedTurno($this->turno);
        session()->flash('exito', '¡Menú publicado correctamente!');
    }

    public function eliminarInsumo($id)
    {
        Insumo::find($id)?->delete();

        $this->dispatch('insumo-eliminado')->to(PedidoEstudiante::class);
    }

    public function render()
    {
        $turnoPublicado = $this->turnoPublicadoActual();
        $insumos = collect();

        if ($turnoPublicado) {
            if ($turnoPublicado !== 'Almuerzo' && $this->seccionVista === 'extras') {
                $this->seccionVista = 'menu';
            }

            $query = Insumo::where('cantidad', '>', 0)
                ->where('turno', $turnoPublicado);

            if ($turnoPublicado === 'Almuerzo') {
                $query->where('es_extra', $this->seccionVista === 'extras');
            } else {
                $query->where(function ($q) {
                    $q->where('es_extra', false)->orWhereNull('es_extra');
                });
            }

            $insumos = $query->get();
        }

        return view('livewire.inventario-insumos', [
            'insumos'        => $insumos,
            'turnoPublicado' => $turnoPublicado,
        ]);
    }
}
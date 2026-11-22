<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Insumo;

class InventarioInsumos extends Component
{
    public $turno = 'Desayuno';
    public $esExtra = false;
    public $categoria = 'BEBIDAS';
    public $nombre;
    public $cantidad;
    public $unidad = 'Porción';
    public $precio;

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
                'precio'    => 'required|numeric|min:0.50',
            ];
        }

        // Si es Almuerzo normal y la categoría es POSTRES o BEBIDAS, el precio es opcional
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

    // Se ejecuta al cambiar el turno
    public function updatedTurno($value)
    {
        if ($value === 'Desayuno') {
            $this->esExtra = false;
            $this->categoria = 'BEBIDAS';
        } else {
            $this->categoria = $this->esExtra ? 'Plato' : 'PLATOS DE FONDO';
        }
    }

    // Se ejecuta al marcar o desmarcar Extra
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

        // Si es Extra en Almuerzo, siempre exige precio; en menú normal mantiene tu regla
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
            $rules['precio'] = $esExtraAlmuerzo ? 'required|numeric|min:0.50' : 'required|numeric|min:0';
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
        return view('livewire.inventario-insumos', [
            'insumos' => Insumo::where('cantidad', '>', 0)
                                 ->where('turno', $this->turno)
                                 ->get()
        ]);
    }
}
<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Insumo;

class InventarioInsumos extends Component
{
    public $turno = 'Desayuno';
    public $categoria = 'Bebida';
    public $nombre;
    public $cantidad;
    public $unidad = 'Porción';
    public $precio;

    protected function rules()
    {
        // Si es Almuerzo y la categoría es Postres o Bebidas, el precio es opcional (nullable)
        $esPostreOBebidaAlmuerzo = ($this->turno === 'Almuerzo' && in_array($this->categoria, ['Postres', 'Bebidas']));

        return [
            'turno'     => 'required|string',
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
            $this->categoria = 'Bebida';
        } else {
            $this->categoria = 'Plato de Fondo / Comida';
        }
    }

    public function guardarInsumo()
    {
        // Si es desayuno, sí exige precio. Si es almuerzo, solo exige precio si dice "Fondo".
        $exigePrecio = ($this->turno === 'Desayuno' || ($this->turno === 'Almuerzo' && stripos($this->categoria, 'Fondo') !== false));

        $rules = [
            'turno'     => 'required|string',
            'categoria' => 'required|string',
            'nombre'    => 'required|string|max:255',
            'cantidad'  => 'required|integer|min:1',
        ];

        if ($exigePrecio) {
            $rules['precio'] = 'required|numeric|min:0';
        }

        $this->validate($rules);

        // Si exige precio guarda lo escrito, si no, guarda 0 automáticamente
        $precioFinal = $exigePrecio ? $this->precio : 0;

        Insumo::create([
            'turno'     => $this->turno,
            'categoria' => $this->categoria, 
            'nombre'    => $this->nombre,
            'cantidad'  => $this->cantidad,
            'unidad'    => $this->unidad,
            'precio'    => $precioFinal,
        ]);

        $this->reset(['nombre', 'cantidad', 'precio']);
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
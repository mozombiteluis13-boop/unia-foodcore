<?php

namespace App\Livewire;

use App\Models\ProductoKiosco;
use Illuminate\Support\Str;
use Livewire\Component;

class GestionKiosco extends Component
{
    public string $categoriaActiva = 'Bebidas';

    public string $categoria = 'Bebidas';
    public string $nombre = '';
    public string $precio = '';
    public int $stock = 10;

    // Cantidad personalizada por ID de producto para reponer stock
    public array $cantidadAgregar = [];

    public array $categorias = ['Bebidas', 'Golosinas', 'Snacks', 'Galletas'];

    protected function rules(): array
    {
        return [
            'categoria' => ['required', 'in:Bebidas,Golosinas,Snacks,Galletas'],
            'nombre'    => ['required', 'string', 'max:255'],
            'precio'    => ['required', 'numeric', 'min:0.10'],
            'stock'     => ['required', 'integer', 'min:0'],
        ];
    }

    public function seleccionarCategoria(string $cat): void
    {
        if (in_array($cat, $this->categorias, true)) {
            $this->categoriaActiva = $cat;
            $this->categoria = $cat;
        }
    }

    public function guardarProducto(): void
    {
        $datos = $this->validate();

        ProductoKiosco::create([
            'categoria' => $datos['categoria'],
            'nombre'    => trim($datos['nombre']),
            'precio'    => $datos['precio'],
            'stock'     => $datos['stock'],
            'activo'    => true,
        ]);

        $this->categoriaActiva = $datos['categoria'];
        $this->reset(['nombre', 'precio']);
        $this->stock = 10;
        $this->categoria = $this->categoriaActiva;

        session()->flash('kiosco_ok', 'Producto agregado al Kiosco correctamente.');
    }

    public function sumarStock(int $id, int $cantidad = 5): void
    {
        $producto = ProductoKiosco::find($id);
        if ($producto) {
            $producto->increment('stock', max(1, $cantidad));
            session()->flash('kiosco_ok', "Stock actualizado para {$producto->nombre}.");
        }
    }

    public function agregarStockPersonalizado(int $id): void
    {
        $cantidad = (int) ($this->cantidadAgregar[$id] ?? 0);

        if ($cantidad < 1) {
            session()->flash('kiosco_ok', 'Ingresa una cantidad mayor o igual a 1 para agregar stock.');
            return;
        }

        $producto = ProductoKiosco::find($id);
        if ($producto) {
            $producto->increment('stock', $cantidad);
            $this->cantidadAgregar[$id] = null;
            session()->flash('kiosco_ok', "Se agregaron +{$cantidad} unidades a {$producto->nombre}.");
        }
    }

    public function eliminarProducto(int $id): void
    {
        $producto = ProductoKiosco::find($id);
        if ($producto) {
            $producto->delete();
            session()->flash('kiosco_ok', 'Producto eliminado del Kiosco.');
        }
    }

    public function obtenerImagenProducto(string $nombreProducto, string $categoria): string
    {
        $limpio = Str::of($nombreProducto)
            ->ascii()
            ->lower()
            ->replace([' ', '-', '_'], '')
            ->toString();

        $rutaProducto = "images/kiosco/{$limpio}.jpg";
        if (file_exists(public_path($rutaProducto))) {
            return asset($rutaProducto);
        }

        $catSlug = Str::of($categoria)->ascii()->lower()->toString();
        $rutaCategoria = "images/kiosco/{$catSlug}.jpg";
        if (file_exists(public_path($rutaCategoria))) {
            return asset($rutaCategoria);
        }

        return '';
    }

    public function render()
    {
        $productos = ProductoKiosco::where('activo', true)
            ->where('categoria', $this->categoriaActiva)
            ->orderBy('nombre')
            ->get()
            ->map(function ($prod) {
                $prod->imagen_url = $this->obtenerImagenProducto($prod->nombre, $prod->categoria);
                return $prod;
            });

        foreach ($productos as $prod) {
            if (!array_key_exists($prod->id, $this->cantidadAgregar)) {
                $this->cantidadAgregar[$prod->id] = null;
            }
        }

        $conteos = [
            'Bebidas'   => ProductoKiosco::where('activo', true)->where('categoria', 'Bebidas')->count(),
            'Golosinas' => ProductoKiosco::where('activo', true)->where('categoria', 'Golosinas')->count(),
            'Snacks'    => ProductoKiosco::where('activo', true)->where('categoria', 'Snacks')->count(),
            'Galletas'  => ProductoKiosco::where('activo', true)->where('categoria', 'Galletas')->count(),
        ];

        $imagenesCategorias = [];
        foreach ($this->categorias as $cat) {
            $slug = Str::of($cat)->ascii()->lower()->toString();
            $ruta = "images/kiosco/{$slug}.jpg";
            $imagenesCategorias[$cat] = file_exists(public_path($ruta)) ? asset($ruta) : null;
        }

        return view('livewire.gestion-kiosco', [
            'productos'          => $productos,
            'conteos'            => $conteos,
            'imagenesCategorias' => $imagenesCategorias,
        ]);
    }
}
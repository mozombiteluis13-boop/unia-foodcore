<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Estudiante;
use App\Models\Insumo;
use App\Models\ProductoKiosco;
use App\Models\Consumo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PedidoEstudiante extends Component
{
    public $dni = '';
    public $seleccionados = [];
    public $saldoActual = null;
    public $nombreEstudiante = '';
    public $seccionVista = 'menu'; // 'menu', 'extras' o 'kiosco'
    public bool $kioscoAbierto = false; // Solo true cuando se presiona el botón Kiosco
    public string $categoriaKioscoActiva = 'Bebidas';
    public array $categoriasKiosco = ['Bebidas', 'Golosinas', 'Snacks', 'Galletas'];

    private function buscarArchivoImagenKiosco(string $baseSinExtension): ?string
    {
        $extensiones = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($extensiones as $ext) {
            $rutaRelativa = "images/kiosco/{$baseSinExtension}.{$ext}";
            if (file_exists(public_path($rutaRelativa))) {
                return asset($rutaRelativa);
            }
        }

        return null;
    }

    public function obtenerImagenProductoKiosco(string $nombreProducto, string $categoria): string
    {
        $limpio = Str::of($nombreProducto)
            ->ascii()
            ->lower()
            ->replace([' ', '-', '_'], '')
            ->toString();

        $imagenProducto = $this->buscarArchivoImagenKiosco($limpio);
        if ($imagenProducto) {
            return $imagenProducto;
        }

        $catSlug = Str::of($categoria)->ascii()->lower()->toString();
        $imagenCategoria = $this->buscarArchivoImagenKiosco($catSlug);
        if ($imagenCategoria) {
            return $imagenCategoria;
        }

        return '';
    }

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

    private function turnoPorHora(?string $horaActual = null): ?string
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

    private function turnoVisible(?string $horaActual = null): ?string
    {
        $turnoHora = $this->turnoPorHora($horaActual);

        if (!$turnoHora) {
            return null;
        }

        $hayPublicado = Insumo::where('cantidad', '>', 0)
            ->where('turno', $turnoHora)
            ->exists();

        return $hayPublicado ? $turnoHora : null;
    }

    private function esAcompanamientoGratisMenu(string $turno, bool $esExtra, float $precio, string $categoria, string $nombre): bool
    {
        if ($turno !== 'Almuerzo' || $esExtra || $precio > 0) {
            return false;
        }

        $catUpper = mb_strtoupper(trim($categoria), 'UTF-8');
        $nombreLower = mb_strtolower($nombre, 'UTF-8');

        if (in_array($catUpper, ['BEBIDAS', 'BEBIDA', 'POSTRES', 'POSTRE'], true)) {
            return true;
        }

        return str_contains($nombreLower, 'camu')
            || str_contains($nombreLower, 'chicha')
            || str_contains($nombreLower, 'maracuya')
            || str_contains($nombreLower, 'jugo')
            || str_contains($nombreLower, 'refresco')
            || str_contains($nombreLower, 'kinua')
            || str_contains($nombreLower, 'quinua')
            || str_contains($nombreLower, 'te')
            || str_contains($nombreLower, 'huancaina')
            || str_contains($nombreLower, 'postre')
            || str_contains($nombreLower, 'gelatina')
            || str_contains($nombreLower, 'mazamorra')
            || str_contains($nombreLower, 'fruta');
    }

    private function esPlatoPrincipalMenu(string $turno, bool $esExtra, float $precio, string $categoria, string $nombre): bool
    {
        if ($turno !== 'Almuerzo' || $esExtra) {
            return false;
        }

        return !$this->esAcompanamientoGratisMenu($turno, $esExtra, $precio, $categoria, $nombre);
    }

    private function tieneMenuPrincipalSeleccionado(): bool
    {
        foreach ($this->seleccionados as $sel) {
            if (($sel['origen'] ?? 'comedor') !== 'comedor') {
                continue;
            }

            if (!empty($sel['es_plato_menu'])) {
                return true;
            }
        }

        return false;
    }

    public function abrirKiosco()
    {
        $this->kioscoAbierto = true;
        $this->seccionVista = 'kiosco';
    }

    public function cambiarSeccionVista(string $seccion)
    {
        if (in_array($seccion, ['menu', 'extras', 'kiosco'], true)) {
            $this->seccionVista = $seccion;
            if ($seccion === 'kiosco') {
                $this->kioscoAbierto = true;
            }
        }
    }

    public function seleccionarCategoriaKiosco(string $categoria)
    {
        if (in_array($categoria, $this->categoriasKiosco, true)) {
            $this->categoriaKioscoActiva = $categoria;
            $this->kioscoAbierto = true;
            $this->seccionVista = 'kiosco';
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

        if (!$item || $item->cantidad <= 0 || !$turnoActual || $item->turno !== $turnoActual) {
            session()->flash('error', 'No hay stock disponible o el ítem no pertenece al turno actual.');
            return;
        }

        $esExtraItem = (bool) ($item->es_extra ?? false);
        $precioReal = (float) ($item->precio ?? 0);
        $categoriaItem = $item->categoria ?? $item->unidad ?? 'General';

        // Si es extra, sí o sí debe tener precio mayor a 0
        if ($esExtraItem && $precioReal <= 0) {
            session()->flash('error', 'Este ítem extra no tiene un precio válido configurado.');
            return;
        }

        $esAcompanamientoGratis = $this->esAcompanamientoGratisMenu(
            (string) $item->turno,
            $esExtraItem,
            $precioReal,
            (string) $categoriaItem,
            (string) $item->nombre
        );

        // Bloqueo: bebida o postre gratis del menú requiere haber elegido primero el Menú
        if ($esAcompanamientoGratis && !$this->tieneMenuPrincipalSeleccionado()) {
            session()->flash('error', '🔒 Primero debes seleccionar tu Menú (Plato de Fondo) para poder elegir tu bebida o postre incluido.');
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

        $esPlatoMenu = $this->esPlatoPrincipalMenu(
            (string) $item->turno,
            $esExtraItem,
            $precioReal,
            (string) $categoriaItem,
            (string) $item->nombre
        );

        $this->seleccionados[] = [
            'origen'                 => 'comedor',
            'id'                     => $item->id,
            'nombre'                 => $item->nombre,
            'categoria'              => $item->categoria ?? 'General',
            'unidad'                 => $item->unidad ?? 'General',
            'es_extra'               => $esExtraItem,
            'es_plato_menu'          => $esPlatoMenu,
            'es_acompanamiento_menu' => $esAcompanamientoGratis,
            'precio'                 => $precioReal,
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
        $itemQuitado = $this->seleccionados[$index] ?? null;
        unset($this->seleccionados[$index]);
        $this->seleccionados = array_values($this->seleccionados);

        // Si quitó su Menú y ya no queda ningún otro plato de fondo del menú, retira también bebida/postre gratis
        if (!empty($itemQuitado['es_plato_menu']) && !$this->tieneMenuPrincipalSeleccionado()) {
            $antes = count($this->seleccionados);
            $this->seleccionados = array_values(array_filter(
                $this->seleccionados,
                fn ($sel) => empty($sel['es_acompanamiento_menu'])
            ));

            if (count($this->seleccionados) < $antes) {
                session()->flash('error', 'ℹ️ Se retiraron la bebida y/o el postre incluidos porque quitaste tu Menú.');
            }
        }
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
            $tienePlatoMenuDb = false;
            $tieneAcompanamientoGratisDb = false;

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
                $categoriaDb = (string) ($insumo->categoria ?? $insumo->unidad ?? 'General');

                // Blindaje: un ítem extra siempre debe cobrar su precio real > 0
                if ($esExtraItem && $precioUnitario <= 0) {
                    $resultadoError = 'El extra "' . $insumo->nombre . '" no tiene precio asignado y no puede procesarse sin cobro.';
                    return;
                }

                if ($this->esAcompanamientoGratisMenu((string) $insumo->turno, $esExtraItem, $precioUnitario, $categoriaDb, (string) $insumo->nombre)) {
                    $tieneAcompanamientoGratisDb = true;
                }

                if ($this->esPlatoPrincipalMenu((string) $insumo->turno, $esExtraItem, $precioUnitario, $categoriaDb, (string) $insumo->nombre)) {
                    $tienePlatoMenuDb = true;
                }

                $totalCalculado += ($precioUnitario * $cantSolicitada);

                for ($i = 0; $i < $cantSolicitada; $i++) {
                    $nombresItems[] = $esExtraItem ? ($insumo->nombre . ' (EXTRA)') : $insumo->nombre;
                }
            }

            if ($tieneAcompanamientoGratisDb && !$tienePlatoMenuDb) {
                $resultadoError = '🔒 No puedes enviar bebidas o postres incluidos sin haber seleccionado tu Menú principal.';
                return;
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
        $this->reset(['seleccionados', 'dni', 'saldoActual', 'nombreEstudiante', 'kioscoAbierto']);
        $this->seccionVista = 'menu';
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

        $productosKiosco = $this->kioscoAbierto
            ? ProductoKiosco::where('activo', true)
                ->where('categoria', $this->categoriaKioscoActiva)
                ->orderBy('nombre')
                ->get()
                ->map(function ($prod) {
                    $prod->imagen_url = $this->obtenerImagenProductoKiosco($prod->nombre, $prod->categoria);
                    return $prod;
                })
            : collect();

        $imagenesCategoriasKiosco = [];
        foreach ($this->categoriasKiosco as $catKiosco) {
            $catSlug = Str::of($catKiosco)->ascii()->lower()->toString();
            $imagenesCategoriasKiosco[$catKiosco] = $this->buscarArchivoImagenKiosco($catSlug);
        }

        return view('livewire.pedido-estudiante', [
            'insumos'                  => $insumos,
            'productosKiosco'          => $productosKiosco,
            'imagenesCategoriasKiosco' => $imagenesCategoriasKiosco,
            'turnoVisible'             => $turnoVisible,
            'puedePedir'               => $puedePedir,
            'tieneMenuSeleccionado'    => $this->tieneMenuPrincipalSeleccionado(),
        ]);
    }
}
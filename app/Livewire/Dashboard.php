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

    // Sección de ganancias semanales
    public bool $mostrarGananciasSemanales = false;

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

    public function abrirGananciasSemanales(): void
    {
        $this->mostrarGananciasSemanales = true;
    }

    public function cerrarGananciasSemanales(): void
    {
        $this->mostrarGananciasSemanales = false;
    }

    private function detectarRubroGanancia(string $textoItem): string
    {
        $upper = mb_strtoupper($textoItem, 'UTF-8');

        if (str_contains($upper, '(EXTRA)')) {
            return 'extras';
        }

        if (str_contains($upper, '(KIOSCO')) {
            if (str_contains($upper, 'BEBID')) {
                return 'bebidas';
            }
            if (str_contains($upper, 'SNACK')) {
                return 'snacks';
            }
            if (str_contains($upper, 'GOLOSIN')) {
                return 'golosinas';
            }
            if (str_contains($upper, 'GALLET')) {
                return 'galletas';
            }
            return 'otros';
        }

        return 'menu';
    }

    private function calcularGananciasSemanales(): array
    {
        $inicioSemana = Carbon::now('America/Lima')->startOfWeek(Carbon::MONDAY)->startOfDay();
        $finSemana    = Carbon::now('America/Lima')->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $consumosSemana = Consumo::whereBetween('created_at', [$inicioSemana, $finSemana])->get();

        $ganancias = [
            'menu'      => 0.0,
            'extras'    => 0.0,
            'bebidas'   => 0.0,
            'snacks'    => 0.0,
            'golosinas' => 0.0,
            'galletas'  => 0.0,
            'otros'     => 0.0,
        ];

        foreach ($consumosSemana as $consumo) {
            if (($consumo->estado ?? '') === 'cancelado') {
                continue;
            }

            $textoPedido = (string) ($consumo->pedido ?? '');
            $montoTotal  = round((float) ($consumo->precio ?? 0), 2);
            $partes      = array_filter(array_map('trim', explode(' + ', $textoPedido)));

            if (count($partes) <= 1) {
                $categoria = $this->detectarRubroGanancia($textoPedido);
                $ganancias[$categoria] = round($ganancias[$categoria] + $montoTotal, 2);
                continue;
            }

            $partesConCobro = [];
            foreach ($partes as $parte) {
                $rubro = $this->detectarRubroGanancia($parte);
                $esAcompanamientoGratis = in_array($rubro, ['bebidas', 'snacks', 'golosinas', 'galletas'], true)
                    && !str_contains(mb_strtoupper($parte, 'UTF-8'), '(KIOSCO')
                    && $montoTotal > 0;

                if (!$esAcompanamientoGratis) {
                    $partesConCobro[] = $parte;
                }
            }

            if (empty($partesConCobro)) {
                $partesConCobro = $partes;
            }

            $cantidadPartes = count($partesConCobro);
            $montoBase      = round($montoTotal / $cantidadPartes, 2);
            $diferencia     = round($montoTotal - ($montoBase * $cantidadPartes), 2);

            foreach ($partesConCobro as $indice => $parteCobrada) {
                $rubro = $this->detectarRubroGanancia($parteCobrada);
                $montoAsignado = $montoBase + ($indice === 0 ? $diferencia : 0.0);
                $ganancias[$rubro] = round($ganancias[$rubro] + $montoAsignado, 2);
            }
        }

        return [
            'rango'  => $inicioSemana->format('d/m/Y') . ' - ' . $finSemana->format('d/m/Y'),
            'rubros' => $ganancias,
            'total'  => round(array_sum($ganancias), 2),
        ];
    }

    public function render()
    {
        $reporteSemanal = $this->mostrarGananciasSemanales
            ? $this->calcularGananciasSemanales()
            : [
                'rango'  => '',
                'rubros' => [
                    'menu'      => 0.0,
                    'extras'    => 0.0,
                    'bebidas'   => 0.0,
                    'snacks'    => 0.0,
                    'golosinas' => 0.0,
                    'galletas'  => 0.0,
                    'otros'     => 0.0,
                ],
                'total'  => 0.0,
            ];

        return view('livewire.dashboard', [
            'almuerzosHoy'        => Consumo::whereDate('created_at', Carbon::today())->count(),
            'estudiantesActivos'  => Estudiante::where('activo', true)->count(),
            'totalInsumos'        => Insumo::count(),
            'menuDelDia'          => Insumo::latest()->get(),
            'ultimasAsistencias'  => Consumo::whereDate('created_at', Carbon::today())
                                        ->latest()
                                        ->take(10)
                                        ->get(),
            'reporteSemanal'      => $reporteSemanal,
        ]);
    }
}
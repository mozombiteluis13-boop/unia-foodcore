<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\AsistenciaComedor;
use App\Livewire\InventarioInsumos;
use App\Livewire\GestionEstudiantes;
use App\Livewire\GestionKiosco;
use App\Livewire\PedidoEstudiante;
use App\Livewire\RegistroEstudiante;
use App\Models\Consumo;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Rutas de Acceso (Login / Logout Administrador)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Rutas PÚBLICAS (Módulo de Estudiantes)
|--------------------------------------------------------------------------
*/
// 1. Módulo para que el Estudiante realice sus pedidos
Route::get('/estudiante/pedido', PedidoEstudiante::class)->name('estudiante.pedido');

// 2. Módulo de Pre-Registro de Estudiantes
Route::get('/registro', RegistroEstudiante::class)->name('registro');

// 3. Módulo de Historial y Reclamos por Estudiante (Mes Calendario Actual)
Route::get('/historial-estudiante', function () {
    $dni = request('dni');
    $historial = [];
    $estudiante = null;

    if ($dni) {
        $estudiante = \App\Models\Estudiante::where('dni', $dni)->first();

        if ($estudiante) {
            $historial = Consumo::where('estudiante_id', $estudiante->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->latest()
                ->get();
        }
    }

    return view('historial-estudiante', compact('historial', 'estudiante', 'dni'));
})->name('historial.estudiante');


/*
|--------------------------------------------------------------------------
| Rutas PROTEGIDAS (Solo Administrador Autenticado)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard Principal del Encargado
    Route::get('/', Dashboard::class)->name('dashboard');

    // Recarga de Saldo desde el Panel del Administrador
    Route::post('/admin/recargar-saldo', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'dni'   => 'required|exists:estudiantes,dni',
            'monto' => 'required|numeric|min:0.50',
        ]);

        $estudiante = \App\Models\Estudiante::where('dni', $request->dni)->first();

        if ($estudiante) {
            $estudiante->saldo = $estudiante->saldo + $request->monto;
            $estudiante->save();
        }

        return back()->with('success', '¡Recarga registrada con éxito!');
    })->name('admin.recargar.saldo');

    // Exportar Ganancias Semanales a Excel (.csv compatible con Excel)
    Route::get('/admin/ganancias-semanales/exportar', function () {
        $inicioSemana = \Carbon\Carbon::now('America/Lima')->startOfWeek(\Carbon\Carbon::MONDAY)->startOfDay();
        $finSemana    = \Carbon\Carbon::now('America/Lima')->endOfWeek(\Carbon\Carbon::SUNDAY)->endOfDay();

        $consumos = Consumo::whereBetween('created_at', [$inicioSemana, $finSemana])
            ->orderBy('created_at')
            ->get();

        $detectarRubro = function (string $textoItem): string {
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
        };

        $ganancias = [
            'menu'      => 0.0,
            'extras'    => 0.0,
            'bebidas'   => 0.0,
            'snacks'    => 0.0,
            'golosinas' => 0.0,
            'galletas'  => 0.0,
            'otros'     => 0.0,
        ];

        foreach ($consumos as $consumo) {
            if (($consumo->estado ?? '') === 'cancelado') {
                continue;
            }

            $textoPedido = (string) ($consumo->pedido ?? '');
            $montoTotal  = (float) ($consumo->precio ?? 0);
            $partes      = array_filter(array_map('trim', explode(' + ', $textoPedido)));

            if (count($partes) <= 1) {
                $rubro = $detectarRubro($textoPedido);
                $ganancias[$rubro] += $montoTotal;
                continue;
            }

            $partesConCobro = [];
            foreach ($partes as $parte) {
                $rubro = $detectarRubro($parte);
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

            $montoPorParte = $montoTotal / count($partesConCobro);
            foreach ($partesConCobro as $parteCobrada) {
                $rubro = $detectarRubro($parteCobrada);
                $ganancias[$rubro] += $montoPorParte;
            }
        }

        $totalSemanal = array_sum($ganancias);
        $nombreArchivo = 'ganancias-semanales-' . $inicioSemana->format('Y-m-d') . '-al-' . $finSemana->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($consumos, $inicioSemana, $finSemana, $ganancias, $totalSemanal) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 para abrir correctamente en Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['REPORTE SEMANAL DE GANANCIAS', $inicioSemana->format('d/m/Y') . ' - ' . $finSemana->format('d/m/Y')], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['RESUMEN POR RUBRO', 'MONTO (S/.)'], ';');
            fputcsv($handle, ['Menú', number_format($ganancias['menu'], 2, '.', '')], ';');
            fputcsv($handle, ['Extras', number_format($ganancias['extras'], 2, '.', '')], ';');
            fputcsv($handle, ['Bebidas / Gaseosas', number_format($ganancias['bebidas'], 2, '.', '')], ';');
            fputcsv($handle, ['Snacks', number_format($ganancias['snacks'], 2, '.', '')], ';');
            fputcsv($handle, ['Golosinas', number_format($ganancias['golosinas'], 2, '.', '')], ';');
            fputcsv($handle, ['Galletas', number_format($ganancias['galletas'], 2, '.', '')], ';');
            fputcsv($handle, ['TOTAL SEMANAL', number_format($totalSemanal, 2, '.', '')], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['DETALLE DE CONSUMOS DE LA SEMANA'], ';');
            fputcsv($handle, ['Fecha y Hora', 'DNI Estudiante', 'Pedido', 'Estado', 'Monto (S/.)'], ';');

            foreach ($consumos as $consumo) {
                $dni = $consumo->dni_estudiante ?? optional($consumo->estudiante)->dni ?? '';
                fputcsv($handle, [
                    \Carbon\Carbon::parse($consumo->created_at)->setTimezone('America/Lima')->format('d/m/Y H:i'),
                    $dni,
                    (string) ($consumo->pedido ?? ''),
                    (string) ($consumo->estado ?? 'registrado'),
                    number_format((float) ($consumo->precio ?? 0), 2, '.', ''),
                ], ';');
            }

            fclose($handle);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    })->name('admin.ganancias.exportar');

    // Monitor de Pedidos del Encargado
    Route::get('/monitor-pedidos', function () {
        return view('monitor-pedidos');
    })->name('monitor.pedidos');

    // Monitoreo automático de nuevos pedidos en la cocina
    Route::get('/conteo-pedidos', function () {
        $pendientes = \App\Models\Consumo::whereDate('created_at', today())
            ->where(function ($q) {
                $q->where('estado', 'pendiente')->orWhereNull('estado');
            });

        return response()->json([
            'conteo'     => $pendientes->count(),
            'ultimo_id'  => $pendientes->max('id') ?? 0,
        ]);
    });

    // Procesar el botón "Entregar" del monitor
    Route::patch('/pedidos/{id}/entregar', function ($id) {
        try {
            $pedido = Consumo::find($id);
            if ($pedido) {
                $pedido->update(['estado' => 'atendido']);
            }
        } catch (\Exception $e) {
            // Si la columna estado no existe en la base de datos, el sistema no se caerá
        }
        return back();
    })->name('pedidos.entregar');

    // Procesar el botón "Cancelar y Reembolsar" del monitor
    Route::post('/pedidos/{id}/cancelar', function ($id) {
        \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $pedido = \Illuminate\Support\Facades\DB::table('consumos')->where('id', $id)->first();

            if ($pedido && $pedido->estado !== 'cancelado') {
                // 1. Devolver saldo al estudiante
                if (!empty($pedido->dni_estudiante)) {
                    \Illuminate\Support\Facades\DB::table('estudiantes')
                        ->where('dni', $pedido->dni_estudiante)
                        ->increment('saldo', $pedido->precio ?? 0);
                } elseif (!empty($pedido->estudiante_id)) {
                    \Illuminate\Support\Facades\DB::table('estudiantes')
                        ->where('id', $pedido->estudiante_id)
                        ->increment('saldo', $pedido->precio ?? 0);
                }

                // 2. Devolver la ración disponible al plato
                if (!empty($pedido->plato_id)) {
                    \Illuminate\Support\Facades\DB::table('platos')
                        ->where('id', $pedido->plato_id)
                        ->increment('raciones_disponibles');
                }

                // 3. Cambiar el estado del pedido a cancelado
                \Illuminate\Support\Facades\DB::table('consumos')
                    ->where('id', $id)
                    ->update(['estado' => 'cancelado']);
            }
        });
        return back();
    })->name('pedidos.cancelar');

    // Módulos de Administración y Comedor
    Route::get('/asistencia', AsistenciaComedor::class)->name('asistencia');
    Route::get('/inventario', InventarioInsumos::class)->name('inventario');
    Route::get('/estudiantes', GestionEstudiantes::class)->name('estudiantes');
    Route::get('/kiosco', GestionKiosco::class)->name('kiosco');

});
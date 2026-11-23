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
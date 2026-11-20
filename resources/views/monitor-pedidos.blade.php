<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIA FoodCore - Monitor de Pedidos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 font-sans antialiased p-6 min-h-screen">

    <div class="max-w-6xl mx-auto">
        <!-- Header Profesional con Indicador Live -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 border-b border-slate-800 pb-6">
            <div>
                <div class="flex items-center gap-3">
                    <span class="text-3xl">👨‍🍳</span>
                    <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">
                        UNIA <span class="text-amber-400">FoodCore</span>
                    </h1>
                    <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[10px] font-extrabold uppercase tracking-widest flex items-center gap-1.5 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Sistema en Vivo
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Panel de control y monitor activo para la gestión de raciones en tiempo real.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs uppercase tracking-wider border border-slate-700 transition flex items-center gap-2 shadow-sm">
                    <span>←</span> Volver al Dashboard
                </a>
            </div>
        </div>

        @php
            $buscar_dni = request('buscar_dni');

            // Métricas en tiempo real con diseño ejecutivo
            $totalPendientes = \App\Models\Consumo::whereDate('created_at', today())->where(function($q) { $q->where('estado', 'pendiente')->orWhereNull('estado'); })->count();
            $totalAtendidos = \App\Models\Consumo::whereDate('created_at', today())->where('estado', 'atendido')->count();
            $totalCancelados = \App\Models\Consumo::whereDate('created_at', today())->where('estado', 'cancelado')->count();

            try {
                $query = \App\Models\Consumo::with('estudiante')
                    ->whereDate('created_at', today())
                    ->where(function($q) {
                        $q->where('estado', 'pendiente')
                          ->orWhereNull('estado');
                    });

                if ($buscar_dni) {
                    $query->whereHas('estudiante', function($q) use ($buscar_dni) {
                        $q->where('dni', 'like', '%' . $buscar_dni . '%');
                    });
                }

                $pedidos_dashboard = $query->latest()->get();

            } catch (\Exception $e) {
                $pedidos_dashboard = \App\Models\Consumo::with('estudiante')
                    ->whereDate('created_at', today())
                    ->latest()
                    ->get();
            }
        @endphp

        <!-- Tarjetas Métricas Bento Grid (Otro Level) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <!-- Pendientes -->
            <div class="bg-slate-900/80 border border-amber-500/30 rounded-2xl p-5 flex items-center justify-between shadow-lg relative overflow-hidden group hover:border-amber-500/60 transition">
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-amber-500/5 rounded-full blur-xl group-hover:bg-amber-500/10 transition"></div>
                <div>
                    <p class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">Pendientes de Entrega</p>
                    <h3 class="text-3xl font-black text-white mt-1">{{ $totalPendientes }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-xl shadow-inner">
                    ⏳
                </div>
            </div>

            <!-- Atendidos -->
            <div class="bg-slate-900/80 border border-emerald-500/30 rounded-2xl p-5 flex items-center justify-between shadow-lg relative overflow-hidden group hover:border-emerald-500/60 transition">
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/5 rounded-full blur-xl group-hover:bg-emerald-500/10 transition"></div>
                <div>
                    <p class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Atendidos Hoy</p>
                    <h3 class="text-3xl font-black text-white mt-1">{{ $totalAtendidos }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl shadow-inner">
                    ✅
                </div>
            </div>

            <!-- Cancelados -->
            <div class="bg-slate-900/80 border border-rose-500/30 rounded-2xl p-5 flex items-center justify-between shadow-lg relative overflow-hidden group hover:border-rose-500/60 transition">
                <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-rose-500/5 rounded-full blur-xl group-hover:bg-rose-500/10 transition"></div>
                <div>
                    <p class="text-[11px] font-bold text-rose-400 uppercase tracking-wider">Cancelados / Reembolsos</p>
                    <h3 class="text-3xl font-black text-white mt-1">{{ $totalCancelados }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-xl shadow-inner">
                    ❌
                </div>
            </div>
        </div>

        <!-- Contenedor Principal de la Tabla -->
        <div class="bg-slate-900/60 rounded-3xl border border-slate-800 p-6 backdrop-blur-sm shadow-2xl">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                <h2 class="text-sm font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                    <span>📋</span> Cola de Pedidos Activos
                </h2>

                <!-- Buscador por DNI estilizado -->
                <form method="GET" action="{{ route('monitor.pedidos') }}" class="flex gap-2 w-full md:w-auto">
                    <input type="text" name="buscar_dni" value="{{ request('buscar_dni') }}" placeholder="🔍 Buscar DNI del estudiante..." 
                           class="w-full md:w-72 px-4 py-2 bg-slate-950 border border-slate-700 rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs font-mono text-slate-200 shadow-inner">
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow cursor-pointer">
                        Buscar
                    </button>
                    @if(request('buscar_dni'))
                        <a href="{{ route('monitor.pedidos') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs uppercase tracking-wider transition flex items-center">
                            Limpiar
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-800/80">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-950/80 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="p-4">DNI</th>
                            <th class="p-4">Estudiante</th>
                            <th class="p-4">Categoría</th>
                            <th class="p-4">Pedido</th>
                            <th class="p-4 text-right">Hora (Perú)</th>
                            <th class="p-4 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @forelse($pedidos_dashboard as $c)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-4 font-mono font-bold text-amber-400">
                                    {{ $c->estudiante->dni ?? $c->dni_estudiante ?? '---' }}
                                </td>
                                <td class="p-4 font-bold text-slate-200">
                                    {{ $c->estudiante->nombres ?? '' }} {{ $c->estudiante->apellidos ?? '' }}
                                </td>
                                <td class="p-4">
                                    <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-slate-300">
                                        {{ $c->categoria ?? ($c->insumo->unidad ?? 'General') }}
                                    </span>
                                </td>
                                <td class="p-4 font-semibold text-amber-300">
                                    {{ $c->pedido ?? ($c->comida ?? ($c->insumo->nombre ?? '---')) }}
                                </td>
                                <td class="p-4 text-right font-mono text-slate-400">
                                    {{ isset($c->created_at) ? \Carbon\Carbon::parse($c->created_at)->setTimezone('America/Lima')->format('h:i A') : ($c['hora'] ?? '---') }}
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Botón Entregar (PATCH seguro) -->
                                        <form action="{{ route('pedidos.entregar', $c->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-lg transition cursor-pointer flex items-center gap-1">
                                                <span>✓</span> Entregar
                                            </button>
                                        </form>

                                        <!-- Botón Cancelar -->
                                        <button type="button" 
                                                onclick="document.getElementById('modal-cancelar-{{ $c->id }}').classList.remove('hidden')"
                                                class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-lg transition cursor-pointer flex items-center gap-1">
                                            <span>✕</span> Cancelar
                                        </button>
                                    </div>

                                    <!-- Modal Profesional Estilo Dark -->
                                    <div id="modal-cancelar-{{ $c->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
                                        <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl max-w-md w-full p-6 text-left transform transition-all">
                                            <div class="flex items-center gap-4 mb-5">
                                                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500 text-xl shrink-0">
                                                    ⚠️
                                                </div>
                                                <div>
                                                    <h3 class="text-base font-bold text-white">¿Cancelar y reembolsar pedido?</h3>
                                                    <p class="text-xs text-slate-400 mt-0.5">Se devolverá el saldo al estudiante y la ración volverá a estar disponible.</p>
                                                </div>
                                            </div>

                                            <div class="flex justify-end gap-3 mt-6">
                                                <button type="button" 
                                                        onclick="document.getElementById('modal-cancelar-{{ $c->id }}').classList.add('hidden')"
                                                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition cursor-pointer">
                                                    Volver
                                                </button>

                                                <form action="{{ route('pedidos.cancelar', $c->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-lg transition cursor-pointer">
                                                        Sí, Cancelar y Reembolsar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center text-slate-500 text-xs font-semibold">
                                    @if(request('buscar_dni'))
                                        No se encontró ningún pedido pendiente para el DNI "{{ request('buscar_dni') }}".
                                    @else
                                        🎉 ¡Excelente! No hay pedidos pendientes por entregar en este momento.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('partials.alerta-cocina')
</body>
</html>
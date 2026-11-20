<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIA FoodCore - Historial y Reclamos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen p-6 md:p-10">

    <div class="max-w-5xl mx-auto space-y-8">
        
        <!-- Encabezado y Botón Volver -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-slate-900 border border-slate-800 p-8 rounded-3xl shadow-2xl relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-3">
                <span class="text-3xl">🔍</span>
                <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                    Historial de Consumos <span class="text-slate-500 font-light">/</span> <span class="text-amber-400 font-semibold text-lg">Últimos 30 días</span>
                </h1>
            </div>
            <a href="{{ route('dashboard') }}" class="relative z-10 inline-flex items-center gap-2.5 px-5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs uppercase tracking-wider rounded-2xl border border-slate-700 transition-all shadow-lg active:scale-95">
                <span>←</span> Volver al Dashboard
            </a>
        </div>

        <!-- Buscador por DNI -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl p-8 relative overflow-hidden">
            <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <form action="{{ route('historial.estudiante') }}" method="GET" class="flex flex-col md:flex-row gap-6 items-end relative z-10">
                <div class="flex-1 w-full space-y-3">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest">Ingrese el DNI del Estudiante:</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-500">
                            🪪
                        </span>
                        <input type="text" name="dni" value="{{ $dni ?? '' }}" placeholder="Ej. 12345678" required
                               class="w-full pl-12 pr-4 py-4 bg-slate-950 border border-slate-700 rounded-2xl text-xs font-bold text-white shadow-inner focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition placeholder:text-slate-600 font-mono">
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-950 font-black px-8 py-4 rounded-2xl text-xs uppercase tracking-widest shadow-xl shadow-amber-600/20 transition-all transform active:scale-95 cursor-pointer whitespace-nowrap">
                        <span>🔍 Buscar Historial</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Resultados de la Búsqueda -->
        @if(isset($estudiante))
            <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl p-8 space-y-6">
                <div class="pb-6 border-b border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-lg font-black text-white tracking-wide">
                            Estudiante: {{ $estudiante->nombres }} {{ $estudiante->apellidos }}
                        </h2>
                        <span class="text-xs font-mono text-slate-400 mt-1 inline-block">DNI: {{ $estudiante->dni }}</span>
                    </div>
                    <span class="text-[11px] font-black bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 px-4 py-2 rounded-xl shadow-sm">
                        Total de registros (30 días): {{ count($historial) }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-950/60 text-slate-400 text-[11px] font-black uppercase tracking-widest border-b border-slate-800">
                                <th class="p-4">Pedido / Plato</th>
                                <th class="p-4">Categoría</th>
                                <th class="p-4">Precio</th>
                                <th class="p-4">Estado</th>
                                <th class="p-4 text-right">Fecha y Hora</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-slate-300">
                            @forelse($historial as $h)
                                <tr class="hover:bg-slate-800/40 transition duration-150">
                                    <td class="p-4 font-extrabold text-amber-300">
                                        {{ $h->pedido ?? '---' }}
                                    </td>
                                    <td class="p-4">
                                        <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-lg bg-slate-800 text-slate-300 border border-slate-700">
                                            {{ $h->categoria ?? 'General' }}
                                        </span>
                                    </td>
                                    <td class="p-4 font-black text-emerald-400">
                                        S/. {{ number_format($h->precio ?? 0, 2) }}
                                    </td>
                                    <td class="p-4">
                                        @if(($h->estado ?? 'pendiente') === 'atendido')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm">
                                                ✓ Entregado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 shadow-sm">
                                                ⏳ Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-right font-mono text-xs text-slate-400">
                                        {{ \Carbon\Carbon::parse($h->created_at)->setTimezone('America/Lima')->format('d/m/Y h:i A') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-16 text-center text-slate-500 text-xs font-bold uppercase tracking-wider">
                                        Este estudiante no registra consumos en los últimos 30 días.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif(request('dni'))
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-300 p-6 rounded-3xl text-xs font-bold text-center backdrop-blur-md shadow-xl">
                ⚠️ No se encontró ningún estudiante registrado con el DNI ingresado.
            </div>
        @endif
    </div>

</body>
</html>
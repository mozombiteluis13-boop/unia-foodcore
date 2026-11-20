<div class="max-w-7xl mx-auto px-4 py-8" wire:poll.3s>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ENCABEZADO Y SALDO -->
    <div class="mb-8 border-b pb-4 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-emerald-700">UNIA FoodCore - Menú del Estudiante</h1>
            <p class="text-xs text-gray-500 font-semibold mt-1">
                🟢 Ingresa tu DNI, selecciona tus raciones y envía tu pedido
            </p>
        </div>

        <div class="flex items-center gap-3">
            <!-- INDICADOR DE SALDO -->
            @if(!is_null($saldoActual ?? null) || !empty($nombreEstudiante))
                <div class="bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-xl text-right">
                    @if(!empty($nombreEstudiante))
                        <span class="block text-[11px] font-extrabold text-emerald-900 uppercase">
                            {{ $nombreEstudiante }}
                        </span>
                    @endif

                    <span class="block text-[10px] font-extrabold text-emerald-600 uppercase mt-1">Tu Saldo Actual:</span>
                    <span class="text-base font-black text-emerald-800">
                        S/. {{ number_format($saldoActual ?? 0, 2) }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    @if(Cache::get('comedor_abierto', true))

        <!-- CAMPO DNI PRINCIPAL -->
        <div class="bg-white p-5 rounded-2xl border mb-6 shadow-sm">
            <label class="block text-xs font-extrabold text-gray-700 uppercase mb-2">
                🪪 DNI del Estudiante:
            </label>
            <div class="flex flex-col sm:flex-row gap-3 items-center">
                <input type="text"
                       wire:model.live="dni"
                       placeholder="Ingresa tu número de DNI aquí..."
                       maxlength="8"
                       class="w-full border rounded-xl p-3 text-base font-semibold border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-gray-50 focus:bg-white">

                @if(!empty($nombreEstudiante ?? null))
                    <div class="w-full sm:w-auto px-4 py-3 rounded-xl text-xs font-bold {{ str_contains($nombreEstudiante, '❌') ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }} whitespace-nowrap text-center">
                        👤 {{ $nombreEstudiante }}
                    </div>
                @endif
            </div>
        </div>

        <!-- MENSAJES DE ESTADO -->
        @if (session()->has('mensaje'))
            <div class="mb-6 p-4 bg-emerald-100 border border-emerald-400 text-emerald-800 rounded-xl text-sm font-bold">
                ✅ {{ session('mensaje') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-800 rounded-xl text-sm font-bold">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        @if (session()->has('exito'))
            <div class="mb-6 p-4 bg-emerald-200 border border-emerald-500 text-emerald-900 rounded-xl text-base font-extrabold text-center">
                🎉 {{ session('exito') }}
            </div>
        @endif

        <!-- MENÚ DISPONIBLE: COLUMNAS PARALELAS CON SEPARACIÓN INTELIGENTE -->
        <div class="mb-8">
            <h3 class="font-bold text-lg text-gray-800 mb-6 flex items-center gap-2">
                <span>📋</span> Menú Disponible en el Comedor
            </h3>

            @php
                $grupos = $insumos->groupBy(function($item) {
                    $cat = trim($item->categoria ?? $item->unidad ?? '');
                    $nombreLower = strtolower($item->nombre);

                    if(!empty($cat) && strcasecmp($cat, 'General') !== 0 && strcasecmp($cat, 'Porción') !== 0) {
                        return strtoupper($cat);
                    }

                    if(str_contains($nombreLower, 'camu') || str_contains($nombreLower, 'chicha') || str_contains($nombreLower, 'maracuya') || str_contains($nombreLower, 'jugo') || str_contains($nombreLower, 'refresco') || str_contains($nombreLower, 'kinua') || str_contains($nombreLower, 'quinua') || str_contains($nombreLower, 'te')) {
                        return 'BEBIDAS';
                    }
                    elseif(str_contains($nombreLower, 'pan') || str_contains($nombreLower, 'huevo') || str_contains($nombreLower, 'palta')) {
                        return 'PANES';
                    }
                    elseif(str_contains($nombreLower, 'huancaina') || str_contains($nombreLower, 'postre') || str_contains($nombreLower, 'gelatina') || str_contains($nombreLower, 'mazamorra') || str_contains($nombreLower, 'fruta')) {
                        return 'POSTRES';
                    }
                    else {
                        return 'PLATOS DE FONDO';
                    }
                });
            @endphp

            <div class="w-full overflow-x-auto pb-4">
                <div class="flex gap-6 items-start min-w-[700px]">
                    @forelse($grupos as $categoriaNombre => $items)
                        <div class="w-80 flex-shrink-0 bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-5 rounded-full bg-emerald-600"></span>
                                    <h4 class="text-base font-black uppercase tracking-wider text-gray-900">
                                        {{ $categoriaNombre }}
                                    </h4>
                                </div>
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                    {{ $items->count() }} {{ $items->count() == 1 ? 'opción' : 'opciones' }}
                                </span>
                            </div>

                            <div class="max-h-[450px] overflow-y-auto pr-2 flex flex-col gap-4">
                                @foreach($items as $item)
                                    <div class="border border-gray-200 rounded-xl p-4 bg-gradient-to-br from-gray-50/50 to-white shadow-sm flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-start mb-2">
                                                <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                                    {{ $item->turno ?? 'General' }}
                                                </span>

                                                @if($item->cantidad > 0)
                                                    <span class="inline-flex items-center text-xs font-bold text-green-600">
                                                        <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full animate-pulse"></span> Disponible
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center text-xs font-bold text-red-500">
                                                        ❌ Agotado
                                                    </span>
                                                @endif
                                            </div>

                                            <h3 class="text-lg font-extrabold text-gray-800 mb-1">{{ $item->nombre }}</h3>

                                            <div class="text-emerald-700 font-black text-sm mb-2">
                                                Precio: S/. {{ number_format($item->precio ?? 0, 2) }}
                                            </div>

                                            <p class="text-xs text-gray-600 mb-4">
                                                Raciones disponibles: <span class="font-extrabold text-emerald-600 text-base">{{ $item->cantidad }}</span>
                                            </p>
                                        </div>

                                        <div>
                                            @if($item->cantidad > 0)
                                                <button wire:click="agregarItem({{ $item->id }})"
                                                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-md flex items-center justify-center gap-2">
                                                    <span>🛒</span> Solicitar mi Ración
                                                </button>
                                            @else
                                                <button disabled class="w-full bg-gray-200 text-gray-400 font-bold py-2.5 px-4 rounded-xl text-xs cursor-not-allowed">
                                                    Sin Stock Disponible
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="w-full text-center py-10 text-gray-400 bg-white rounded-2xl border">
                            <p class="text-base font-semibold">No hay menús disponibles en este momento.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TABLA DE RESUMEN Y BOTÓN DE ENVIAR -->
        <div class="bg-white rounded-2xl border p-6 shadow-sm">
            <h2 class="text-base font-extrabold text-gray-800 mb-4 flex items-center gap-2">
                <span>📋</span> Resumen de tu Pedido Actual
            </h2>

            <div class="overflow-x-auto mb-6">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b text-gray-600 text-xs uppercase font-extrabold">
                            <th class="p-3">Ítem Seleccionado</th>
                            <th class="p-3">Categoría</th>
                            <th class="p-3">Precio</th>
                            <th class="p-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($seleccionados as $index => $sel)
                            <tr class="hover:bg-emerald-50/50 transition">
                                <td class="p-3 font-bold text-gray-800">{{ $sel['nombre'] }}</td>
                                <td class="p-3">
                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                        {{ $sel['categoria'] ?? $sel['unidad'] ?? 'General' }}
                                    </span>
                                </td>
                                <td class="p-3 font-black text-emerald-700">
                                    S/. {{ number_format($sel['precio'] ?? 0, 2) }}
                                </td>
                                <td class="p-3 text-right">
                                    <button wire:click="quitarItem({{ $index }})"
                                            class="text-red-500 hover:text-red-700 font-bold text-xs">
                                        ✕ Quitar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-gray-400 text-xs font-semibold">
                                    Aún no has seleccionado ningún plato o bebida.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(count($seleccionados) > 0)
                <div class="border-t pt-4 mb-6 flex justify-between items-center bg-gray-50 p-4 rounded-xl">
                    <span class="text-xs font-extrabold text-gray-600 uppercase">Total a Descontar de tu Saldo:</span>
                    <span class="text-lg font-black text-emerald-700">
                        S/. {{ number_format(collect($seleccionados)->sum('precio'), 2) }}
                    </span>
                </div>

                <button wire:click="enviarPedidoFinal"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-xl text-base transition shadow-lg flex items-center justify-center gap-3 uppercase tracking-wider">
                    <span>🚀</span> Enviar Pedido
                </button>
            @endif
        </div>

    @else
        <div class="bg-white border border-rose-100 rounded-3xl p-12 text-center shadow-sm max-w-xl mx-auto my-6">
            <div class="w-20 h-20 bg-rose-50 text-rose-600 rounded-3xl flex items-center justify-center text-4xl mx-auto mb-6 shadow-sm">
                🔒
            </div>
            <h2 class="text-2xl font-black text-gray-900 mb-2">El Comedor se encuentra CERRADO</h2>
            <p class="text-sm text-gray-500 font-medium leading-relaxed max-w-md mx-auto mb-6">
                En este momento el servicio de comedor no está recibiendo solicitudes. Por favor, intente en el próximo horario de atención.
            </p>
            <div class="inline-flex items-center gap-2 bg-rose-50 text-rose-700 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></span>
                Servicio no disponible
            </div>
        </div>
    @endif
</div>
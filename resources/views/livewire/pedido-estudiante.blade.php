<div class="max-w-7xl mx-auto px-4 py-8" wire:poll.3s>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ENCABEZADO Y SALDO -->
    <div class="mb-8 border-b pb-4 flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-emerald-700">UNIA FoodCore - Menú del Estudiante</h1>
            <p class="text-xs text-gray-500 font-semibold mt-1">
                🟢 Ingresa tu DNI, selecciona tus raciones o productos del Kiosco y envía tu pedido
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

    <!-- BARRA DE SECCIONES Y BOTÓN KIOSCO -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h3 class="font-bold text-lg text-gray-800 flex items-center gap-2">
            <span>📋</span>
            @if($kioscoAbierto)
                Kiosco Estudiantil ({{ $categoriaKioscoActiva }})
            @elseif($seccionVista === 'extras')
                Extras Disponibles en el Comedor
            @elseif(($turnoVisible ?? null) === 'Desayuno')
                Desayuno Disponible en el Comedor
            @else
                Menú Disponible en el Comedor
            @endif
        </h3>

        <div class="inline-flex flex-wrap items-center gap-2">
            @if(Cache::get('comedor_abierto', true) && !is_null($turnoVisible) && $kioscoAbierto)
                <button type="button"
                        wire:click="$set('kioscoAbierto', false)"
                        class="px-4 py-2 rounded-lg text-xs font-extrabold uppercase tracking-wider transition cursor-pointer bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200">
                    {{ $turnoVisible === 'Desayuno' ? '🍳 Ver Desayuno' : '🍽️ Ver Menú' }}
                </button>
            @endif

            <button type="button"
                    wire:click="abrirKiosco"
                    class="px-4 py-2 rounded-lg text-xs font-extrabold uppercase tracking-wider transition cursor-pointer {{ $kioscoAbierto ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-amber-500 hover:bg-amber-600 text-slate-950 shadow-sm' }}">
                🥤 Kiosco
            </button>
        </div>
    </div>

    @if($kioscoAbierto)
        <!-- SOLO AL PRESIONAR KIOSCO: 4 BOTONES DE CATEGORÍA Y LO DE LA CATEGORÍA ELEGIDA -->
        @php
            $iconosKiosco = [
                'Bebidas'   => '🥤',
                'Golosinas' => '🍬',
                'Snacks'    => '🥨',
                'Galletas'  => '🍪',
            ];
        @endphp

        <div class="bg-white rounded-2xl border p-6 shadow-sm mb-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                @foreach($categoriasKiosco as $catKiosco)
                    @php $activaKiosco = ($categoriaKioscoActiva === $catKiosco); @endphp
                    <button type="button"
                            wire:click="seleccionarCategoriaKiosco('{{ $catKiosco }}')"
                            class="p-3.5 rounded-xl border transition cursor-pointer flex items-center justify-center gap-2 text-xs font-black uppercase tracking-wider {{ $activaKiosco ? 'bg-amber-500 text-slate-950 border-amber-400 shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                        <span class="text-lg">{{ $iconosKiosco[$catKiosco] ?? '🛒' }}</span>
                        <span>{{ $catKiosco }}</span>
                    </button>
                @endforeach
            </div>

            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="text-xl">{{ $iconosKiosco[$categoriaKioscoActiva] ?? '🛒' }}</span>
                    <h4 class="text-base font-black uppercase tracking-wider text-gray-900">
                        {{ $categoriaKioscoActiva }}
                    </h4>
                </div>
                <span class="text-xs font-bold text-amber-800 bg-amber-100 px-3 py-1 rounded-full">
                    {{ $productosKiosco->count() }} {{ $productosKiosco->count() === 1 ? 'producto' : 'productos' }}
                </span>
            </div>

            @if($productosKiosco->isEmpty())
                <div class="text-center py-10 text-gray-400 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                    <p class="text-sm font-semibold">No hay productos disponibles en {{ $categoriaKioscoActiva }}.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($productosKiosco as $prod)
                        <div class="border border-gray-200 rounded-xl p-4 bg-gradient-to-br from-gray-50/60 to-white shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-center mb-2 gap-2">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                        {{ $prod->categoria }}
                                    </span>

                                    @if((int) $prod->stock > 0)
                                        <span class="inline-flex items-center text-xs font-bold text-green-600">
                                            <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full animate-pulse"></span> Disponible
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-xs font-bold text-red-500">
                                            ❌ Agotado
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-base font-extrabold text-gray-800 mb-1">{{ $prod->nombre }}</h3>
                                <div class="text-amber-700 font-black text-sm mb-2">
                                    Precio: S/. {{ number_format((float) $prod->precio, 2) }}
                                </div>
                                <p class="text-xs text-gray-600 mb-4">
                                    Stock disponible: <span class="font-extrabold text-amber-700 text-sm">{{ $prod->stock }}</span>
                                </p>
                            </div>

                            <div>
                                @if((int) $prod->stock > 0)
                                    <button type="button"
                                            wire:click="agregarProductoKiosco({{ $prod->id }})"
                                            class="w-full bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                                        <span>🛒</span> Agregar al Pedido
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
            @endif
        </div>
    @elseif(Cache::get('comedor_abierto', true) && !is_null($turnoVisible))
        <!-- MENÚ DISPONIBLE: PESTAÑAS DESAYUNO / MENÚ / EXTRAS + COLUMNAS PARALELAS -->
        <div class="mb-8">
            <div class="flex flex-wrap items-center justify-end gap-4 mb-6">
                <div class="inline-flex p-1 bg-gray-100 border border-gray-200 rounded-xl gap-1">
                    <button type="button"
                            wire:click="cambiarSeccionVista('menu')"
                            class="px-4 py-2 rounded-lg text-xs font-extrabold uppercase tracking-wider transition cursor-pointer {{ $seccionVista === 'menu' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ ($turnoVisible ?? null) === 'Desayuno' ? '🍳 Desayuno' : '🍽️ Menú' }}
                    </button>

                    @if(($turnoVisible ?? null) === 'Almuerzo')
                        <button type="button"
                                wire:click="cambiarSeccionVista('extras')"
                                class="px-4 py-2 rounded-lg text-xs font-extrabold uppercase tracking-wider transition cursor-pointer {{ $seccionVista === 'extras' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            ⚡ Extras
                        </button>
                    @endif
                </div>
            </div>

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
                                    <span class="w-2.5 h-5 rounded-full {{ $seccionVista === 'extras' ? 'bg-amber-500' : 'bg-emerald-600' }}"></span>
                                    <h4 class="text-base font-black uppercase tracking-wider text-gray-900">
                                        {{ $categoriaNombre }}
                                    </h4>
                                </div>
                                <span class="text-xs font-bold {{ $seccionVista === 'extras' ? 'text-amber-700 bg-amber-50' : 'text-emerald-700 bg-emerald-50' }} px-2.5 py-1 rounded-full">
                                    {{ $items->count() }} {{ $items->count() == 1 ? 'opción' : 'opciones' }}
                                </span>
                            </div>

                            <div class="max-h-[450px] overflow-y-auto pr-2 flex flex-col gap-4">
                                @foreach($items as $item)
                                    <div class="border border-gray-200 rounded-xl p-4 bg-gradient-to-br from-gray-50/50 to-white shadow-sm flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-start mb-2 gap-2">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                                        {{ $item->turno ?? 'General' }}
                                                    </span>
                                                    @if($item->es_extra ?? false)
                                                        <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                                            EXTRA
                                                        </span>
                                                    @endif
                                                </div>

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

                                            <div class="{{ ($item->es_extra ?? false) ? 'text-amber-700' : 'text-emerald-700' }} font-black text-sm mb-2">
                                                @if(($item->precio ?? 0) > 0)
                                                    Precio: S/. {{ number_format($item->precio, 2) }}
                                                @else
                                                    INCLUIDO (S/. 0.00)
                                                @endif
                                            </div>

                                            <p class="text-xs text-gray-600 mb-4">
                                                Raciones disponibles: <span class="font-extrabold text-emerald-600 text-base">{{ $item->cantidad }}</span>
                                            </p>
                                        </div>

                                        <div>
                                            @if($item->cantidad > 0)
                                                <button wire:click="agregarItem({{ $item->id }})"
                                                        class="w-full {{ ($item->es_extra ?? false) ? 'bg-amber-500 hover:bg-amber-600 text-slate-950' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }} font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-md flex items-center justify-center gap-2 cursor-pointer">
                                                    <span>🛒</span> {{ ($item->es_extra ?? false) ? 'Solicitar Extra' : 'Solicitar mi Ración' }}
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
                            <p class="text-base font-semibold">
                                {{ $seccionVista === 'extras' ? 'No hay extras disponibles en este momento.' : 'No hay menús disponibles en este momento.' }}
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        <div class="bg-white border border-rose-100 rounded-3xl p-12 text-center shadow-sm max-w-xl mx-auto my-6">
            <div class="w-20 h-20 bg-rose-50 text-rose-600 rounded-3xl flex items-center justify-center text-4xl mx-auto mb-6 shadow-sm">
                🔒
            </div>
            <h2 class="text-2xl font-black text-gray-900 mb-2">El Comedor se encuentra CERRADO</h2>
            <p class="text-sm text-gray-500 font-medium leading-relaxed max-w-md mx-auto mb-6">
                En este momento el servicio de comedor no está recibiendo solicitudes. Puedes presionar el botón <span class="font-bold text-amber-700">Kiosco</span> arriba para comprar con tu saldo.
            </p>
            <div class="inline-flex items-center gap-2 bg-rose-50 text-rose-700 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></span>
                Servicio no disponible
            </div>
        </div>
    @endif

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
                            <td class="p-3 font-bold text-gray-800">
                                {{ $sel['nombre'] }}
                                @if(!empty($sel['es_extra']))
                                    <span class="ml-1.5 text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                        EXTRA
                                    </span>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ ($sel['origen'] ?? 'comedor') === 'kiosco' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $sel['categoria'] ?? $sel['unidad'] ?? 'General' }}
                                </span>
                            </td>
                            <td class="p-3 font-black text-emerald-700">
                                S/. {{ number_format($sel['precio'] ?? 0, 2) }}
                            </td>
                            <td class="p-3 text-right">
                                <button wire:click="quitarItem({{ $index }})"
                                        class="text-red-500 hover:text-red-700 font-bold text-xs cursor-pointer">
                                    ✕ Quitar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-6 text-center text-gray-400 text-xs font-semibold">
                                Aún no has seleccionado ningún plato, bebida o producto del Kiosco.
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
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-xl text-base transition shadow-lg flex items-center justify-center gap-3 uppercase tracking-wider cursor-pointer">
                <span>🚀</span> Enviar Pedido
            </button>
        @endif
    </div>
</div>
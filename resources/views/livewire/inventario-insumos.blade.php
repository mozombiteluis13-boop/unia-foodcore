<div class="py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 font-sans">
    
    <!-- ENCABEZADO PRINCIPAL -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10 bg-slate-900 border border-slate-800 p-8 rounded-3xl shadow-2xl relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <span class="text-3xl">⚙️</span>
                <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                    UNIA <span class="text-amber-400">FoodCore</span> <span class="text-slate-600 font-light">/</span> <span class="text-slate-300 font-semibold text-lg">Gestión de Menú e Insumos</span>
                </h2>
            </div>
            <p class="text-xs text-emerald-400 font-bold flex items-center gap-2 mt-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span> 
                Panel Administrativo: Sincronización en tiempo real con el comedor universitario
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="relative z-10 inline-flex items-center gap-2.5 px-5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs uppercase tracking-wider rounded-2xl border border-slate-700 transition-all shadow-lg active:scale-95">
            <span>←</span> Volver al Dashboard
        </a>
    </div>

    <!-- MENSAJE DE ÉXITO -->
    @if (session()->has('exito'))
        <div class="mb-8 p-5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-2xl font-semibold text-xs shadow-xl flex items-center gap-4 backdrop-blur-md">
            <span class="text-xl p-2 bg-emerald-500/20 rounded-xl">✨</span> 
            <div>
                <strong class="block text-emerald-400 font-bold text-sm">¡Operación exitosa!</strong>
                <span>{{ session('exito') }}</span>
            </div>
        </div>
    @endif

    <!-- FORMULARIO DE REGISTRO / PUBLICACIÓN -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl p-8 md:p-10 mb-10 relative overflow-hidden">
        <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-800 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-2xl text-amber-400 font-bold shadow-inner">
                    🍽️
                </div>
                <div>
                    <h3 class="font-black text-base text-white tracking-wide uppercase">Publicar Nuevo Plato o Bebida</h3>
                    <p class="text-xs text-slate-400 mt-1">Configura los parámetros del menú diario para habilitar el consumo automático de los estudiantes.</p>
                </div>
            </div>

            @if($turno === 'Almuerzo')
                <label class="inline-flex items-center gap-3 px-4 py-3 rounded-2xl bg-slate-950 border {{ $esExtra ? 'border-amber-500/60 text-amber-300' : 'border-slate-700 text-slate-300' }} cursor-pointer select-none transition">
                    <input type="checkbox" wire:model.live="esExtra" class="rounded bg-slate-800 border-slate-600 text-amber-500 focus:ring-amber-500/30 w-4 h-4 cursor-pointer">
                    <span class="text-xs font-black uppercase tracking-wider">⚡ Modalidad Extra (Plato / Bebida)</span>
                </label>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 items-start relative z-10">
            
            <!-- 1. Selector de Turno -->
            <div class="space-y-2">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Turno</label>
                <select wire:model.live="turno" class="w-full bg-slate-950 border border-slate-700 rounded-2xl text-xs font-bold p-4 text-white shadow-inner focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition cursor-pointer">
                    <option value="Desayuno" class="bg-slate-900 text-white">☀️ Desayuno</option>
                    <option value="Almuerzo" class="bg-slate-900 text-white">🌙 Almuerzo</option>
                </select>
            </div>

            <!-- 2. Selector de Categoría Dinámica -->
            <div class="space-y-2">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Categoría</label>
                <select wire:model.live="categoria" class="w-full bg-slate-950 border border-slate-700 rounded-2xl text-xs font-bold p-4 text-white shadow-inner focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition cursor-pointer">
                    <option value="" class="bg-slate-900 text-slate-400">Seleccione...</option>
                    @if($turno === 'Desayuno')
                        <option value="BEBIDAS" class="bg-slate-900 text-white">🥤 Bebidas</option>
                        <option value="PANES" class="bg-slate-900 text-white">🥪 Panes</option>
                    @elseif($esExtra)
                        <option value="Plato" class="bg-slate-900 text-white">🍽️ Plato Extra</option>
                        <option value="Bebida" class="bg-slate-900 text-white">🥤 Bebida Extra</option>
                    @else
                        <option value="PLATOS DE FONDO" class="bg-slate-900 text-white">🍲 Plato de Fondo</option>
                        <option value="BEBIDAS" class="bg-slate-900 text-white">🥤 Bebidas</option>
                        <option value="POSTRES" class="bg-slate-900 text-white">🍮 Postres</option>
                    @endif
                </select>
                @error('categoria') <span class="block text-[11px] text-rose-400 font-bold mt-1.5 flex items-center gap-1">⚠️ {{ $message }}</span> @enderror
            </div>

            <!-- 3. Nombre -->
            <div class="space-y-2">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Nombre del Ítem</label>
                <input type="text" wire:model="nombre" placeholder="{{ ($turno === 'Almuerzo' && $esExtra) ? 'Ej. Chicharrón / Chicha Extra' : 'Ej. Seco de Pollo' }}" class="w-full bg-slate-950 border border-slate-700 rounded-2xl text-xs font-bold p-4 text-white shadow-inner focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition placeholder:text-slate-500">
                @error('nombre') <span class="block text-[11px] text-rose-400 font-bold mt-1.5 flex items-center gap-1">⚠️ {{ $message }}</span> @enderror
            </div>

            <!-- 4. Raciones -->
            <div class="space-y-2">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">Stock / Raciones</label>
                <input type="number" wire:model="cantidad" min="1" placeholder="Ej. 120" class="w-full bg-slate-950 border border-slate-700 rounded-2xl text-xs font-bold p-4 text-white shadow-inner focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition placeholder:text-slate-500">
                @error('cantidad') <span class="block text-[11px] text-rose-400 font-bold mt-1.5 flex items-center gap-1">⚠️ {{ $message }}</span> @enderror
            </div>

            <!-- 5. Precio Unitario -->
            @if(($turno === 'Almuerzo' && $esExtra) || $turno === 'Desayuno' || ($turno === 'Almuerzo' && stripos($categoria, 'Fondo') !== false))
                <div class="space-y-2">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest">
                        Precio Unitario {{ ($turno === 'Almuerzo' && $esExtra) ? '(Obligatorio)' : '' }}
                    </label>
                    <input type="number" step="0.50" wire:model="precio" min="{{ ($turno === 'Almuerzo' && $esExtra) ? '0.10' : '0' }}" placeholder="Ej. 3.50" class="w-full bg-slate-950 border border-slate-700 rounded-2xl text-xs font-bold p-4 text-white shadow-inner focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition placeholder:text-slate-500">
                    @error('precio') <span class="block text-[11px] text-rose-400 font-bold mt-1.5 flex items-center gap-1">⚠️ {{ $message }}</span> @enderror
                </div>
            @endif
        </div>

        <!-- Botón de Envío -->
        <div class="mt-10 pt-6 border-t border-slate-800 flex justify-end relative z-10">
            <button wire:click="guardarInsumo" class="inline-flex items-center gap-3 bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-950 font-black py-4 px-10 rounded-2xl text-xs uppercase tracking-widest shadow-xl shadow-amber-600/20 transition-all transform active:scale-95 cursor-pointer">
                <span>+ Publicar en el Menú</span>
            </button>
        </div>
    </div>

    <!-- PESTAÑAS MENÚ / EXTRAS JUSTO ARRIBA DEL LISTADO -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="inline-flex p-1.5 bg-slate-900 border border-slate-800 rounded-2xl shadow-lg gap-2">
            <button type="button"
                    wire:click="cambiarSeccionVista('menu')"
                    class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition cursor-pointer {{ $seccionVista === 'menu' ? 'bg-amber-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
                🍽️ Menú
            </button>

            @if($turno === 'Almuerzo')
                <button type="button"
                        wire:click="cambiarSeccionVista('extras')"
                        class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition cursor-pointer {{ $seccionVista === 'extras' ? 'bg-amber-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white' }}">
                    ⚡ Extras
                </button>
            @endif
        </div>

        <span class="text-xs font-bold text-slate-400">
            Mostrando {{ $seccionVista === 'extras' ? 'Extras de Almuerzo' : 'Menú Regular (' . $turno . ')' }}
        </span>
    </div>

    <!-- SECCIÓN DE MENÚ DISPONIBLE -->
    @php
        $grupos = $insumos->groupBy(function($item) {
            $cat = trim($item->categoria ?? '');
            $nombreLower = strtolower($item->nombre);

            if (!empty($cat) && strcasecmp($cat, 'General') !== 0) {
                return mb_strtoupper($cat, 'UTF-8');
            }

            if (str_contains($nombreLower, 'kinua') || str_contains($nombreLower, 'quinua') || str_contains($nombreLower, 'maca') || str_contains($nombreLower, 'chapo') || str_contains($nombreLower, 'camu') || str_contains($nombreLower, 'chicha') || str_contains($nombreLower, 'jugo') || str_contains($nombreLower, 'refresco') || str_contains($nombreLower, 'te') || str_contains($nombreLower, 'cafe')) {
                return 'BEBIDAS';
            }
            elseif (str_contains($nombreLower, 'pan') || str_contains($nombreLower, 'huevo') || str_contains($nombreLower, 'palta')) {
                return 'PANES';
            }
            elseif (str_contains($nombreLower, 'postre') || str_contains($nombreLower, 'gelatina') || str_contains($nombreLower, 'mazamorra') || str_contains($nombreLower, 'fruta') || str_contains($nombreLower, 'huancaina')) {
                return 'POSTRES';
            }
            else {
                return 'PLATOS DE FONDO';
            }
        });
    @endphp

    @if($grupos->count() > 0)
        <!-- CONTENEDOR CON SCROLL HORIZONTAL PRO -->
        <div class="w-full overflow-x-auto pb-8">
            <div class="flex gap-6 items-start min-w-[750px]">
                @foreach($grupos as $categoriaNombre => $items)
                    <!-- Tarjeta contenedora de categoría -->
                    <div class="w-80 flex-shrink-0 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl">
                        
                        <!-- Encabezado de la categoría -->
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-7 rounded-full bg-gradient-to-b from-amber-400 to-amber-600 shadow-sm shadow-amber-500/50"></span>
                                <h4 class="text-sm font-black uppercase tracking-wider text-white">
                                    {{ $categoriaNombre }}
                                </h4>
                            </div>
                            <span class="text-[10px] font-black text-amber-400 bg-amber-500/10 border border-amber-500/20 px-3 py-1.5 rounded-xl">
                                {{ $items->count() }} {{ $items->count() == 1 ? 'ítem' : 'ítems' }}
                            </span>
                        </div>
                        
                        <!-- LISTA CON SCROLL VERTICAL INDEPENDIENTE -->
                        <div class="max-h-[480px] overflow-y-auto pr-1 flex flex-col gap-3.5">
                            @foreach($items as $item)
                                <div class="border border-slate-800 rounded-2xl p-4.5 bg-slate-950/70 hover:bg-slate-800/40 transition-all shadow-md flex justify-between items-center relative overflow-hidden group">
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-md bg-slate-800 text-slate-300 border border-slate-700">
                                                {{ $item->turno ?? 'General' }}
                                            </span>
                                            @if($item->es_extra ?? false)
                                                <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-md bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                                    EXTRA
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <h5 class="text-sm font-extrabold text-slate-100 group-hover:text-amber-300 transition-colors">{{ $item->nombre }}</h5>
                                        
                                        <div class="text-amber-500 font-black text-sm mb-2">
                                            @if(($item->precio ?? 0) > 0)
                                                S/. {{ number_format($item->precio, 2) }}
                                            @else
                                                INCLUIDO
                                            @endif
                                        </div>
                                        
                                        <p class="text-[11px] text-slate-400 font-medium">
                                            Stock: <span class="font-black text-emerald-400">{{ $item->cantidad }}</span> raciones
                                        </p>
                                    </div>

                                    <div class="flex flex-col items-end gap-3">
                                        @if($item->cantidad > 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm">
                                                <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-400 rounded-full animate-pulse"></span> ACTIVO
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-500/10 text-rose-400 border border-rose-500/20 shadow-sm">
                                                ❌ AGOTADO
                                            </span>
                                        @endif

                                        <div x-data="{ confirmingDelete: false }" class="relative inline-block w-full">
                                            <!-- Botón principal de eliminar -->
                                            <button type="button"
                                                    x-show="!confirmingDelete"
                                                    @click="confirmingDelete = true"
                                                    class="w-full text-xs text-rose-400 hover:text-rose-300 font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 mt-2 bg-rose-500/10 hover:bg-rose-500/20 px-3 py-2 rounded-xl border border-rose-500/20">
                                                🗑️ Eliminar
                                            </button>

                                            <!-- Mini tarjeta de confirmación con diseño profesional -->
                                            <div x-show="confirmingDelete" 
                                                x-cloak
                                                class="mt-2 bg-slate-900 border border-rose-500/40 p-3 rounded-2xl shadow-2xl flex flex-col gap-2 text-center">
                                                <p class="text-[11px] font-bold text-gray-200">
                                                    ¿Eliminar este elemento?
                                                </p>
                                                <div class="flex gap-2">
                                                    <button type="button"
                                                            @click="confirmingDelete = false"
                                                            class="flex-1 bg-slate-800 hover:bg-slate-700 text-gray-300 text-[10px] font-bold py-1.5 px-3 rounded-xl transition cursor-pointer">
                                                        Cancelar
                                                    </button>
                                                    <button type="button"
                                                            wire:click="eliminarInsumo({{ $item->id }})"
                                                            @click="confirmingDelete = false"
                                                            class="flex-1 bg-rose-600 hover:bg-rose-500 text-white text-[10px] font-bold py-1.5 px-3 rounded-xl transition shadow-md cursor-pointer">
                                                        Sí, eliminar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="w-full text-center py-20 bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl">
            <span class="text-5xl">🍽️</span>
            <p class="text-sm font-black text-slate-300 mt-4 uppercase tracking-wider">
                {{ $seccionVista === 'extras' ? 'No hay extras publicados para este turno' : 'No hay raciones ni bebidas publicadas para hoy' }}
            </p>
            <p class="text-xs text-slate-500 mt-1">Usa el formulario superior para comenzar a registrar el menú del comedor.</p>
        </div>
    @endif

</div>
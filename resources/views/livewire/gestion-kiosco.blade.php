<div class="min-h-screen bg-slate-950 text-slate-100 p-5 lg:p-8 font-sans selection:bg-indigo-500 selection:text-white">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ENCABEZADO Y NAVEGACIÓN -->
    <header class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 pb-5 border-b border-slate-800/80 gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-2xl shadow-xl">
                🥤
            </div>
            <div>
                <h1 class="text-2xl lg:text-3xl font-black tracking-tight text-white">
                    Módulo <span class="bg-gradient-to-r from-amber-400 via-orange-400 to-rose-400 bg-clip-text text-transparent">Kiosco</span>
                </h1>
                <p class="text-xs text-slate-400 font-medium mt-0.5">Gestión independiente de Bebidas, Golosinas, Snacks y Galletas</p>
            </div>
        </div>

        <nav class="flex flex-wrap items-center gap-2.5 bg-slate-900/90 p-2 rounded-3xl border border-slate-800 shadow-2xl w-full xl:w-auto">
            <a href="{{ route('dashboard') }}" class="flex-1 xl:flex-none px-5 py-3 rounded-2xl text-slate-300 hover:text-white hover:bg-slate-800/80 font-bold text-xs uppercase tracking-widest transition-all text-center">Dashboard</a>
            <a href="/inventario" class="flex-1 xl:flex-none px-5 py-3 rounded-2xl text-slate-300 hover:text-white hover:bg-slate-800/80 font-bold text-xs uppercase tracking-widest transition-all text-center">Menú</a>
            <a href="/estudiantes" class="flex-1 xl:flex-none px-5 py-3 rounded-2xl text-slate-300 hover:text-white hover:bg-slate-800/80 font-bold text-xs uppercase tracking-widest transition-all text-center">Estudiantes</a>
            <span class="flex-1 xl:flex-none px-5 py-3 rounded-2xl bg-amber-500 text-slate-950 font-black text-xs uppercase tracking-widest shadow-lg shadow-amber-500/30 text-center">Kiosco</span>
        </nav>
    </header>

    @if (session()->has('kiosco_ok'))
        <div class="mb-5 p-3.5 bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 rounded-2xl text-sm font-bold">
            ✅ {{ session('kiosco_ok') }}
        </div>
    @endif

    <!-- 4 BOTONES PRINCIPALES DE CATEGORÍA (PANTALLA COMPLETA POR CATEGORÍA) -->
    @php
        $iconos = [
            'Bebidas'   => '🥤',
            'Golosinas' => '🍬',
            'Snacks'    => '🥨',
            'Galletas'  => '🍪',
        ];
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 mb-6">
        @foreach ($categorias as $cat)
            @php $activa = ($categoriaActiva === $cat); @endphp
            <button type="button"
                    wire:click="seleccionarCategoria('{{ $cat }}')"
                    class="p-4 rounded-2xl border transition-all cursor-pointer flex items-center justify-between {{ $activa ? 'bg-amber-500 text-slate-950 border-amber-400 shadow-lg shadow-amber-500/25 font-black' : 'bg-slate-900/90 text-slate-200 border-slate-800 hover:border-slate-700 font-bold' }}">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ $iconos[$cat] ?? '🛒' }}</span>
                    <span class="text-sm uppercase tracking-wider">{{ $cat }}</span>
                </div>
                <span class="px-2.5 py-1 rounded-xl text-xs font-mono font-black {{ $activa ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-800 text-amber-400' }}">
                    {{ $conteos[$cat] ?? 0 }}
                </span>
            </button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- FORMULARIO RÁPIDO DE REGISTRO -->
        <div class="lg:col-span-4 bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-2xl h-fit">
            <h2 class="text-lg font-black text-white mb-1">Nuevo Producto</h2>
            <p class="text-xs text-slate-400 mb-4">Agrega un producto a la categoría seleccionada o cámbiala aquí.</p>

            <form wire:submit.prevent="guardarProducto" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Categoría</label>
                    <select wire:model="categoria"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('categoria') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Nombre del producto</label>
                    <input type="text"
                           wire:model="nombre"
                           placeholder="Ej. Inca Kola 500ml, Sporade, Sublime, Oreo"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    @error('nombre') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Precio (S/.)</label>
                        <input type="number"
                               step="0.10"
                               min="0.10"
                               wire:model="precio"
                               placeholder="2.50"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm font-mono focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        @error('precio') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Stock inicial</label>
                        <input type="number"
                               min="0"
                               wire:model="stock"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm font-mono focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        @error('stock') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-4 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-widest shadow-lg shadow-amber-500/25 transition cursor-pointer">
                    Guardar en Kiosco
                </button>
            </form>
        </div>

        <!-- PANTALLA COMPLETA DE LA CATEGORÍA ACTIVA -->
        <div class="lg:col-span-8 bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ $iconos[$categoriaActiva] ?? '🛒' }}</span>
                    <div>
                        <h2 class="text-xl font-black text-white">{{ $categoriaActiva }}</h2>
                        <p class="text-xs text-slate-400">Mostrando únicamente los productos de {{ $categoriaActiva }}</p>
                    </div>
                </div>
                <span class="text-xs font-mono text-amber-400 bg-amber-500/10 border border-amber-500/20 px-3 py-1 rounded-full">
                    {{ $productos->count() }} {{ $productos->count() === 1 ? 'producto' : 'productos' }}
                </span>
            </div>

            @if ($productos->isEmpty())
                <div class="py-14 text-center border border-dashed border-slate-800 rounded-2xl">
                    <p class="text-slate-400 text-sm font-semibold">Aún no hay productos registrados en <span class="text-amber-400">{{ $categoriaActiva }}</span>.</p>
                    <p class="text-xs text-slate-500 mt-1">Usa el formulario de la izquierda para agregar el primero.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($productos as $prod)
                        <div class="bg-slate-950/90 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between gap-4 hover:border-amber-500/40 transition">
                            <div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[11px] font-mono uppercase text-amber-400 bg-amber-500/10 px-2.5 py-0.5 rounded-lg">
                                        {{ $prod->categoria }}
                                    </span>
                                    <span class="text-xs font-mono font-bold {{ $prod->stock > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                        Stock: {{ $prod->stock }}
                                    </span>
                                </div>
                                <h3 class="text-base font-extrabold text-white mt-2.5">{{ $prod->nombre }}</h3>
                                <p class="text-lg font-black text-cyan-400 font-mono mt-1">
                                    S/. {{ number_format((float) $prod->precio, 2) }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2 pt-3 border-t border-slate-800/80">
                                <button type="button"
                                        wire:click="sumarStock({{ $prod->id }}, 5)"
                                        class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-400 font-bold text-xs uppercase transition cursor-pointer">
                                    +5 Stock
                                </button>
                                <button type="button"
                                        wire:click="eliminarProducto({{ $prod->id }})"
                                        class="py-2 px-3 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 font-bold text-xs uppercase transition cursor-pointer">
                                    Borrar
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
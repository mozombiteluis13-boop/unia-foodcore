<div class="max-w-7xl mx-auto px-4 py-10 space-y-8 bg-slate-950 text-slate-100 min-h-screen">
    
    <!-- TARJETAS DE RESUMEN RÁPIDO -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl flex items-center gap-5 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="w-14 h-14 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-xl text-amber-400 font-bold shadow-inner">
                👥
            </div>
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Total Registrados</p>
                <h4 class="text-2xl font-black text-white mt-1">{{ count($estudiantes) }}</h4>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl flex items-center gap-5 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-xl text-emerald-400 font-bold shadow-inner">
                ✅
            </div>
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Estudiantes Activos</p>
                <h4 class="text-2xl font-black text-white mt-1">{{ collect($estudiantes)->where('activo', true)->count() }}</h4>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl flex items-center gap-5 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-rose-500/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="w-14 h-14 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-xl text-rose-400 font-bold shadow-inner">
                ⏳
            </div>
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Pendientes / Inactivos</p>
                <h4 class="text-2xl font-black text-white mt-1">{{ collect($estudiantes)->where('activo', false)->count() }}</h4>
            </div>
        </div>
    </div>

    <!-- TABLA PRINCIPAL Y CONTENEDOR -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden backdrop-blur-xl">
        
        <!-- Cabecera de la sección -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 p-6 md:p-8 border-b border-slate-800 bg-slate-900/50">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-xl text-amber-400 font-bold shadow-inner">
                    📋
                </div>
                <div>
                    <h2 class="text-xl font-black text-white tracking-tight">Gestión de Solicitudes y Estudiantes</h2>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Controla las cuentas, saldos y accesos del comedor universitario.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <!-- Botón Volver al Inicio -->
                <a href="/" class="inline-flex items-center gap-2 px-5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs uppercase tracking-wider rounded-2xl border border-slate-700 transition-all shadow-lg active:scale-95">
                    <span>←</span>
                    <span>Volver al Inicio</span>
                </a>

                <!-- Buscador por DNI -->
                <div class="relative flex-1 md:w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                        🔍
                    </span>
                    <input type="text" 
                           wire:model.live="busqueda" 
                           placeholder="Buscar alumno por DNI..." 
                           class="w-full pl-11 pr-4 py-3 bg-slate-950 border border-slate-700 rounded-2xl text-xs font-bold text-white shadow-inner focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition placeholder:text-slate-500">
                </div>
            </div>
        </div>

        <!-- Tabla Estilizada -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-950/60 text-slate-400 text-[11px] font-black uppercase tracking-widest border-b border-slate-800">
                        <th class="py-4 px-6">DNI</th>
                        <th class="py-4 px-6">Nombres y Apellidos</th>
                        <th class="py-4 px-6">Carrera</th>
                        <th class="py-4 px-6 text-center">Saldo Actual</th>
                        <th class="py-4 px-6">Estado</th>
                        <th class="py-4 px-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-sm font-medium text-slate-300">
                    @forelse($estudiantes as $estudiante)
                        <tr class="hover:bg-slate-800/40 transition duration-150">
                            <!-- DNI -->
                            <td class="py-4 px-6 font-bold text-white">{{ $estudiante->dni }}</td>
                            
                            <!-- Nombres -->
                            <td class="py-4 px-6 text-slate-200 font-semibold">{{ $estudiante->nombres }} {{ $estudiante->apellidos }}</td>
                            
                            <!-- Carrera -->
                            <td class="py-4 px-6 text-slate-400 text-xs">{{ $estudiante->carrera->nombre ?? 'Ingeniería de Software con IA' }}</td>
                            
                            <!-- Saldo Actual -->
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-xs font-extrabold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm">
                                    S/. {{ number_format($estudiante->saldo ?? 0, 2) }}
                                </span>
                            </td>

                            <!-- Estado -->
                            <td class="py-4 px-6">
                                @if($estudiante->activo)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Pendiente
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="py-4 px-6 text-center">
                                <div class="flex items-center justify-center gap-2.5">
                                    <button wire:click="cambiarEstado({{ $estudiante->id }})" 
                                            class="px-4 py-2 {{ $estudiante->activo ? 'bg-amber-600 hover:bg-amber-500 text-slate-950 font-black' : 'bg-emerald-600 hover:bg-emerald-500 text-white font-bold' }} text-xs rounded-xl shadow-md transition transform active:scale-95 cursor-pointer">
                                        {{ $estudiante->activo ? 'Desactivar' : 'Aprobar' }}
                                    </button>

                                    <div x-data="{ confirmingDelete: false }" class="relative inline-block">
                                        <!-- Botón principal de eliminar -->
                                        <button type="button"
                                                x-show="!confirmingDelete"
                                                @click="confirmingDelete = true"
                                                class="px-4 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 font-bold text-xs rounded-xl shadow-md transition transform active:scale-95 cursor-pointer">
                                            Eliminar
                                        </button>

                                        <!-- Mini tarjeta de confirmación profesional -->
                                        <div x-show="confirmingDelete" 
                                            x-cloak
                                            class="absolute right-0 z-50 mt-1 w-48 bg-slate-900 border border-rose-500/40 p-3 rounded-2xl shadow-2xl flex flex-col gap-2 text-center">
                                            <p class="text-[11px] font-bold text-gray-200">
                                                ¿Deseas eliminar este estudiante?
                                            </p>
                                            <div class="flex gap-2">
                                                <button type="button"
                                                        @click="confirmingDelete = false"
                                                        class="flex-1 bg-slate-800 hover:bg-slate-700 text-gray-300 text-[10px] font-bold py-1.5 px-2 rounded-xl transition cursor-pointer">
                                                    Cancelar
                                                </button>
                                                <button type="button"
                                                        wire:click="eliminar({{ $estudiante->id }})"
                                                        @click="confirmingDelete = false"
                                                        class="flex-1 bg-rose-600 hover:bg-rose-500 text-white text-[10px] font-bold py-1.5 px-2 rounded-xl transition shadow-md cursor-pointer">
                                                    Sí, eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center text-slate-500 text-xs font-bold uppercase tracking-wider">
                                No se encontraron estudiantes registrados con ese DNI.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
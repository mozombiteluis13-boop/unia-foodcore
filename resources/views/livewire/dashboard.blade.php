<div class="min-h-screen bg-slate-950 text-slate-100 p-6 lg:p-12 font-sans selection:bg-indigo-500 selection:text-white">
    <script src="https://cdn.tailwindcss.com"></script>

    @php
        $horaActual = (int) now()->format('Hi');
        $esDesayuno = ($horaActual >= 700 && $horaActual <= 930);
        $esAlmuerzo = ($horaActual >= 1130 && $horaActual <= 1500);
    @endphp

    <!-- HEADER EJECUTIVO GRANDE Y POTENTE -->
    <header class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-10 pb-8 border-b border-slate-800/80 gap-6">
        <div class="flex items-center gap-5">
            <div class="relative">
                <div class="absolute -inset-1 rounded-3xl bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-500 opacity-75 blur-md"></div>
                <div class="relative w-18 h-18 rounded-3xl bg-slate-900 border border-slate-800 flex items-center justify-center text-3xl shadow-2xl">
                    ⚡
                </div>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-4xl font-black tracking-tight text-white">UNIA <span class="bg-gradient-to-r from-indigo-400 via-cyan-400 to-teal-300 bg-clip-text text-transparent">FoodCore</span></h1>
                    <span class="px-3.5 py-1 rounded-lg text-xs font-mono font-black bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-inner">ENTERPRISE v4.0</span>
                </div>
                <p class="text-sm text-slate-400 font-medium mt-1">Plataforma de Control Avanzado y Gestión de Asistencia para el Comedor Universitario</p>
            </div>
        </div>

        <!-- BARRA DE NAVEGACIÓN SUPERIOR (BOTONES GRANDES Y VISIBLES) -->
        <nav class="flex flex-wrap items-center gap-3 bg-slate-900/90 p-2.5 rounded-3xl border border-slate-800 backdrop-blur-2xl shadow-2xl w-full xl:w-auto">
            <a href="/" class="flex-1 xl:flex-none px-6 py-3.5 rounded-2xl bg-indigo-600 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-indigo-600/40 transition-all text-center">Dashboard</a>
            <a href="/inventario" class="flex-1 xl:flex-none px-6 py-3.5 rounded-2xl text-slate-300 hover:text-white hover:bg-slate-800/80 font-bold text-xs uppercase tracking-widest transition-all text-center">Menú</a>
            <a href="/estudiantes" class="flex-1 xl:flex-none px-6 py-3.5 rounded-2xl text-slate-300 hover:text-white hover:bg-slate-800/80 font-bold text-xs uppercase tracking-widest transition-all text-center">Estudiantes</a>
            <a href="{{ route('historial.estudiante') }}" class="flex-1 xl:flex-none px-6 py-3.5 rounded-2xl text-slate-300 hover:text-white hover:bg-slate-800/80 font-bold text-xs uppercase tracking-widest transition-all text-center">Reclamos</a>
            <button type="button"
                    wire:click="abrirModal"
                    class="flex-1 xl:flex-none px-6 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-emerald-950/50 transition-all text-center cursor-pointer">
                💳 Recargar Saldo
            </button>
        </nav>
        <nav>
            @auth
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="px-12 py-3 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-xs font-bold uppercase tracking-wider rounded-xl transition cursor-pointer flex items-center gap-1.5">
                        <span>Salir</span>
                    </button>
                </form>
            @endauth
        </nav>
    </header>

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 rounded-2xl text-sm font-bold">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-500/15 border border-rose-500/40 text-rose-300 rounded-2xl text-sm font-bold">
            ⚠️ {{ $errors->first() }}
        </div>
    @endif

    <!-- GRID PRINCIPAL -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- MÓDULO 1: ESTADO DEL COMEDOR (6 Columnas) -->
        <div class="lg:col-span-6 bg-gradient-to-b from-slate-900 to-slate-950 p-8 lg:p-10 rounded-3xl border border-slate-800/80 shadow-2xl flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-80 h-80 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none group-hover:bg-indigo-600/20 transition-all duration-700"></div>

            <div>
                <div class="flex justify-between items-center mb-8">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="text-xs font-mono uppercase tracking-widest text-slate-400 font-bold">Estado del Servidor</span>
                    </div>

                    @if($esDesayuno)
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span> Turno Desayuno
                        </span>
                    @elseif($esAlmuerzo)
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span> Turno Almuerzo
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span> Fuera de Horario
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-6 my-6">
                    <div class="w-20 h-20 rounded-3xl bg-slate-800/90 border border-slate-700/60 flex items-center justify-center text-4xl shadow-inner">
                        {!! $comedorAbierto ? '🟢' : '🔒' !!}
                    </div>
                    <div>
                        <p class="text-xs font-mono uppercase tracking-widest text-slate-400 font-semibold">Servicio en Línea</p>
                        <h2 class="text-3xl lg:text-4xl font-black tracking-tight mt-1 {{ $comedorAbierto ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $comedorAbierto ? 'COMEDOR ABIERTO' : 'SISTEMA BLOQUEADO' }}
                        </h2>
                    </div>
                </div>

                <p class="text-sm text-slate-400 mt-4 leading-relaxed font-medium">
                    {{ $comedorAbierto ? 'Las validaciones de DNI y control de asistencia están activas en tiempo real para todos los estudiantes.' : 'El sistema se encuentra con el acceso cerrado por cumplimiento de la programación horaria.' }}
                </p>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-800/80">
                <button wire:click="toggleEstadoComedor" 
                        class="w-full py-5 px-6 rounded-2xl font-black text-xs lg:text-sm uppercase tracking-widest transition-all duration-300 flex items-center justify-center gap-3 cursor-pointer shadow-2xl {{ $comedorAbierto ? 'bg-rose-600 hover:bg-rose-500 text-white shadow-rose-950/60' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-950/60' }}">
                    <span>{{ $comedorAbierto ? 'Cerrar Acceso al Comedor' : 'Abrir Acceso al Comedor' }}</span>
                    <span class="text-lg">⚡</span>
                </button>
            </div>
        </div>

        <!-- MÓDULO 2 & 3: ESTADÍSTICAS (6 Columnas divididas) -->
        <div class="lg:col-span-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
            
            <!-- Estudiantes -->
            <div class="bg-slate-900/90 backdrop-blur-xl p-8 rounded-3xl border border-slate-800 shadow-2xl flex flex-col justify-between hover:border-slate-700 transition-all group">
                <div>
                    <div class="flex justify-between items-center mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            👥
                        </div>
                        <span class="text-[11px] font-mono uppercase tracking-wider text-emerald-400 bg-emerald-500/10 px-3.5 py-1 rounded-full border border-emerald-500/20">Activos</span>
                    </div>
                    <p class="text-xs font-mono uppercase tracking-wider text-slate-400 font-bold">Estudiantes</p>
                    <h2 class="text-5xl font-black text-white mt-2 tracking-tight">{{ $estudiantesActivos }}</h2>
                </div>
                <div class="mt-10">
                    <a href="/estudiantes" class="w-full py-3.5 px-5 rounded-2xl bg-slate-800/90 hover:bg-slate-800 text-slate-200 hover:text-white font-bold text-xs uppercase tracking-widest transition-all flex items-center justify-between border border-slate-700/60">
                        <span>Gestionar Padrón</span>
                        <span class="text-sm">→</span>
                    </a>
                </div>
            </div>

            <!-- Menú -->
            <div class="bg-slate-900/90 backdrop-blur-xl p-8 rounded-3xl border border-slate-800 shadow-2xl flex flex-col justify-between hover:border-slate-700 transition-all group">
                <div>
                    <div class="flex justify-between items-center mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            🍲
                        </div>
                        <span class="text-[11px] font-mono uppercase tracking-wider text-indigo-400 bg-indigo-500/10 px-3.5 py-1 rounded-full border border-indigo-500/20">Catálogo</span>
                    </div>
                    <p class="text-xs font-mono uppercase tracking-wider text-slate-400 font-bold">Opciones Menú</p>
                    <h2 class="text-5xl font-black text-white mt-2 tracking-tight">{{ $totalInsumos }}</h2>
                </div>
                <div class="mt-10">
                    <a href="/inventario" class="w-full py-3.5 px-5 rounded-2xl bg-slate-800/90 hover:bg-slate-800 text-slate-200 hover:text-white font-bold text-xs uppercase tracking-widest transition-all flex items-center justify-between border border-slate-700/60">
                        <span>Ver Menú</span>
                        <span class="text-sm">→</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- MÓDULO 4: MONITOR DE PEDIDOS (Ancho Completo) -->
        <div class="lg:col-span-12 bg-gradient-to-r from-indigo-950 via-slate-900 to-blue-950 p-8 lg:p-10 rounded-3xl border border-indigo-500/30 shadow-2xl flex flex-col lg:flex-row items-center justify-between gap-8 relative overflow-hidden">
            <div class="absolute -left-10 -bottom-10 w-56 h-56 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-3">
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full text-xs font-mono uppercase tracking-widest bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping"></span> Stream de Datos en Vivo
                </div>
                <h3 class="text-2xl lg:text-3xl font-black tracking-tight text-white">Monitoreo General de Asistencia y Órdenes</h3>
                <p class="text-sm text-slate-400 max-w-3xl font-medium leading-relaxed">
                    Supervisa de forma inmediata el flujo de entradas por carrera, consumo por turnos y reportes consolidados directamente desde el núcleo de la base de datos.
                </p>
            </div>
            <div>
                <a href="{{ route('monitor.pedidos') }}" class="px-8 py-5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs lg:text-sm uppercase tracking-widest shadow-2xl shadow-indigo-600/40 transition-all flex items-center gap-3 transform hover:-translate-y-0.5 whitespace-nowrap">
                    <span>Abrir Monitor en Vivo</span>
                    <span class="text-base">🚀</span>
                </a>
            </div>
        </div>

        <!-- MÓDULO 5: GRÁFICO EN TIEMPO REAL DE PEDIDOS POR HORA (HOY) -->
        @php
            $hoyLima = \Carbon\Carbon::today('America/Lima');
            $horasLabels = ['06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];
            $consumosHoy = \App\Models\Consumo::whereDate('created_at', $hoyLima)->get();
            $seriePedidosHora = [];
            $serieCanceladosHora = [];
            foreach (range(6, 16) as $h) {
                $deHora = $consumosHoy->filter(function ($c) use ($h) {
                    $horaReg = (int) \Carbon\Carbon::parse($c->created_at)->setTimezone('America/Lima')->format('H');
                    return $horaReg === $h;
                });
                $seriePedidosHora[] = $deHora->where('estado', '!=', 'cancelado')->count();
                $serieCanceladosHora[] = $deHora->where('estado', 'cancelado')->count();
            }
        @endphp
        <div wire:poll.5s class="lg:col-span-12 bg-slate-900/90 backdrop-blur-xl p-8 lg:p-10 rounded-3xl border border-slate-800 shadow-2xl relative overflow-hidden"
             data-labels='@json($horasLabels)'
             data-pedidos='@json($seriePedidosHora)'
             data-cancelados='@json($serieCanceladosHora)'>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <span class="text-xs font-mono uppercase tracking-widest text-cyan-400 font-bold">Tiempo Real • Hoy ({{ $hoyLima->format('d/m/Y') }})</span>
                    <h3 class="text-2xl font-black tracking-tight text-white mt-1">Pedidos del Comedor por Hora</h3>
                </div>
                <div class="flex items-center gap-4 text-xs font-mono">
                    <span class="inline-flex items-center gap-2 text-cyan-400">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span> Pedidos
                    </span>
                    <span class="inline-flex items-center gap-2 text-rose-400">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span> Cancelados
                    </span>
                </div>
            </div>

            <div class="h-72 w-full">
                <canvas id="graficoPedidosTiempoReal" wire:ignore></canvas>
            </div>
        </div>
    </div>

    <!-- MODAL FLOTANTE DE RECARGA DE SALDO -->
    @if($abrirModalRecarga ?? false)
    <div class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl text-slate-100">
            <h3 class="text-lg font-extrabold text-white mb-2">Recarga de Saldo</h3>
            <p class="text-xs text-slate-400 mb-4">Ingresa el DNI. El sistema ubicará automáticamente el nombre antes de procesar la recarga.</p>

            <form action="{{ route('admin.recargar.saldo') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">DNI del Estudiante:</label>
                    <input type="text"
                           name="dni"
                           wire:model.live="dniRecarga"
                           placeholder="Ej. 12345678"
                           required
                           maxlength="8"
                           class="w-full px-3 py-2 border border-slate-700 bg-slate-950 text-white rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono text-xs">
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Estudiante Encontrado:</label>
                    <input type="text"
                           readonly
                           value="{{ $nombreEstudianteRecarga ?: 'Escribe el DNI (8 dígitos)...' }}"
                           class="w-full px-3 py-2 border border-slate-800 rounded-xl bg-slate-950/70 font-bold text-xs {{ str_contains($nombreEstudianteRecarga ?? '', 'no encontrado') ? 'text-rose-400' : 'text-emerald-400' }}">
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Monto a Recargar (S/.):</label>
                    <input type="number"
                           step="0.50"
                           min="0.50"
                           name="monto"
                           value="{{ old('monto') }}"
                           placeholder="Ej. 10.00"
                           required
                           class="w-full px-3 py-2 border border-slate-700 bg-slate-950 text-white rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono text-xs">
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button"
                            wire:click="cerrarModal"
                            class="bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold px-4 py-2 rounded-xl text-xs uppercase transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-xl text-xs uppercase transition shadow cursor-pointer">
                        Registrar Recarga
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function () {
            function sincronizarGraficoPedidos() {
                const canvas = document.getElementById('graficoPedidosTiempoReal');
                if (!canvas) return;
                const contenedor = canvas.closest('[data-labels]');
                if (!contenedor) return;

                const labels = JSON.parse(contenedor.getAttribute('data-labels') || '[]');
                const pedidos = JSON.parse(contenedor.getAttribute('data-pedidos') || '[]');
                const cancelados = JSON.parse(contenedor.getAttribute('data-cancelados') || '[]');

                if (window.graficoPedidosInstancia) {
                    window.graficoPedidosInstancia.data.labels = labels;
                    window.graficoPedidosInstancia.data.datasets[0].data = pedidos;
                    window.graficoPedidosInstancia.data.datasets[1].data = cancelados;
                    window.graficoPedidosInstancia.update();
                    return;
                }

                window.graficoPedidosInstancia = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Pedidos',
                                data: pedidos,
                                borderColor: '#22d3ee',
                                backgroundColor: 'rgba(34, 211, 238, 0.15)',
                                borderWidth: 3,
                                tension: 0.35,
                                pointRadius: 4,
                                pointBackgroundColor: '#22d3ee',
                                fill: true
                            },
                            {
                                label: 'Cancelados',
                                data: cancelados,
                                borderColor: '#fb7185',
                                backgroundColor: 'rgba(251, 113, 133, 0.12)',
                                borderWidth: 2,
                                tension: 0.35,
                                pointRadius: 3,
                                pointBackgroundColor: '#fb7185',
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 350 },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                labels: { color: '#cbd5e1', font: { family: 'monospace', size: 12 } }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#94a3b8' },
                                grid: { color: 'rgba(148, 163, 184, 0.1)' }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: { color: '#94a3b8', precision: 0 },
                                grid: { color: 'rgba(148, 163, 184, 0.1)' }
                            }
                        }
                    }
                });
            }

            sincronizarGraficoPedidos();
            document.addEventListener('livewire:morph.updated', sincronizarGraficoPedidos);
            document.addEventListener('livewire:navigated', sincronizarGraficoPedidos);
            setInterval(sincronizarGraficoPedidos, 5000);
        })();
    </script>
</div>
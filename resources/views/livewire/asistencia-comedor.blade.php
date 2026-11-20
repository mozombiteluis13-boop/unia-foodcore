<div class="max-w-3xl mx-auto mt-8 p-6">
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tarjeta Formulario -->
    <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">UNIA FoodCore - Asistencia Comedor</h2>

        <form wire:submit.prevent="registrarAsistencia" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Ingrese el DNI del Estudiante:</label>
                <input type="text" wire:model="dni" maxlength="8" placeholder="Ej: 71234567" autofocus
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm text-lg focus:ring-indigo-500 focus:border-indigo-500">
                @error('dni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <button type="submit" 
                class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-lg rounded-md shadow transition duration-200">
                Registrar Asistencia
            </button>
        </form>

        @if ($mensaje)
            <div class="mt-4 p-4 rounded-md text-center text-sm font-bold {{ $tipoMensaje === 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300' }}">
                {{ $mensaje }}
            </div>
        @endif
    </div>

    <!-- Tabla Historial de Hoy -->
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Asistencias de Hoy (Últimas 10)</h3>

        @if($consumosHoy->isEmpty())
            <p class="text-gray-500 text-center py-4">Aún no se han registrado asistencias el día de hoy.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b text-xs font-semibold text-gray-600 uppercase">
                            <th class="py-3 px-4">Hora</th>
                            <th class="py-3 px-4">DNI</th>
                            <th class="py-3 px-4">Estudiante</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($consumosHoy as $consumo)
                            <tr class="hover:bg-gray-50 text-sm text-gray-700">
                                <td class="py-3 px-4 font-mono font-medium text-indigo-600">
                                    {{ $consumo->fecha_consumo ? \Carbon\Carbon::parse($consumo->fecha_consumo)->format('h:i:s A') : $consumo->created_at->format('h:i:s A') }}
                                </td>
                                <td class="py-3 px-4 font-mono">{{ $consumo->estudiante->dni }}</td>
                                <td class="py-3 px-4 font-semibold">{{ $consumo->estudiante->nombres }} {{ $consumo->estudiante->apellidos }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
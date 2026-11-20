<div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-indigo-600">UNIA FoodCore</h2>
            <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Pre-Registro para Estudiantes</p>
        </div>

        @if ($mensajeExito)
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-xl text-center text-sm font-bold">
            {{ $mensajeExito }}
        </div>
        @endif

        <form wire:submit.prevent="registrar" class="space-y-4">
            <!-- DNI -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">DNI (8 dígitos)</label>
                <input type="text" wire:model="dni" maxlength="8" placeholder="Ingrese su DNI"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('dni') <span class="text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
            </div>

            <!-- Nombres y Apellidos Completos -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombres y Apellidos Completos</label>
                <input type="text" wire:model="nombres" placeholder="Ej. Juan Pérez"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('nombres') <span class="text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
            </div>

            <!-- Carrera Profesional Dinámica -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Carrera Profesional</label>
                <select wire:model="carrera_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">-- Selecciona tu carrera --</option>
                    @foreach($carreras as $carrera)
                        <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                    @endforeach
                </select>
                @error('carrera_id') <span class="text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
            </div>

            <!-- Botón Enviar Registro -->
            <button type="submit" 
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition text-sm uppercase tracking-wider mt-2">
                ENVIAR REGISTRO
            </button>
        </form>

    </div>
</div>
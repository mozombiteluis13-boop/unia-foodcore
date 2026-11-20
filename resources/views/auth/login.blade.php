<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIA FoodCore - Acceso Administrador</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-200 font-sans antialiased min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-3xl">
                👨‍🍳
            </div>
            <h1 class="text-2xl font-black tracking-tight text-white">
                UNIA <span class="text-amber-400">FoodCore</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-semibold">
                Acceso Restringido · Administración y Cocina
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Correo Institucional
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="admin@unia.edu.pe"
                       class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                    Contraseña
                </label>
                <input type="password" name="password" required
                       placeholder="••••••••"
                       class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="inline-flex items-center gap-2 text-slate-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-700 text-amber-500 focus:ring-amber-500">
                    <span>Mantener sesión iniciada</span>
                </label>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl shadow-lg transition cursor-pointer">
                Ingresar al Panel
            </button>
        </form>
    </div>

</body>
</html>
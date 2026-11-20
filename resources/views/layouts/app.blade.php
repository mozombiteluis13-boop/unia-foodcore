<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'UNIA FoodCore' }}</title>
        
        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Obligatorio para que Livewire funcione -->
        @livewireStyles
    </head>
    <body class="bg-gray-100 text-gray-900">
        {{ $slot }}

        <!-- Obligatorio para que Livewire procese los clics -->
        @livewireScripts

        @auth
            @if (!request()->is('estudiante/*', 'registro', 'historial-estudiante', 'login'))
                @include('partials.alerta-cocina')
            @endif
        @endauth
    </body>
</html>
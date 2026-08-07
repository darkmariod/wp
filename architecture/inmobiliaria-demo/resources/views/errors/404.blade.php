<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada — Inmobiliaria Riobamba</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased flex items-center justify-center min-h-screen">
    <div class="text-center px-4 max-w-lg">
        <h1 class="text-8xl font-bold text-amber-600 mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Página no encontrada</h2>
        <p class="text-gray-500 mb-8">La propiedad que buscás no existe o fue removida.</p>
        <a href="{{ url('/') }}"
           class="inline-block bg-amber-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-amber-700 transition">
            Volver al inicio
        </a>
        <p class="mt-6 text-sm text-gray-400">
            <a href="{{ route('properties.index') }}" class="hover:text-amber-600">Ver todas las propiedades</a>
        </p>
    </div>
</body>
</html>

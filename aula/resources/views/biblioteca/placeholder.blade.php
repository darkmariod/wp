{{-- Provisional (T01): lo reemplaza el layout y la página real de la Biblioteca en T06/T07. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Biblioteca — Mi Escuelita</title>
    </head>
    <body>
        <main>
            <h1>Biblioteca</h1>
            <p>Muy pronto vas a encontrar aquí los recursos de tus cursos.</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Cerrar sesión</button>
            </form>
        </main>
    </body>
</html>

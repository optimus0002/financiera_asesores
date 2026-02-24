<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema Financiero')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">
    <div id="app">
        @yield('content')
    </div>

    <script>
        // Inicializar iconos de Lucide
        lucide.createIcons();
    </script>
    
    @stack('scripts')
</body>
</html>

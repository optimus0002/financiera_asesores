<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema Financiero')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class=" bg-[#060147]">
    <div id="app">
        <!-- Header General (solo para usuarios autenticados) -->
        @auth
        <div class="sticky top-0 z-40 bg-gradient-to-r from-[#eba44e] to-[#fb8c00] shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <!-- Logo y título -->
                    <div class="flex items-center space-x-4">
                        <div class="p-2 rounded-full">
                            <div class="group ...">
                                <img class="w-20 h-20 rounded-full" src="{{ Auth::user()->avatar }}" alt="Avatar" />
                            </div>
                        </div>
                        <!-- Info desktop -->
                        <div class="hidden sm:block">
                            <h1 class="text-2xl font-bold text-[#060147]">Panel de Control</h1>
                            <p class="text-sm text-gray-900">
                                Bienvenido de vuelta, <span class="font-medium text-[#060147]">{{ Auth::user()->full_name }}</span>
                            </p>
                        </div>
                        <!-- Info móvil -->
                        <div class="sm:hidden">
                            <div class="text-white">
                                <h1 class="text-[20px] font-extrabold">{{ Auth::user()->full_name }}</h1>
                                <p class="text-[16px] opacity-90">DNI: {{ Auth::user()->dni ?? 'N/A' }}</p>
                                <p class="text-[14px] opacity-80">Cargo: {{ Auth::user()->role ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Menú hamburguesa para móvil -->
                    <div class="sm:hidden">
                        <button onclick="toggleMobileMenu()" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <i data-lucide="menu" class="w-6 h-6 text-gray-600"></i>
                        </button>
                    </div>

                    <!-- Navegación desktop -->
                    <div class="hidden sm:flex items-center space-x-3">
                        <a href="{{ route('asesor.dashboard') }}"
                            class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors">
                            <i data-lucide="home" class="w-4 h-4"></i>
                            <span class="hidden lg:inline">Dashboard</span>
                        </a>
                        <a href="{{ route('asesor.reports') }}"
                            class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                            <span class="hidden lg:inline">Reportes del Día</span>
                        </a>
                        <a
                            href="{{ route('asesor.payment-history.view') }}"
                            class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" style="display: none;">
                            <i data-lucide="history" class="w-4 h-4"></i>
                            <span class="hidden lg:inline">Historial de Pagos</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-300 transition-colors">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                <span class="hidden lg:inline">Cerrar Sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Menú móvil ventana flotante -->
            <div id="mobileMenu" class="hidden fixed inset-0 bg-black bg-opacity-30 z-50">
                <div class="absolute top-4 right-4 bg-white bg-opacity-80 backdrop-blur-sm rounded-lg shadow-xl border border-gray-200 w-64">
                    <div class="p-4">
                        <!-- Header del menú -->
                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Menú</h3>
                            <button onclick="toggleMobileMenu()" class="p-1 hover:bg-gray-100 rounded-lg transition-colors">
                                <i data-lucide="x" class="w-5 h-5 text-gray-600"></i>
                            </button>
                        </div>
                        
                        <!-- Opciones del menú -->
                        <div class="space-y-2">
                            <a href="{{ route('asesor.dashboard') }}"
                                class="flex items-center gap-3 px-3 py-3 hover:bg-gray-50 transition-colors rounded-lg">
                                <i data-lucide="home" class="w-5 h-5 text-gray-600"></i>
                                <span class="text-gray-900">Dashboard</span>
                            </a>
                            <a href="{{ route('asesor.reports') }}"
                                class="flex items-center gap-3 px-3 py-3 hover:bg-gray-50 transition-colors rounded-lg">
                                <i data-lucide="file-text" class="w-5 h-5 text-gray-600"></i>
                                <span class="text-gray-900">Reportes del Día</span>
                            </a>
                            <a
                                href="{{ route('asesor.payment-history.view') }}"
                                class="flex items-center gap-3 px-3 py-3 hover:bg-gray-50 transition-colors rounded-lg">
                                <i data-lucide="history" class="w-5 h-5 text-gray-600"></i>
                                <span class="text-gray-900">Historial de Pagos</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full flex items-center gap-3 px-3 py-3 hover:bg-red-50 hover:text-red-600 transition-colors rounded-lg text-left">
                                    <i data-lucide="log-out" class="w-5 h-5 text-gray-600"></i>
                                    <span class="text-gray-900">Cerrar Sesión</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endauth

        <!-- Contenido específico de cada página -->
        @yield('content')
        
        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-4 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-sm text-gray-300">
                        &copy; {{ date('Y') }} FINANCIERA PRISMA. Todos los derechos reservados.
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Sistema Integral Financiero - Versión 1.0
                    </p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Inicializar iconos de Lucide
        lucide.createIcons();
        
        // =============================
        // Función para menú móvil
        // =============================
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobileMenu');
            const menuButton = event.target.closest('button[onclick="toggleMobileMenu()"]');
            
            if (!menu.contains(event.target) && !menuButton) {
                menu.classList.add('hidden');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>

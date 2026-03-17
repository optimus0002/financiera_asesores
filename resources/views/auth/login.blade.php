@extends('layouts.app')

@section('title', 'Login - Sistema Financiero')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <div class="text-center mb-8">
                <img src="{{ asset('images/logo.png') }}" alt="FINANCIERA PRISMA" class="w-24 h-24 mx-auto mb-4">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">FINANCIERA PRISMA</h1>
                <p class="text-gray-600">Sistema Integral Financiero</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 mr-2"></i>
                        <span class="text-red-700">{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6" id="loginForm">
                @csrf
                
                <!-- Mensaje de restricción horaria -->
                <div id="timeRestriction" class="hidden mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <i data-lucide="clock" class="w-5 h-5 text-yellow-600 mr-3"></i>
                        <div>
                            <p class="text-yellow-800 font-medium">Horario de Acceso Restringido</p>
                            <p class="text-yellow-700 text-sm mt-1">El sistema está disponible de <strong>Lunes a Sábado, 8:15 AM a 6:00 PM</strong></p>
                            <p class="text-yellow-600 text-sm mt-1">Por favor, intente nuevamente después de las 8:15 AM.</p>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="dni" class="block text-sm font-medium text-gray-700 mb-2">
                        DNI
                    </label>
                    <input 
                        type="text" 
                        id="dni" 
                        name="dni" 
                        value="{{ old('dni') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="12345678"
                        required
                        autocomplete="username"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Contraseña"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center"
                >
                    <i data-lucide="log-in" class="w-5 h-5 mr-2"></i>
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
        
        // Validar horario de acceso
        const loginForm = document.getElementById('loginForm');
        const timeRestrictionDiv = document.getElementById('timeRestriction');
        
        if (loginForm && timeRestrictionDiv) {
            loginForm.addEventListener('submit', function(e) {
                const now = new Date();
                const currentHour = now.getHours();
                const currentDay = now.getDay(); // 0 = Domingo, 6 = Sábado
                
                // Verificar si es fin de semana (Domingo = 0, Sábado = 6)
                const isWeekend = currentDay === 0 || currentDay === 6;
                
                // Verificar si está antes de las 8:15 AM
                const isBefore815 = currentHour < 8 || (currentHour === 8 && now.getMinutes() < 15);
                
                // Mostrar mensaje si es fin de semana o antes de las 8:15 AM
                if (isWeekend || isBefore815) {
                    e.preventDefault();
                    timeRestrictionDiv.classList.remove('hidden');
                    
                    // Ocultar mensaje después de 5 segundos
                    setTimeout(() => {
                        timeRestrictionDiv.classList.add('hidden');
                    }, 5000);
                }
            });
        }
    });
</script>
@endpush

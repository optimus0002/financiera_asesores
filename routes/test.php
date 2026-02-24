<?php

use Illuminate\Support\Facades\Route;
use App\Services\SupabaseService;

// Ruta de prueba para verificar conexión con Supabase
Route::get('/test-supabase', function () {
    try {
        $supabase = app(SupabaseService::class);
        
        // 1. Probar conexión básica
        $response = $supabase->from('users')
            ->select('count')
            ->limit(1)
            ->get();
        
        // 2. Probar búsqueda por DNI
        $userByDni = $supabase->from('users')
            ->where('dni', '75698021')
            ->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Conexión exitosa con Supabase',
            'connection_test' => $response,
            'user_search' => $userByDni,
            'supabase_url' => env('SUPABASE_URL'),
            'supabase_key' => env('SUPABASE_ANON_KEY') ? substr(env('SUPABASE_ANON_KEY'), 0, 20) . '...' : 'NOT_SET'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error de conexión con Supabase',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Ruta para probar autenticación exacta como el AuthController
Route::get('/test-auth-exact', function () {
    try {
        $supabase = app(SupabaseService::class);
        
        // Simular exactamente el flujo del AuthController
        $dni = '75698021';
        $password = 'prueba123';
        
        // 1. Buscar usuario por DNI
        $user = $supabase->from('users')
            ->where('dni', $dni)
            ->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
                'step' => 'user_search_failed'
            ]);
        }
        
        // 2. Verificar password manualmente
        $storedHash = $user['password_hash'];
        $passwordCorrect = password_verify($password, $storedHash);
        
        // 3. Autenticar con Supabase Auth
        $authResult = $supabase->signIn($user['email'], $password);
        
        return response()->json([
            'success' => $authResult['success'],
            'message' => $authResult['success'] ? 'Autenticación exitosa' : 'Autenticación fallida',
            'step' => 'auth_result',
            'user_found' => $user,
            'password_check' => [
                'stored_hash' => $storedHash,
                'password_entered' => $password,
                'password_correct' => $passwordCorrect
            ],
            'auth_result' => $authResult,
            'user_email' => $user['email'],
            'supabase_response' => $authResult
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error en autenticación',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

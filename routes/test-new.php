<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Services\SupabaseService;

// Test del nuevo flujo de autenticación (como React)
Route::get('/test-new-auth', function () {
    try {
        $dni = '75698021';
        $password = 'prueba123';
        
        // 1. Buscar usuario por DNI (como hace el nuevo AuthController)
        $supabase = app(SupabaseService::class);
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
        
        // 2. Verificar password localmente (como React)
        $passwordCorrect = password_verify($password, $user['password_hash']);
        
        if (!$passwordCorrect) {
            return response()->json([
                'success' => false,
                'message' => 'Password incorrecto',
                'step' => 'password_verify_failed',
                'user_found' => $user,
                'password_check' => [
                    'password_entered' => $password,
                    'stored_hash' => substr($user['password_hash'], 0, 30) . '...',
                    'password_correct' => $passwordCorrect
                ]
            ]);
        }
        
        // 3. Si todo está correcto
        return response()->json([
            'success' => true,
            'message' => '✅ Autenticación exitosa',
            'step' => 'authentication_complete',
            'user_found' => $user,
            'password_check' => [
                'password_entered' => $password,
                'stored_hash' => substr($user['password_hash'], 0, 30) . '...',
                'password_correct' => $passwordCorrect
            ],
            'redirect_to' => $user['role'] === 'admin' ? '/admin/reports' : '/asesor/dashboard'
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

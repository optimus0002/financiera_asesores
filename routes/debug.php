<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Services\SupabaseService;

// Ruta de depuración simple
Route::get('/debug-users', function () {
    try {
        // Llamada directa a Supabase
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE_ROLE_KEY'),
            'apikey' => env('SUPABASE_SERVICE_ROLE_KEY')
        ])->get(env('SUPABASE_URL') . '/rest/v1/users?select=id,dni,full_name,email,role,password_hash&limit=5');
        
        $data = $response->json();
        
        return response()->json([
            'success' => true,
            'response_status' => $response->status(),
            'response_body' => $data,
            'count' => is_array($data) ? count($data) : 'not_array',
            'is_successful' => $response->successful(),
            'supabase_url' => env('SUPABASE_URL'),
            'service_key' => env('SUPABASE_SERVICE_ROLE_KEY') ? substr(env('SUPABASE_SERVICE_ROLE_KEY'), 0, 20) . '...' : 'NOT_SET'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

<?php

use Illuminate\Support\Facades\Route;
use App\Services\SupabaseService;

// Test para búsqueda de clientes
Route::get('/test-search', function () {
    try {
        $supabase = app(SupabaseService::class);
        
        // Simular usuario asesor (ID 1)
        $advisorId = 1;
        $searchTerm = '87654321'; // DNI del cliente que creamos
        
        // 1. Test: Buscar todos los clientes del asesor
        $allClients = $supabase->from('clients')
            ->select('id, full_name, dni, email, phone, address, created_at, advisor_id')
            ->where('advisor_id', $advisorId)
            ->get();
        
        // 2. Test: Buscar por DNI
        $searchByDni = $supabase->from('clients')
            ->select('id, full_name, dni, email, phone, address, created_at, advisor_id')
            ->where('advisor_id', $advisorId)
            ->like('dni', $searchTerm)
            ->get();
        
        // 3. Test: Buscar por nombre
        $searchByName = $supabase->from('clients')
            ->select('id, full_name, dni, email, phone, address, created_at, advisor_id')
            ->where('advisor_id', $advisorId)
            ->like('full_name', $searchTerm)
            ->get();
        
        return response()->json([
            'success' => true,
            'tests' => [
                'all_clients_count' => $allClients ? count($allClients) : 0,
                'all_clients' => $allClients ? array_slice($allClients, 0, 3) : [],
                'search_term' => $searchTerm,
                'search_by_dni_count' => $searchByDni ? count($searchByDni) : 0,
                'search_by_dni' => $searchByDni ? array_slice($searchByDni, 0, 2) : [],
                'search_by_name_count' => $searchByName ? count($searchByName) : 0,
                'search_by_name' => $searchByName ? array_slice($searchByName, 0, 2) : [],
                'advisor_id' => $advisorId
            ],
            'message' => 'Búsqueda de clientes verificada'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

<?php

use Illuminate\Support\Facades\Route;
use App\Services\SupabaseService;

// Test para verificar datos del dashboard
Route::get('/test-dashboard', function () {
    try {
        $supabase = app(SupabaseService::class);
        
        // Simular usuario autenticado (ID 1 = asesor)
        $userId = 1;
        
        // 1. Test: Obtener clientes del asesor
        $clients = $supabase->from('clients')
            ->select('id, full_name, dni, email, phone, address, created_at, advisor_id')
            ->where('advisor_id', $userId)
            ->get();
        
        // 2. Test: Contar clientes
        $totalClients = $clients ? count($clients) : 0;
        
        // 3. Test: Obtener préstamos
        $loansTest = $supabase->from('loans')
            ->select('id, client_id, amount, interest_rate, term_months, monthly_payment, status_id, start_date, end_date, notes, codigo')
            ->limit(5)
            ->get();
        
        // 4. Test: Obtener pagos de hoy
        $today = date('Y-m-d');
        $todayPayments = $supabase->from('payments')
            ->select('id, amount, payment_method, created_at, loan_id')
            ->like('created_at', $today)
            ->get();
        
        return response()->json([
            'success' => true,
            'tests' => [
                'clients_count' => $totalClients,
                'clients_data' => $clients ? array_slice($clients, 0, 2) : [],
                'loans_count' => $loansTest ? count($loansTest) : 0,
                'loans_sample' => $loansTest ? array_slice($loansTest, 0, 2) : [],
                'today_payments_count' => $todayPayments ? count($todayPayments) : 0,
                'today_payments_sample' => $todayPayments ? array_slice($todayPayments, 0, 2) : [],
                'today_date' => $today,
                'user_id' => $userId
            ],
            'supabase_connection' => 'OK',
            'message' => 'Datos del dashboard verificados'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

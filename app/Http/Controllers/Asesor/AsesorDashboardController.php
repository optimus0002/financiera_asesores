<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Savings;

class AsesorDashboardController extends Controller
{
    public function index()
    {
        try {
            Log::info('AsesorDashboardController: index() called');
            
            $user = Auth::user();
            Log::info('AsesorDashboardController: Auth::user() retrieved');
            Log::info('AsesorDashboardController: User ID: ' . $user->id);
            Log::info('AsesorDashboardController: User role: ' . $user->role);
            
            // Obtener clientes asignados al asesor con relaciones
            $clients = Client::with([
                'loans' => function($query) {
                    $query->where('status_id', 1); // Solo préstamos activos
                },
                'savings'
            ])->where('advisor_id', $user->id)->get();

            // Estadísticas básicas
            $totalClients = $clients->count();
            $activeLoans = $clients->flatMap(function($client) {
                return $client->loans->where('status_id', 1);
            })->count();
            
            // Obtener pagos del día
            $today = date('Y-m-d');
            
            // Obtener pagos de préstamos del día
            $loanPayments = \App\Models\Installment::whereDate('payment_date', $today)
                ->where('status', 'pending_review')
                ->whereHas('loan.client', function($query) use ($user) {
                    $query->where('advisor_id', $user->id);
                })
                ->get();
            
            // Obtener pagos de ahorros del día
            $savingsPayments = \App\Models\SavingsInstallment::whereDate('payment_date', $today)
                ->where('status', 'pending_review')
                ->whereHas('savings.client', function($query) use ($user) {
                    $query->where('advisor_id', $user->id);
                })
                ->get();
            
            // Combinar ambos tipos de pagos
            $allPayments = $loanPayments->concat($savingsPayments);
            $todayPaymentsTotal = $allPayments->sum('paid_amount');

            // Datos para la vista
            $dashboardData = [
                'user' => [
                    'id' => $user->id,
                    'dni' => $user->dni,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'stats' => [
                    'total_clients' => $totalClients,
                    'active_loans' => $activeLoans,
                    'today_payments_count' => $allPayments->count(),
                    'today_payments_total' => number_format($todayPaymentsTotal, 2),
                ],
                'recent_clients' => $clients->take(5)->map(function($client) {
                    return [
                        'id' => $client->id,
                        'full_name' => $client->full_name,
                        'dni' => $client->dni,
                        'email' => $client->email,
                        'phone' => $client->phone,
                        'created_at' => $client->created_at->format('d/m/Y'),
                        'loans_count' => $client->loans->count(),
                        'savings_count' => $client->savings->count(),
                    ];
                })->toArray(),
                'today_payments' => $allPayments->map(function($payment) {
                    if ($payment instanceof \App\Models\Installment) {
                        // Es un pago de préstamo
                        return [
                            'id' => $payment->id,
                            'amount' => number_format($payment->paid_amount, 2),
                            'payment_method' => $payment->payment_method,
                            'client_name' => $payment->loan->client->full_name ?? 'N/A',
                            'created_at' => $payment->payment_date->format('H:i'),
                        ];
                    } else {
                        // Es un pago de ahorro
                        return [
                            'id' => $payment->id,
                            'amount' => number_format($payment->paid_amount, 2),
                            'payment_method' => $payment->payment_method,
                            'client_name' => $payment->savings->client->full_name ?? 'N/A',
                            'created_at' => $payment->payment_date->format('H:i'),
                        ];
                    }
                })->toArray()
            ];

            return view('asesor.dashboard', array_merge($dashboardData, [
                'totalClients' => $totalClients,
                'activeLoans' => $activeLoans,
                'todayPaymentsTotal' => $todayPaymentsTotal,
            ]));

        } catch (\Exception $e) {
            Log::error('Error en dashboard: ' . $e->getMessage());
            
            // Retornar vista con datos vacíos en caso de error
            return view('asesor.dashboard', [
                'user' => [
                    'id' => Auth::id() ?? 0,
                    'dni' => Auth::user()->dni ?? '',
                    'full_name' => Auth::user()->full_name ?? '',
                    'email' => Auth::user()->email ?? '',
                    'role' => Auth::user()->role ?? '',
                ],
                'stats' => [
                    'total_clients' => 0,
                    'active_loans' => 0,
                    'today_payments_count' => 0,
                    'today_payments_total' => '0.00',
                ],
                'recent_clients' => [],
                'today_payments' => [],
                'totalClients' => 0,
                'activeLoans' => 0,
                'todayPaymentsTotal' => 0,
                'error' => 'Error al cargar datos: ' . $e->getMessage()
            ]);
        }
    }
}

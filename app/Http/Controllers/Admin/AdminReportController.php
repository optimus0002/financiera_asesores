<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Client;
use App\Models\Loan;
use App\Models\User;

class AdminReportController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');

        // Estadísticas generales
        $totalClients = Client::count();
        $totalLoans = Loan::count();
        $activeLoans = Loan::whereHas('loanStatus', function ($query) {
            $query->where('description', '!=', 'Pagado');
        })->count();

        // Pagos del día
        $todayPayments = Payment::whereDate('created_at', $today)->get();
        $todayPaymentsTotal = $todayPayments->sum('amount');

        // Estadísticas por asesor
        $advisors = User::where('role', 'asesor')
            ->withCount(['clients', 'loans' => function ($query) {
                $query->whereHas('loanStatus', function ($q) {
                    $q->where('description', '!=', 'Pagado');
                });
            }])
            ->withSum(['payments' => function ($query) use ($today) {
                $query->whereDate('created_at', $today);
            }], 'amount')
            ->get();

        // Pagos recientes
        $recentPayments = Payment::with(['loan.client', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.reports', compact(
            'totalClients',
            'totalLoans',
            'activeLoans',
            'todayPaymentsTotal',
            'advisors',
            'recentPayments'
        ));
    }
}

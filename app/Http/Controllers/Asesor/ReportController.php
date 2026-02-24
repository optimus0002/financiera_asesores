<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Client;
use App\Models\Loan;
use App\Services\S3Service;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        // Obtener pagos del día
        $todayPayments = Payment::whereDate('created_at', $today)
            ->whereHas('loan.client', function ($query) use ($user) {
                $query->where('advisor_id', $user->id);
            })
            ->with(['loan.client', 'loan.loanStatus'])
            ->get();

        // Estadísticas del día
        $totalAmount = $todayPayments->sum('amount');
        $totalTransactions = $todayPayments->count();
        $averagePayment = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;

        // Pagos por método
        $paymentsByMethod = $todayPayments->groupBy('payment_method')
            ->map(function ($payments) {
                return [
                    'count' => $payments->count(),
                    'total' => $payments->sum('amount')
                ];
            });

        return view('asesor.reports', compact(
            'todayPayments',
            'totalAmount',
            'totalTransactions',
            'averagePayment',
            'paymentsByMethod'
        ));
    }

    public function todayPayments(Request $request)
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        // Obtener pagos de préstamos del día desde installments
        $loanPayments = \App\Models\Installment::whereDate('payment_date', $today)
            ->where('status', 'pending_review')
            ->whereHas('loan.client', function ($query) use ($user) {
                $query->where('advisor_id', $user->id);
            })
            ->with(['loan.client'])
            ->get()
            ->map(function ($installment) {
                return [
                    'id' => $installment->id,
                    'amount' => $installment->paid_amount,
                    'payment_method' => $installment->payment_method,
                    'created_at' => $installment->payment_date->format('Y-m-d H:i:s'),
                    'client_name' => $installment->loan->client->full_name,
                    'client_dni' => $installment->loan->client->dni,
                    'type' => 'préstamo'
                ];
            });

        // Obtener depósitos de ahorros del día desde savings_installments
        $savingsPayments = \App\Models\SavingsInstallment::whereDate('payment_date', $today)
            ->where('status', 'pending_review')
            ->whereHas('savings.client', function ($query) use ($user) {
                $query->where('advisor_id', $user->id);
            })
            ->with(['savings.client'])
            ->get()
            ->map(function ($installment) {
                return [
                    'id' => $installment->id,
                    'amount' => $installment->paid_amount,
                    'payment_method' => $installment->payment_method,
                    'created_at' => $installment->payment_date->format('Y-m-d H:i:s'),
                    'client_name' => $installment->savings->client->full_name,
                    'client_dni' => $installment->savings->client->dni,
                    'type' => 'ahorros'
                ];
            });

        // Combinar ambos resultados
        $allPayments = $loanPayments->concat($savingsPayments)
            ->sortBy('created_at')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $allPayments
        ]);
    }

    public function cashClosing(Request $request)
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        // Logging para depuración
        Log::info('Usuario autenticado:', [
            'user_id' => $user->id ?? 'null',
            'user_email' => $user->email ?? 'null',
            'user_name' => $user->name ?? 'null',
            'advisor_id' => $user->id ?? 'null'
        ]);

        try {
            // Validar que venga el método de pago
            $paymentMethod = $request->payment_method;
            if (!$paymentMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'El método de pago es requerido'
                ], 400);
            }

            // Validar que el usuario tenga un ID válido
            if (!$user || !$user->id) {
                Log::error('Usuario no válido o sin ID');
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado correctamente'
                ], 401);
            }

            // Verificar que el usuario exista en la tabla users
            $userExists = \App\Models\User::find($user->id);
            if (!$userExists) {
                Log::error('Usuario no encontrado en tabla users con ID: ' . $user->id);
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado en el sistema. Por favor contacte al administrador.'
                ], 400);
            }

            // Verificar que el usuario exista en la tabla user_asesores (para la restricción de clave foránea)
            try {
                $userAsesor = DB::table('user_asesores')->where('id', $user->id)->first();
                if (!$userAsesor) {
                    Log::error('Usuario no encontrado en tabla user_asesores con ID: ' . $user->id);
                    Log::info('Usuarios en user_asesores:', DB::table('user_asesores')->pluck('id', 'email')->toArray());
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Usuario no encontrado en la tabla de asesores. Por favor contacte al administrador.'
                    ], 400);
                }
                
                Log::info('Usuario encontrado en user_asesores:', [
                    'id' => $userAsesor->id,
                    'email' => $userAsesor->email,
                    'full_name' => $userAsesor->full_name
                ]);
            } catch (\Exception $e) {
                Log::error('Error al verificar user_asesores: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error al verificar el usuario en el sistema de asesores.'
                ], 500);
            }

            Log::info('Usuario validado correctamente, ID: ' . $user->id);

            // Verificar si el usuario existe en la tabla users (para la restricción de clave foránea)
            $userInUsersTable = \App\Models\User::find($user->id);
            if (!$userInUsersTable) {
                Log::info('Usuario no encontrado en tabla users, creando registro...');
                
                // Crear el usuario en la tabla users con los datos de user_asesores
                try {
                    $userAsesor = DB::table('user_asesores')->where('id', $user->id)->first();
                    if ($userAsesor) {
                        $newUser = new \App\Models\User();
                        $newUser->id = $userAsesor->id;
                        $newUser->email = $userAsesor->email;
                        $newUser->password = $user->password; // Usar la misma contraseña
                        $newUser->name = $userAsesor->full_name;
                        $newUser->save();
                        
                        Log::info('Usuario creado en tabla users con ID: ' . $user->id);
                    }
                } catch (\Exception $e) {
                    Log::error('Error al crear usuario en tabla users: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al sincronizar usuario con el sistema. Por favor contacte al administrador.'
                    ], 500);
                }
            } else {
                Log::info('Usuario ya existe en tabla users con ID: ' . $user->id);
            }

            // Depuración: Mostrar estructura real de la tabla daily_cash_closings
            try {
                $tableStructure = DB::select("SHOW CREATE TABLE daily_cash_closings");
                Log::info('Estructura de daily_cash_closings:', $tableStructure);
                
                // Mostrar las restricciones de clave foránea
                $constraints = DB::select("
                    SELECT 
                        CONSTRAINT_NAME,
                        TABLE_NAME,
                        COLUMN_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = 'financiera_db' 
                    AND TABLE_NAME = 'daily_cash_closings'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                Log::info('Restricciones de daily_cash_closings:', $constraints);
            } catch (\Exception $e) {
                Log::error('Error al obtener estructura de tabla: ' . $e->getMessage());
            }

            // Obtener datos del request según el método de pago
            $cashAmount = 0;
            $yapeAmount = 0;
            $transferProof = null;
            $paymentType = $paymentMethod;

            switch($paymentMethod) {
                case 'efectivo':
                    $cashAmount = $request->efectivo_amount;
                    if (!$cashAmount || $cashAmount <= 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'El monto en efectivo es requerido y debe ser mayor a 0'
                        ], 400);
                    }
                    break;

                case 'yape':
                    $yapeAmount = $request->yape_amount;
                    if (!$yapeAmount || $yapeAmount <= 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'El monto por Yape es requerido y debe ser mayor a 0'
                        ], 400);
                    }

                    // Manejar el archivo de comprobante
                    if ($request->hasFile('payment_proof')) {
                        $file = $request->file('payment_proof');
                        $transferProof = S3Service::uploadImage($file, 'yape-comprobantes-cierre-caja');
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'El comprobante de pago Yape es requerido'
                        ], 400);
                    }
                    break;

                case 'mixto':
                    $cashAmount = $request->efectivo_amount;
                    $yapeAmount = $request->yape_amount;
                    
                    if (!$cashAmount || $cashAmount <= 0 || !$yapeAmount || $yapeAmount <= 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Ambos montos (efectivo y Yape) son requeridos y deben ser mayores a 0'
                        ], 400);
                    }

                    // Manejar el archivo de comprobante
                    if ($request->hasFile('payment_proof')) {
                        $file = $request->file('payment_proof');
                        $transferProof = S3Service::uploadImage($file, 'yape-comprobantes-cierre-caja');
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'El comprobante de pago Yape es requerido'
                        ], 400);
                    }
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Método de pago no válido'
                    ], 400);
            }

            // Calcular total
            $totalAmount = $cashAmount + $yapeAmount;

            // Crear registro de cierre de caja con los campos correctos
            \App\Models\DailyCashClosing::create([
                'advisor_id' => $user->id,
                'closing_date' => $today,
                'total_amount' => $totalAmount,
                'yape_amount' => $yapeAmount,
                'cash_amount' => $cashAmount,
                'transfer_method' => $paymentMethod,
                'transfer_proof' => $transferProof,
                'notes' => 'Cierre de caja realizado vía ' . $paymentMethod,
                'status' => 'pending_review',
                'payment_type' => $paymentType
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cierre de caja realizado exitosamente',
                'data' => [
                    'total_amount' => $totalAmount,
                    'cash_amount' => $cashAmount,
                    'yape_amount' => $yapeAmount,
                    'payment_method' => $paymentMethod
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en cashClosing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar el cierre de caja: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkCashClosingStatus(Request $request)
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        // Logging para depuración
        Log::info('🔍 checkCashClosingStatus iniciado', [
            'user_id' => $user->id ?? 'null',
            'user_email' => $user->email ?? 'null',
            'today' => $today
        ]);

        try {
            // Verificar si ya existe un cierre de caja para hoy
            Log::info('📋 Buscando cierre de caja existente...', [
                'advisor_id' => $user->id,
                'closing_date' => $today
            ]);

            $existingClosing = \App\Models\DailyCashClosing::where('advisor_id', $user->id)
                ->where('closing_date', $today)
                ->first();

            Log::info('📊 Resultado de búsqueda:', [
                'found' => $existingClosing ? true : false,
                'closing_data' => $existingClosing ? [
                    'id' => $existingClosing->id,
                    'total_amount' => $existingClosing->total_amount,
                    'cash_amount' => $existingClosing->cash_amount,
                    'yape_amount' => $existingClosing->yape_amount,
                    'transfer_method' => $existingClosing->transfer_method,
                    'created_at' => $existingClosing->created_at->format('Y-m-d H:i:s')
                ] : null
            ]);

            if ($existingClosing) {
                Log::info('✅ Cierre de caja encontrado - Ya fue cerrado hoy');
                
                return response()->json([
                    'success' => true,
                    'already_closed' => true,
                    'closing_info' => [
                        'total_amount' => $existingClosing->total_amount,
                        'cash_amount' => $existingClosing->cash_amount,
                        'yape_amount' => $existingClosing->yape_amount,
                        'payment_method' => $existingClosing->transfer_method,
                        'closed_at' => $existingClosing->created_at->format('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                Log::info('🔓 No se encontró cierre de caja - Puede cerrar hoy');
                
                return response()->json([
                    'success' => true,
                    'already_closed' => false
                ]);
            }

        } catch (\Exception $e) {
            Log::error('💥 Error en checkCashClosingStatus: ' . $e->getMessage());
            Log::error('📋 Detalles del error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar estado de cierre de caja: ' . $e->getMessage()
            ], 500);
        }
    }
}

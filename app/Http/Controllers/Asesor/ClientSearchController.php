<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Client;

class ClientSearchController extends Controller
{
    public function search(Request $request)
    {
        try {
            $searchTerm = $request->get('q');
            $user = Auth::user();
            
            Log::info('Búsqueda de clientes - User ID: ' . ($user ? $user->id : 'null'));
            Log::info('Búsqueda de clientes - Search term: ' . $searchTerm);
            
            if (!$user) {
                Log::error('Usuario no autenticado');
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $advisorId = $user->id;

            // Búsqueda de clientes que tienen préstamos o ahorros asignados al asesor actual
            // Usamos una subconsulta para obtener los IDs de clientes que tienen préstamos con el asesor
            $clientsWithLoans = Client::whereHas('loans', function($query) use ($advisorId) {
                $query->where('advisor_id', $advisorId);
            })->pluck('id');

            // Usamos una subconsulta para obtener los IDs de clientes que tienen ahorros con el asesor
            $clientsWithSavings = Client::whereHas('savings', function($query) use ($advisorId) {
                $query->where('advisor_id', $advisorId);
            })->pluck('id');

            // Combinamos ambos conjuntos de IDs y eliminamos duplicados
            $clientIds = $clientsWithLoans->merge($clientsWithSavings)->unique();

            // Búsqueda con relaciones completas filtrando por los IDs de clientes encontrados
            $query = Client::with([
                'loans' => function($query) use ($advisorId) {
                    $query->where('advisor_id', $advisorId)
                          ->with('status')
                          ->with(['installments' => function($query) {
                            $query->orderBy('due_date', 'asc');
                        }]);
                },
                'savings' => function($query) use ($advisorId) {
                    $query->where('advisor_id', $advisorId);
                }
            ])->whereIn('id', $clientIds);

            if ($searchTerm && trim($searchTerm) !== '') {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('dni', 'like', "%{$searchTerm}%")
                      ->orWhere('full_name', 'like', "%{$searchTerm}%");
                });
            }

            $clients = $query->get();
            
            Log::info('Búsqueda de clientes - Results count: ' . $clients->count());

            // Mapeo completo con préstamos y ahorros
            $clientsWithDetails = [];
            foreach ($clients as $client) {
                // Calcular próximo pago
                $nextInstallment = null;
                if ($client->loans && $client->loans->isNotEmpty()) {
                    $upcomingInstallments = [];
                    foreach ($client->loans as $loan) {
                        if ($loan->installments && $loan->installments->isNotEmpty()) {
                            foreach ($loan->installments as $installment) {
                                $upcomingInstallments[] = [
                                    'id' => $installment->id,
                                    'due_date' => $installment->due_date->format('Y-m-d'),
                                    'amount' => $installment->amount,
                                    'paid_amount' => $installment->paid_amount,
                                    'status' => $installment->status,
                                    'loan_id' => $loan->id,
                                    'loan_amount' => $loan->amount
                                ];
                            }
                        }
                    }

                    if (!empty($upcomingInstallments)) {
                        // Ordenar por fecha de vencimiento
                        usort($upcomingInstallments, function ($a, $b) {
                            return strtotime($a['due_date']) - strtotime($b['due_date']);
                        });
                        $nextInstallment = $upcomingInstallments[0];
                    }
                }

                $clientData = [
                    'id' => $client->id,
                    'full_name' => $client->full_name,
                    'dni' => $client->dni,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                    'created_at' => $client->created_at->format('Y-m-d H:i:s'),
                    'advisor_id' => $client->advisor_id,
                    'loans' => $client->loans->map(function($loan) {
                        return [
                            'id' => $loan->id,
                            'client_id' => $loan->client_id,
                            'amount' => $loan->amount,
                            'interest_rate' => $loan->interest_rate,
                            'term_months' => $loan->term_months,
                            'monthly_payment' => $loan->monthly_payment,
                            'status_id' => $loan->status_id,
                            'start_date' => $loan->start_date->format('Y-m-d'),
                            'end_date' => $loan->end_date->format('Y-m-d'),
                            'notes' => $loan->notes,
                            'codigo' => $loan->codigo,
                            'tipo_credito' => $loan->tipo_credito,
                            'loan_status' => $loan->status ? [
                                'id' => $loan->status->id,
                                'code' => $loan->status->code,
                                'description' => $loan->status->description
                            ] : null,
                            'installments' => $loan->installments->map(function($installment) {
                                return [
                                    'id' => $installment->id,
                                    'loan_id' => $installment->loan_id,
                                    'due_date' => $installment->due_date->format('Y-m-d'),
                                    'amount' => $installment->amount,
                                    'paid_amount' => $installment->paid_amount,
                                    'status' => $installment->status
                                ];
                            })->toArray()
                        ];
                    })->toArray(),
                    'savings' => $client->savings->map(function($saving) {
                        return [
                            'id' => $saving->id,
                            'client_id' => $saving->client_id,
                            'amount' => $saving->amount,
                            'daily_contribution' => $saving->daily_contribution,
                            'start_date' => $saving->start_date->format('Y-m-d'),
                            'end_date' => $saving->end_date->format('Y-m-d'),
                            'status' => $saving->status,
                            'currency' => $saving->currency,
                            'codigo' => $saving->codigo
                        ];
                    })->toArray(),
                    'next_installment' => $nextInstallment
                ];
                
                $clientsWithDetails[] = $clientData;
            }

            $response = [
                'success' => true,
                'data' => $clientsWithDetails
            ];
            
            Log::info('Respuesta JSON: ' . json_encode($response));

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Error en búsqueda de clientes: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getClient($id)
    {
        $user = Auth::user();
        $advisorId = $user->id;
        
        // Verificar que el cliente tenga préstamos o ahorros asignados al asesor actual
        $hasLoansWithAdvisor = Client::whereHas('loans', function($query) use ($advisorId) {
            $query->where('advisor_id', $advisorId);
        })->where('id', $id)->exists();

        $hasSavingsWithAdvisor = Client::whereHas('savings', function($query) use ($advisorId) {
            $query->where('advisor_id', $advisorId);
        })->where('id', $id)->exists();

        if (!$hasLoansWithAdvisor && !$hasSavingsWithAdvisor) {
            return response()->json(['error' => 'Cliente no encontrado o no asignado a este asesor'], 404);
        }
        
        // Obtener el cliente con sus préstamos y ahorros del asesor
        $client = Client::with([
            'loans' => function($query) use ($advisorId) {
                $query->where('advisor_id', $advisorId)
                      ->with(['status', 'installments' => function($query) {
                        $query->orderBy('due_date', 'asc');
                    }]);
            },
            'savings' => function($query) use ($advisorId) {
                $query->where('advisor_id', $advisorId);
            }
        ])->where('id', $id)
          ->first();

        if (!$client) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }

        // Formatear datos del cliente
        $clientData = [
            'id' => $client->id,
            'full_name' => $client->full_name,
            'dni' => $client->dni,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address,
            'created_at' => $client->created_at->format('Y-m-d H:i:s'),
            'advisor_id' => $client->advisor_id,
            'loans' => $client->loans->map(function($loan) {
                return [
                    'id' => $loan->id,
                    'client_id' => $loan->client_id,
                    'amount' => $loan->amount,
                    'interest_rate' => $loan->interest_rate,
                    'term_months' => $loan->term_months,
                    'monthly_payment' => $loan->monthly_payment,
                    'status_id' => $loan->status_id,
                    'start_date' => $loan->start_date->format('Y-m-d'),
                    'end_date' => $loan->end_date->format('Y-m-d'),
                    'notes' => $loan->notes,
                    'codigo' => $loan->codigo,
                    'tipo_credito' => $loan->tipo_credito,
                    'loan_status' => $loan->status ? [
                        'id' => $loan->status->id,
                        'code' => $loan->status->code,
                        'description' => $loan->status->description
                    ] : null,
                    'installments' => $loan->installments->map(function($installment) {
                        return [
                            'id' => $installment->id,
                            'loan_id' => $installment->loan_id,
                            'due_date' => $installment->due_date->format('Y-m-d'),
                            'amount' => $installment->amount,
                            'paid_amount' => $installment->paid_amount,
                            'status' => $installment->status
                        ];
                    })->toArray()
                ];
            })->toArray(),
            'savings' => $client->savings->map(function($saving) {
                return [
                    'id' => $saving->id,
                    'client_id' => $saving->client_id,
                    'amount' => $saving->amount,
                    'daily_contribution' => $saving->daily_contribution,
                    'start_date' => $saving->start_date->format('Y-m-d'),
                    'end_date' => $saving->end_date->format('Y-m-d'),
                    'status' => $saving->status,
                    'currency' => $saving->currency,
                    'codigo' => $saving->codigo
                ];
            })->toArray()
        ];

        return response()->json($clientData);
    }

    public function recentClients()
    {
        try {
            $user = Auth::user();
            $advisorId = $user->id;
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Obtener IDs de clientes que tienen préstamos o ahorros con el asesor
            $clientsWithLoans = Client::whereHas('loans', function($query) use ($advisorId) {
                $query->where('advisor_id', $advisorId);
            })->pluck('id');

            $clientsWithSavings = Client::whereHas('savings', function($query) use ($advisorId) {
                $query->where('advisor_id', $advisorId);
            })->pluck('id');

            // Combinar IDs y eliminar duplicados
            $clientIds = $clientsWithLoans->merge($clientsWithSavings)->unique();

            // Obtener clientes recientes con conteo de préstamos del asesor
            $clients = Client::whereIn('id', $clientIds)
                ->withCount(['loans' => function($query) use ($advisorId) {
                    $query->where('advisor_id', $advisorId);
                }])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            $clientsData = $clients->map(function($client) {
                return [
                    'id' => $client->id,
                    'full_name' => $client->full_name,
                    'dni' => $client->dni,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                    'created_at' => $client->created_at->format('Y-m-d H:i:s'),
                    'advisor_id' => $client->advisor_id,
                    'loans_count' => $client->loans_count
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'data' => $clientsData
            ]);

        } catch (\Exception $e) {
            Log::error('Error en recent clients: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar clientes recientes: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Models\Loan;
use App\Services\S3Service;
use App\Models\Savings;
use App\Models\Installment;
use App\Models\ImagenComprobante;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Collection index llamado');
        
        $clientId = $request->get('clientId');
        Log::info('Client ID: ' . $clientId);
        
        if (!$clientId) {
            Log::error('Client ID no proporcionado');
            return redirect()->route('asesor.dashboard')
                ->with('error', 'Cliente no especificado');
        }

        // Verificar que el cliente tenga préstamos o ahorros con el asesor
        $hasLoansWithAdvisor = Client::whereHas('loans', function($query) {
            $query->where('advisor_id', Auth::id());
        })->where('id', $clientId)->exists();

        $hasSavingsWithAdvisor = Client::whereHas('savings', function($query) {
            $query->where('advisor_id', Auth::id());
        })->where('id', $clientId)->exists();

        if (!$hasLoansWithAdvisor && !$hasSavingsWithAdvisor) {
            Log::error('Cliente no encontrado o no asignado a este asesor');
            return redirect()->route('asesor.dashboard')
                ->with('error', 'Cliente no encontrado o no asignado a este asesor');
        }

        $client = Client::where('id', $clientId)
            ->where(function($query) {
                $query->whereHas('loans', function($subQuery) {
                    $subQuery->where('advisor_id', Auth::id());
                })
                ->orWhereHas('savings', function($subQuery) {
                    $subQuery->where('advisor_id', Auth::id());
                });
            })
            ->with([
                'loans.loanStatus',
                'loans.installments' => function ($query) {
                    $query->orderBy('due_date', 'asc');
                },
                'savings'
            ])
            ->firstOrFail();

        Log::info('Cliente encontrado: ' . $client->id);

        // Verificar si hay cuotas pendientes hasta el día de hoy (inclusive)
        $hasPendingInstallments = false;
        $pendingInstallmentsCount = 0;
        $today = now()->startOfDay(); // Asegurar que compare solo la fecha sin hora
        
        Log::info('Fecha de hoy (para comparación): ' . $today->format('Y-m-d H:i:s'));
        
        foreach ($client->loans as $loan) {
            foreach ($loan->installments as $installment) {
                $installmentDueDate = \Carbon\Carbon::parse($installment->due_date)->startOfDay();
                
                Log::info("Cuota ID: {$installment->id}, Due Date: {$installmentDueDate->format('Y-m-d')}, Status: {$installment->status}, Comparación: " . ($installmentDueDate->lte($today) ? 'Menor o igual' : 'Mayor'));
                
                if ($installment->status === 'pending' && $installmentDueDate->lte($today)) {
                    $hasPendingInstallments = true;
                    $pendingInstallmentsCount++;
                    Log::info('Cuota pendiente encontrada - ID: ' . $installment->id . ', Due: ' . $installment->due_date . ', Total acumulado: ' . $pendingInstallmentsCount);
                }
            }
        }

        Log::info('Total de cuotas pendientes hasta hoy: ' . $pendingInstallmentsCount);
        Log::info('¿Hay cuotas pendientes hasta hoy?: ' . ($hasPendingInstallments ? 'Sí' : 'No'));

        // Si es una solicitud AJAX, devolver JSON con datos de cuotas
        if ($request->ajax() || $request->wantsJson()) {
            $installmentsData = [];
            
            foreach ($client->loans as $loan) {
                foreach ($loan->installments as $installment) {
                    $installmentsData[] = [
                        'id' => $installment->id,
                        'installment_number' => $installment->installment_number,
                        'amount' => $installment->amount,
                        'paid_amount' => $installment->paid_amount,
                        'status' => $installment->status,
                        'due_date' => $installment->due_date,
                        'payment_date' => $installment->payment_date,
                        'payment_method' => $installment->payment_method,
                        'loan_id' => $installment->loan_id
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'client_id' => $client->id,
                'installments' => $installmentsData
            ]);
        }

        return view('asesor.collection', compact('client', 'hasPendingInstallments'));
    }

    public function processPayment(Request $request)
    {
        Log::info('processPayment llamado - Inicio del método');
        Log::info('Datos recibidos:', $request->all());
        
        // Verificar si es un pago individual de cuota
        if ($request->has('installment_id')) {
            Log::info('Procesando pago individual de cuota...');
            
            $request->validate([
                'installment_id' => 'required|exists:installments,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|string|in:yape,efectivo,transferencia',
                'client_id' => 'required|exists:clients,id'
            ]);
            
            Log::info('Validación pasada para pago individual');
            
            // Obtener la cuota específica
            $installment = \App\Models\Installment::with('loan')->find($request->installment_id);
            
            if (!$installment) {
                Log::error('Cuota no encontrada: ' . $request->installment_id);
                return response()->json(['success' => false, 'message' => 'Cuota no encontrada'], 404);
            }
            
            // Verificar que el préstamo pertenezca al asesor
            if ($installment->loan->advisor_id !== Auth::id()) {
                Log::error('Préstamo no pertenece al asesor: ' . $installment->loan->advisor_id . ' vs ' . Auth::id());
                return response()->json(['success' => false, 'message' => 'No tiene permiso para procesar este pago'], 403);
            }
            
            // Verificar que el cliente coincida
            if ($installment->loan->client_id != $request->client_id) {
                Log::error('Cliente no coincide: ' . $installment->loan->client_id . ' vs ' . $request->client_id);
                return response()->json(['success' => false, 'message' => 'Cliente no válido para esta cuota'], 400);
            }
            
            // Verificar que la cuota esté pendiente
            if ($installment->status !== 'pending') {
                Log::error('Cuota no está pendiente: ' . $installment->status);
                return response()->json(['success' => false, 'message' => 'Esta cuota ya ha sido procesada'], 400);
            }
            
            // Actualizar la cuota
            $installment->paid_amount = $request->amount;
            $installment->status = 'pending_review';
            $installment->payment_date = now();
            $installment->payment_method = $request->payment_method;
            
            // Manejar los comprobantes de pago si es Yape (múltiples imágenes)
            if ($request->payment_method === 'yape' && $request->hasFile('installment_proof')) {
                $paymentProofs = $request->file('installment_proof');
                
                // Procesar cada imagen
                foreach ($paymentProofs as $index => $paymentProof) {
                    $proofUrl = S3Service::uploadImage($paymentProof, 'yape-comprobantes');
                    
                    // Guardar en la tabla imagenes_comprobantes
                    ImagenComprobante::create([
                        'id_cuenta' => $installment->id,
                        'tipo_cuenta' => 'cuenta_credito',
                        'imagen' => $proofUrl,
                        'fecha' => now(),
                    ]);
                    
                    Log::info('Comprobante Yape #' . ($index + 1) . ' guardado en S3: ' . $proofUrl);
                }
            }
            
            $installment->save();
            
            Log::info('Cuota actualizada correctamente: ' . $installment->id);
            
            // Respuesta JSON para AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pago de cuota procesado correctamente'
                ]);
            }
            
            // Redirección para solicitudes normales
            return redirect()->route('asesor.collection', ['clientId' => $request->client_id])
                ->with('success', 'Pago de cuota procesado correctamente');
        }
        
        // Lógica original para pagos múltiples (si no hay installment_id)
        $paymentTypes = $request->input('payment_type', []);
        Log::info('Payment types:', $paymentTypes);
        
        // Procesar pago de préstamo si está seleccionado
        if (in_array('loan', $paymentTypes)) {
            Log::info('Procesando pago de préstamo...');
            
            $request->validate([
                'loan_id' => 'required|exists:loans,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|string|in:yape,efectivo,transferencia',
                'notes' => 'nullable|string'
            ]);

            Log::info('Validación pasada, loan_id: ' . $request->loan_id);

            // Validación adicional para Yape
            if ($request->payment_method === 'yape') {
                Log::info('Validando comprobante Yape...');
                Log::info('Archivo recibido:', ['has_file' => $request->hasFile('installment_proof') ? 'Sí' : 'No']);
                if ($request->hasFile('installment_proof')) {
                    $files = $request->file('installment_proof');
                    Log::info('Info archivos:', [
                        'file_count' => is_array($files) ? count($files) : 1,
                        'files' => is_array($files) ? array_map(function($file) {
                            return [
                                'original_name' => $file->getClientOriginalName(),
                                'mime_type' => $file->getMimeType(),
                                'size' => $file->getSize(),
                                'extension' => $file->getClientOriginalExtension()
                            ];
                        }, $files) : [[
                            'original_name' => $files->getClientOriginalName(),
                            'mime_type' => $files->getMimeType(),
                            'size' => $files->getSize(),
                            'extension' => $files->getClientOriginalExtension()
                        ]]
                    ]);
                }
                
                try {
                    $request->validate([
                        'installment_proof' => 'required|array|min:1',
                        'installment_proof.*' => 'required|image|mimes:jpeg,jpg,png|max:5120'
                    ]);
                    Log::info('Validación Yape pasada');
                } catch (\Illuminate\Validation\ValidationException $e) {
                    Log::info('Error de validación Yape:', ['message' => $e->getMessage()]);
                    Log::info('Errores de validación:', ['errors' => $e->errors()]);
                    throw $e;
                }
            }

            $loan = Loan::where('id', $request->loan_id)
                ->where('advisor_id', Auth::id())
                ->findOrFail($request->loan_id);
            Log::info('Préstamo encontrado: ' . $loan->id);
            
            // Verificar que el cliente exista
            $client = Client::findOrFail($loan->client_id);
            Log::info('Cliente verificado: ' . $client->id);

            // Encontrar la cuota más antigua con estado "pending" (excluyendo las que ya están en revisión)
           
            $allInstallments = Installment::where('loan_id', $loan->id)
                ->orderBy('due_date', 'asc')
                ->get();
                
      
            foreach ($allInstallments as $inst) {
              
            }
            
            $oldestPendingInstallment = Installment::where('loan_id', $loan->id)
                ->where('status', 'pending')
                ->orderBy('due_date', 'asc')
                ->first();
                
            if ($oldestPendingInstallment) {
     
            } else {
             
            }

            if (!$oldestPendingInstallment) {
                return redirect()->route('asesor.collection', ['clientId' => $request->client_id])
                    ->with('error', 'No hay cuotas pendientes para este préstamo');
            }

            // Actualizar la cuota más antigua
            $oldestPendingInstallment->paid_amount = $request->amount;
            $oldestPendingInstallment->status = 'pending_review';
            $oldestPendingInstallment->payment_date = now();
            $oldestPendingInstallment->payment_method = $request->payment_method;
            
            // Manejar los comprobantes de pago si es Yape (múltiples imágenes)
            if ($request->payment_method === 'yape' && $request->hasFile('payment_proof')) {
                $paymentProofs = $request->file('payment_proof');
                
                // Procesar cada imagen
                foreach ($paymentProofs as $index => $paymentProof) {
                    $proofUrl = S3Service::uploadImage($paymentProof, 'yape-comprobantes');
                    
                    // Guardar en la tabla imagenes_comprobantes
                    ImagenComprobante::create([
                        'id_cuenta' => $oldestPendingInstallment->id,
                        'tipo_cuenta' => 'cuenta_credito',
                        'imagen' => $proofUrl,
                        'fecha' => now(),
                    ]);
                    
                    Log::info('Comprobante Yape #' . ($index + 1) . ' guardado en S3: ' . $proofUrl);
                }
            }
            
            $oldestPendingInstallment->save();
            
            // El pago ya está registrado en la tabla installments, no se necesita tabla payments separada
        }
        
        // Procesar depósito de ahorros si está seleccionado
        if (in_array('savings', $paymentTypes)) {
            Log::info('Procesando depósito de ahorros...');
            
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'savings_id' => 'required|exists:savings,id',
                'savings_amount' => 'required|numeric|min:0.01',
                'savings_payment_method' => 'required|string|in:yape,efectivo,transferencia'
            ]);

            Log::info('Validación depósito pasada, savings_id: ' . $request->savings_id);

            // Validación adicional para Yape en ahorros
            if ($request->savings_payment_method === 'yape') {
                Log::info('Validando comprobante Yape para ahorros...');
                try {
                    $request->validate([
                        'savings_payment_proof' => 'required|array|min:1',
                        'savings_payment_proof.*' => 'required|image|mimes:jpeg,jpg,png|max:5120'
                    ]);
                    Log::info('Validación Yape para ahorros pasada');
                } catch (\Illuminate\Validation\ValidationException $e) {
                    Log::info('Error de validación Yape para ahorros:', ['message' => $e->getMessage()]);
                    throw $e;
                }
            }

            // Verificar que el cliente tenga préstamos o ahorros con el asesor
            $hasLoansWithAdvisor = Client::whereHas('loans', function($query) {
                $query->where('advisor_id', Auth::id());
            })->where('id', $request->client_id)->exists();

            $hasSavingsWithAdvisor = Client::whereHas('savings', function($query) {
                $query->where('advisor_id', Auth::id());
            })->where('id', $request->client_id)->exists();

            if (!$hasLoansWithAdvisor && !$hasSavingsWithAdvisor) {
                return redirect()->route('asesor.dashboard')
                    ->with('error', 'Cliente no encontrado o no asignado a este asesor');
            }

            $client = Client::findOrFail($request->client_id);

            $savings = Savings::where('id', $request->savings_id)
                ->where('client_id', $client->id)
                ->where('advisor_id', Auth::id())
                ->firstOrFail();

            // Encontrar la cuota de ahorro más antigua con estado "pending"
            Log::info('Buscando cuota de ahorro más antigua para savings_id: ' . $savings->id);
            
            $oldestPendingSavingsInstallment = \App\Models\SavingsInstallment::where('savings_id', $savings->id)
                ->where('status', 'pending')
                ->orderBy('due_date', 'asc')
                ->first();

            if (!$oldestPendingSavingsInstallment) {
                Log::info('No se encontró cuota de ahorro pendiente');
                return redirect()->route('asesor.collection', ['clientId' => $request->client_id])
                    ->with('error', 'No hay cuotas de ahorro pendientes para esta cuenta');
            }

            Log::info('Cuota de ahorro encontrada - ID: ' . $oldestPendingSavingsInstallment->id . ', Due: ' . $oldestPendingSavingsInstallment->due_date);

            // Actualizar la cuota de ahorro más antigua
            $oldestPendingSavingsInstallment->paid_amount = $request->savings_amount;
            $oldestPendingSavingsInstallment->status = 'pending_review';
            $oldestPendingSavingsInstallment->payment_date = now();
            $oldestPendingSavingsInstallment->payment_method = $request->savings_payment_method;
            
            // Manejar los comprobantes de pago si es Yape para ahorros (múltiples imágenes)
            if ($request->savings_payment_method === 'yape' && $request->hasFile('savings_payment_proof')) {
                $paymentProofs = $request->file('savings_payment_proof');
                
                // Procesar cada imagen
                foreach ($paymentProofs as $index => $paymentProof) {
                    $proofUrl = S3Service::uploadImage($paymentProof, 'yape-comprobantes-ahorros');
                    
                    // Guardar en la tabla imagenes_comprobantes
                    ImagenComprobante::create([
                        'id_cuenta' => $oldestPendingSavingsInstallment->id,
                        'tipo_cuenta' => 'cuenta_ahorro',
                        'imagen' => $proofUrl,
                        'fecha' => now(),
                    ]);
                    
                    Log::info('Comprobante Yape para ahorros #' . ($index + 1) . ' guardado en S3: ' . $proofUrl);
                }
            }
            
            $oldestPendingSavingsInstallment->save();

            // Actualizar el monto total de ahorros
            $savings->amount += $request->savings_amount;
            $savings->save();

            Log::info('Depósito de ahorros procesado correctamente - Cuota actualizada y monto total incrementado');
        }

        // Construir mensaje de éxito según lo que se procesó
        $processedTypes = [];
        if (in_array('loan', $paymentTypes)) {
            $processedTypes[] = 'préstamo';
        }
        if (in_array('savings', $paymentTypes)) {
            $processedTypes[] = 'ahorros';
        }

        $message = 'Pago procesado correctamente';
        if (count($processedTypes) > 0) {
            $message = 'Se procesó correctamente: ' . implode(' y ', $processedTypes);
        }

        // Si es una solicitud AJAX, devolver JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        // Si es una solicitud normal, hacer redirect
        return redirect()->route('asesor.collection', ['clientId' => $request->client_id])
            ->with('success', $message);
    }

    public function processSavingsDeposit(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'savings_id' => 'required|exists:savings,id',
            'savings_amount' => 'required|numeric|min:0.01',
            'savings_payment_method' => 'required|string|in:yape,efectivo,transferencia'
        ]);

        // Verificar que el cliente tenga préstamos o ahorros con el asesor
        $hasLoansWithAdvisor = Client::whereHas('loans', function($query) {
            $query->where('advisor_id', Auth::id());
        })->where('id', $request->client_id)->exists();

        $hasSavingsWithAdvisor = Client::whereHas('savings', function($query) {
            $query->where('advisor_id', Auth::id());
        })->where('id', $request->client_id)->exists();

        if (!$hasLoansWithAdvisor && !$hasSavingsWithAdvisor) {
            return redirect()->route('asesor.dashboard')
                ->with('error', 'Cliente no encontrado o no asignado a este asesor');
        }

        $client = Client::findOrFail($request->client_id);

        $savings = Savings::where('id', $request->savings_id)
            ->where('client_id', $client->id)
            ->where('advisor_id', Auth::id())
            ->firstOrFail();

        // Actualizar monto de ahorros
        $savings->amount += $request->savings_amount;
        $savings->save();

        // Crear registro del depósito en savings_installments
        \App\Models\SavingsInstallment::create([
            'savings_id' => $savings->id,
            'amount' => $request->savings_amount,
            'paid_amount' => $request->savings_amount,
            'payment_date' => now(),
            'payment_method' => $request->savings_payment_method,
            'status' => 'pending_review',
            'notes' => 'Depósito en cuenta de ahorros',
            'created_by' => Auth::id()
        ]);

        return redirect()->route('asesor.collection', ['clientId' => $client->id])
            ->with('success', 'Depósito de ahorros procesado correctamente');
    }

    private function updateInstallments(Loan $loan, $paymentAmount)
    {
        $pendingInstallments = $loan->installments()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->get();

        $remainingAmount = $paymentAmount;

        foreach ($pendingInstallments as $installment) {
            if ($remainingAmount <= 0) break;

            $neededAmount = $installment->amount - $installment->paid_amount;
            
            if ($remainingAmount >= $neededAmount) {
                // Pagar completamente esta cuota
                $installment->paid_amount = $installment->amount;
                $installment->status = 'paid';
                $remainingAmount -= $neededAmount;
            } else {
                // Pago parcial
                $installment->paid_amount += $remainingAmount;
                $remainingAmount = 0;
            }

            $installment->save();
        }

        // Verificar si el préstamo está completamente pagado
        $allPaid = $loan->installments()
            ->where('status', '!=', 'paid')
            ->count() === 0;

        if ($allPaid) {
            $loan->status_id = 3; // Estado pagado
            $loan->save();
        }
    }
}

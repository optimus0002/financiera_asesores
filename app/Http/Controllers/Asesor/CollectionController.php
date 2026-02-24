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

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $clientId = $request->get('clientId');
        
        if (!$clientId) {
            return redirect()->route('asesor.dashboard')
                ->with('error', 'No se especificó un cliente');
        }

        $client = Client::where('id', $clientId)
            ->where('advisor_id', Auth::id())
            ->with([
                'loans.loanStatus',
                'loans.installments' => function ($query) {
                    $query->orderBy('due_date', 'asc');
                },
                'savings'
            ])
            ->firstOrFail();

        return view('asesor.collection', compact('client'));
    }

    public function processPayment(Request $request)
    {
        Log::info('processPayment llamado - Inicio del método');
        Log::info('Datos recibidos:', $request->all());
        
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
                Log::info('Archivo recibido:', ['has_file' => $request->hasFile('payment_proof') ? 'Sí' : 'No']);
                if ($request->hasFile('payment_proof')) {
                    $file = $request->file('payment_proof');
                    Log::info('Info archivo:', [
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'extension' => $file->getClientOriginalExtension()
                    ]);
                }
                
                try {
                    $request->validate([
                        'payment_proof' => 'required|image|mimes:jpeg,jpg,png|max:5120'
                    ]);
                    Log::info('Validación Yape pasada');
                } catch (\Illuminate\Validation\ValidationException $e) {
                    Log::info('Error de validación Yape:', ['message' => $e->getMessage()]);
                    Log::info('Errores de validación:', ['errors' => $e->errors()]);
                    throw $e;
                }
            }

            $loan = Loan::findOrFail($request->loan_id);
            Log::info('Préstamo encontrado: ' . $loan->id);
            
            // Verificar que el préstamo pertenezca a un cliente del asesor
            $client = Client::where('id', $loan->client_id)
                ->where('advisor_id', Auth::id())
                ->firstOrFail();
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
            
            // Manejar el comprobante de pago si es Yape
            if ($request->payment_method === 'yape' && $request->hasFile('payment_proof')) {
                $paymentProof = $request->file('payment_proof');
                $proofUrl = S3Service::uploadImage($paymentProof, 'yape-comprobantes');
                $oldestPendingInstallment->payment_proof = $proofUrl;
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
                        'savings_payment_proof' => 'required|image|mimes:jpeg,jpg,png|max:5120'
                    ]);
                    Log::info('Validación Yape para ahorros pasada');
                } catch (\Illuminate\Validation\ValidationException $e) {
                    Log::info('Error de validación Yape para ahorros:', ['message' => $e->getMessage()]);
                    throw $e;
                }
            }

            $client = Client::where('id', $request->client_id)
                ->where('advisor_id', Auth::id())
                ->firstOrFail();

            $savings = Savings::where('id', $request->savings_id)
                ->where('client_id', $client->id)
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
            
            // Manejar el comprobante de pago si es Yape para ahorros
            if ($request->savings_payment_method === 'yape' && $request->hasFile('savings_payment_proof')) {
                $paymentProof = $request->file('savings_payment_proof');
                $proofUrl = S3Service::uploadImage($paymentProof, 'yape-comprobantes-ahorros');
                $oldestPendingSavingsInstallment->payment_proof = $proofUrl;
                Log::info('Comprobante Yape para ahorros guardado en S3: ' . $proofUrl);
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

        $client = Client::where('id', $request->client_id)
            ->where('advisor_id', Auth::id())
            ->firstOrFail();

        $savings = Savings::where('id', $request->savings_id)
            ->where('client_id', $client->id)
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

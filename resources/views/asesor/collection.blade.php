@extends('layouts.app')

@section('title', 'Cobros - Asesor')

@section('content')
<br>
<div class="min-h-screen">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="bg-white shadow-lg border-0 rounded-lg">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b p-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    Información del Cliente
                </h2>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <h3 class="font-medium text-gray-900">{{ $client->full_name }}</h3>
                    <p class="text-sm text-gray-500">DNI: {{ $client->dni }}</p>
                </div>

                @if($client->loans && $client->loans->count() > 0 || $client->savings && $client->savings->count() > 0)
                    <form method="POST" action="{{ route('asesor.collection.payment') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <input type="hidden" name="client_id" value="{{ $client->id }}">

                        <div>
                            <label class="block text-sm font-medium mb-2">Tipo de Registro</label>
                            <div class="space-y-3">
                                @if($client->loans && $client->loans->count() > 0)
                                    <label class="flex items-center space-x-3 p-3 border rounded-lg hover:bg-blue-50 cursor-pointer transition-colors">
                                        <input type="checkbox" name="payment_type[]" value="loan" class="h-4 w-4" checked>
                                        <span class="font-medium">Pago de Préstamo</span>
                                    </label>
                                @else
                                    <label class="flex items-center space-x-3 p-3 border rounded-lg opacity-50 cursor-not-allowed">
                                        <input type="checkbox" name="payment_type[]" value="loan" class="h-4 w-4" disabled>
                                        <span class="font-medium text-gray-400">Pago de Préstamo (No disponible)</span>
                                    </label>
                                @endif

                                @if($client->savings && $client->savings->count() > 0)
                                    <label class="flex items-center space-x-3 p-3 border rounded-lg hover:bg-green-50 cursor-pointer transition-colors">
                                        <input type="checkbox" name="payment_type[]" value="savings" class="h-4 w-4">
                                        <span class="font-medium">Depósito de Ahorros</span>
                                    </label>
                                @else
                                    <label class="flex items-center space-x-3 p-3 border rounded-lg opacity-50 cursor-not-allowed">
                                        <input type="checkbox" name="payment_type[]" value="savings" class="h-4 w-4" disabled>
                                        <span class="font-medium text-gray-400">Depósito de Ahorros (No disponible)</span>
                                    </label>
                                @endif
                            </div>
                        </div>

                        <!-- Préstamos Section -->
                        @if($client->loans && $client->loans->count() > 0)
                            <div id="loan-section" class="border rounded-lg p-4 bg-blue-50" style="display: none;">
                                <h4 class="font-medium text-blue-900 mb-4">Pago de Préstamo</h4>
                                
                                @if($client->loans->count() > 1)
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium mb-2">Seleccionar préstamo a pagar:</label>
                                        <select name="loan_id" id="loan-select" class="w-full p-3 border border-blue-200 rounded-md bg-white text-sm">
                                            <option value="">Seleccione un préstamo...</option>
                                            @foreach($client->loans as $loan)
                                                <option value="{{ $loan->id }}" data-monthly-payment="{{ $loan->monthly_payment }}">
                                                    {{ $loan->codigo ? 'Préstamo ' . $loan->codigo : 'Préstamo #' . $loan->id }} - 
                                                    Cuota {{ $loan->tipo_credito === 'credito_diario' ? 'diaria' : ($loan->tipo_credito === 'credito_semanal' ? 'semanal' : 'mensual') }}: {{ number_format($loan->monthly_payment, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="loan_id" id="loan-id" value="{{ $client->loans->first()->id }}">
                                    <input type="hidden" id="monthly-payment" value="{{ $client->loans->first()->monthly_payment }}">
                                @endif

                                <!-- Verificar si hay cuotas retrasadas -->
                                <div id="overdue-installments-section" class="mb-4 hidden">
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <div class="flex items-center gap-2 mb-3">
                                            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                                            <h5 class="font-medium text-red-900">Cuotas Retrasadas</h5>
                                        </div>
                                        <p class="text-sm text-red-700 mb-4">Este préstamo tiene cuotas vencidas. Seleccione cuáles desea pagar:</p>
                                        
                                        <div id="overdue-installments-list" class="space-y-2">
                                            <!-- Las cuotas se cargarán dinámicamente con JavaScript -->
                                        </div>
                                        
                                        <div class="mt-4">
                                            <p class="text-sm text-gray-600">
                                                Selecciona los pagos vencidos que deseas procesar individualmente
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div id="regular-payment-section">
                                    <div>
                                        <label class="block text-sm font-medium mb-2">Monto del pago</label>
                                        <input 
                                            type="number" 
                                            name="amount" 
                                            id="payment-amount"
                                            step="0.01" 
                                            min="0" 
                                            placeholder="0.00"
                                            class="w-full p-3 text-lg font-semibold border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                        >
                                        <p class="text-xs text-gray-500 mt-1">
                                            Cuota sugerida: S/. <span id="suggested-payment">{{ number_format($client->loans->first()->monthly_payment, 2) }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div id="regular-payment-method" class="mt-4 hidden">
                                    <label class="block text-sm font-medium mb-2">Método de pago</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-3">
                                            <input type="radio" name="payment_method" value="efectivo" class="h-4 w-4" checked>
                                            <span>Efectivo</span>
                                        </label>
                                        <label class="flex items-center space-x-3">
                                            <input type="radio" name="payment_method" value="yape" class="h-4 w-4">
                                            <span>Yape</span>
                                        </label>
                                    </div>
                                </div>

                                <div id="yape-proof-section" class="mt-4 hidden">
                                    <div class="border-2 border-dashed border-green-300 rounded-lg p-6 bg-green-50">
                                        <div class="text-center">
                                            <div id="preview-container" class="mb-4 hidden">
                                                <img id="image-preview" src="" alt="Vista previa del comprobante" class="mx-auto max-h-64 rounded-lg shadow-md">
                                                <button type="button" id="remove-image" class="mt-2 px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600 transition-colors">
                                                    <i data-lucide="trash-2" class="w-4 h-4 inline mr-1"></i>
                                                    Quitar imagen
                                                </button>
                                            </div>
                                            
                                            <div id="upload-area" class="border-2 border-dashed border-green-400 rounded-lg p-8 cursor-pointer hover:bg-green-100 transition-colors">
                                                <i data-lucide="upload-cloud" class="w-12 h-12 text-green-500 mx-auto mb-4"></i>
                                                <p class="text-green-700 font-medium">Arrastra el comprobante Yape aquí</p>
                                                <p class="text-green-600 text-sm mt-2">o haz clic para seleccionar</p>
                                                <p class="text-green-500 text-xs mt-1">Formatos: JPG, PNG (máx. 5MB)</p>
                                            </div>
                                            
                                            <input type="file" 
                                                   name="payment_proof" 
                                                   id="payment_proof" 
                                                   accept="image/jpeg,image/jpg,image/png" 
                                                   class="hidden">
                                        </div>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Savings Section -->
                        @if($client->savings && $client->savings->count() > 0)
                            <div id="savings-section" class="border rounded-lg p-4 bg-green-50" style="display: none;">
                                <h4 class="font-medium text-green-900 mb-4">Depósito de Ahorros</h4>
                                
                                @if($client->savings->count() > 1)
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium mb-2">Seleccionar cuenta de ahorros:</label>
                                        <select name="savings_id" class="w-full p-3 border border-green-200 rounded-md bg-white text-sm">
                                            <option value="">Seleccione una cuenta...</option>
                                            @foreach($client->savings as $saving)
                                                <option value="{{ $saving->id }}">
                                                    {{ $saving->codigo ? 'Ahorro ' . $saving->codigo : 'Ahorro #' . $saving->id }} - 
                                                    Aporte diario: {{ number_format($saving->daily_contribution, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="savings_id" value="{{ $client->savings->first()->id }}">
                                @endif

                                <div>
                                    <label class="block text-sm font-medium mb-2">Monto del depósito</label>
                                    <input 
                                        type="number" 
                                        name="savings_amount" 
                                        step="0.01" 
                                        min="0" 
                                        placeholder="0.00"
                                        class="w-full p-3 text-lg font-semibold border border-gray-200 rounded-lg focus:border-green-500 focus:ring-green-500"
                                    >
                                    <p class="text-xs text-gray-500 mt-1">
                                        Aporte sugerido: S/. {{ number_format($client->savings->first()->daily_contribution, 2) }}
                                    </p>
                                </div>

                                <div class="mt-4">
                                    <label class="block text-sm font-medium mb-2">Método de Pago</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-3">
                                            <input type="radio" name="savings_payment_method" value="efectivo" class="h-4 w-4">
                                            <span>Efectivo</span>
                                        </label>
                                        <label class="flex items-center space-x-3">
                                            <input type="radio" name="savings_payment_method" value="yape" class="h-4 w-4">
                                            <span>Yape</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Yape Proof Section for Savings -->
                                <div id="savings-yape-proof-section" class="mt-4 hidden">
                                    <div class="border-2 border-dashed border-green-300 rounded-lg p-6 bg-green-50">
                                        <div class="text-center">
                                            <div id="savings-preview-container" class="mb-4 hidden">
                                                <img id="savings-image-preview" src="" alt="Vista previa del comprobante" class="mx-auto max-h-64 rounded-lg shadow-md">
                                                <button type="button" id="savings-remove-image" class="mt-2 px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600 transition-colors">
                                                    <i data-lucide="trash-2" class="w-4 h-4 inline mr-1"></i>
                                                    Quitar imagen
                                                </button>
                                            </div>
                                            
                                            <div id="savings-upload-area" class="border-2 border-dashed border-green-400 rounded-lg p-8 cursor-pointer hover:bg-green-100 transition-colors">
                                                <i data-lucide="upload-cloud" class="w-12 h-12 text-green-500 mx-auto mb-4"></i>
                                                <p class="text-green-700 font-medium">Arrastra el comprobante Yape aquí</p>
                                                <p class="text-green-600 text-sm mt-2">o haz clic para seleccionar</p>
                                                <p class="text-green-500 text-xs mt-1">Formatos: JPG, PNG (máx. 5MB)</p>
                                            </div>
                                            
                                            <input type="file" 
                                                   name="savings_payment_proof" 
                                                   id="savings_payment_proof" 
                                                   accept="image/jpeg,image/jpg,image/png" 
                                                   class="hidden">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($client->loans->count() === 0 && $client->savings->count() === 0)
                            <div class="text-center py-8">
                                <i data-lucide="alert-circle" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                                <p class="text-gray-600 font-medium">El cliente no tiene préstamos activos ni planes de ahorros</p>
                                <p class="text-sm text-gray-500 mt-2">No hay pagos ni depósitos disponibles para registrar</p>
                            </div>
                        @endif

                        @if($client->loans->count() > 0 || $client->savings->count() > 0)
                            <div class="flex justify-end mt-6">
                                <button 
                                    type="submit" 
                                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                                >
                                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                                    Procesar Pago
                                </button>
                            </div>
                        @endif
                    </form>
                @else
                    <div class="text-center py-8">
                        <i data-lucide="alert-circle" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                        <p class="text-gray-600 font-medium">El cliente no tiene préstamos activos ni planes de ahorros</p>
                        <p class="text-sm text-gray-500 mt-2">Contacte al administrador para asignar productos</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>

// Funciones globales para manejar cuotas individuales
document.addEventListener('change', function(e) {
    if (e.target.name && e.target.name.startsWith('installment_method_')) {
        const installmentId = e.target.name.replace('installment_method_', '');
        const yapeProof = document.getElementById(`yape-proof-${installmentId}`);
        
        console.log(` Cambió método de pago para cuota ${installmentId}:`, e.target.value);
        
        if (e.target.value === 'yape') {
            if (yapeProof) {
                yapeProof.classList.remove('hidden');
                console.log(` Mostrando comprobante Yape para cuota ${installmentId}`);
            } else {
                console.warn(` No se encontró yape-proof-${installmentId}`);
            }
        } else {
            if (yapeProof) {
                yapeProof.classList.add('hidden');
                console.log(` Ocultando comprobante Yape para cuota ${installmentId}`);
            }
        }
    }
});

window.toggleInstallmentPayment = function(installmentId, isChecked) {
    const paymentOptions = document.getElementById(`payment-options-${installmentId}`);
    
    // Validar que el elemento exista
    if (!paymentOptions) {
        console.warn(` Elemento payment-options-${installmentId} no encontrado`);
        return;
    }
    
    if (isChecked) {
        paymentOptions.classList.remove('hidden');
        console.log(` Mostrando opciones de pago para cuota ${installmentId}`);
    } else {
        paymentOptions.classList.add('hidden');
        console.log(` Ocultando opciones de pago para cuota ${installmentId}`);
    }
    updateSelectedTotal();
};

window.previewInstallmentProof = function(installmentId, input) {
    const file = input.files[0];
    if (!file) return;

    // Validar tipo de archivo
    if (!file.type.match('image.*')) {
        alert('Por favor selecciona un archivo de imagen (JPG o PNG)');
        return;
    }

    // Validar tamaño (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('El archivo no debe ser mayor a 5MB');
        return;
    }

    // Mostrar vista previa
    const reader = new FileReader();
    reader.onload = function(e) {
        const previewContainer = document.getElementById(`installment-preview-${installmentId}`);
        const img = previewContainer.querySelector('img');
        img.src = e.target.result;
        previewContainer.classList.remove('hidden');
        
        // Ocultar el área de subida
        const uploadArea = document.getElementById(`yape-proof-${installmentId}`).querySelector('.border-dashed');
        if (uploadArea) {
            uploadArea.classList.add('hidden');
        }
    };
    reader.readAsDataURL(file);
};

window.removeInstallmentProof = function(installmentId) {
    const previewContainer = document.getElementById(`installment-preview-${installmentId}`);
    const input = document.getElementById(`installment_proof_${installmentId}`);
    const yapeProof = document.getElementById(`yape-proof-${installmentId}`);
    
    // Limpiar input
    input.value = '';
    
    // Ocultar vista previa
    previewContainer.classList.add('hidden');
    
    // Mostrar área de subida
    const uploadArea = yapeProof.querySelector('.border-dashed');
    if (uploadArea) {
        uploadArea.classList.remove('hidden');
    }
};

// Función para mostrar modal de confirmación
function showConfirmModal(message, onConfirm) {
    // Crear modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                    <i data-lucide="alert-triangle" class="w-6 h-6 text-yellow-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar Pago</h3>
                    <p class="text-sm text-gray-600">Verifique los detalles antes de confirmar</p>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <pre class="text-sm text-gray-700 whitespace-pre-wrap">${message}</pre>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" id="cancel-btn" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors font-medium">
                    Cancelar
                </button>
                <button type="button" id="confirm-btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium">
                    Confirmar Pago
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    lucide.createIcons();
    
    // Manejar eventos
    const cancelBtn = modal.querySelector('#cancel-btn');
    const confirmBtn = modal.querySelector('#confirm-btn');
    
    cancelBtn.addEventListener('click', () => {
        document.body.removeChild(modal);
        console.log('❌ Usuario canceló el pago');
    });
    
    confirmBtn.addEventListener('click', () => {
        document.body.removeChild(modal);
        console.log('✅ Usuario confirmó el pago, procesando...');
        onConfirm();
    });
    
    // Cerrar al hacer clic fuera
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            document.body.removeChild(modal);
            console.log('❌ Usuario cerró el modal');
        }
    });
    
    // Cerrar con ESC
    const handleEscape = (e) => {
        if (e.key === 'Escape') {
            document.body.removeChild(modal);
            document.removeEventListener('keydown', handleEscape);
            console.log('❌ Usuario cerró el modal con ESC');
        }
    };
    document.addEventListener('keydown', handleEscape);
}

// Función para procesar pago individual de cuota
window.processIndividualInstallment = function(installmentId) {
    console.log(`💳 Procesando pago individual para cuota ${installmentId}`);
    
    // Validar que la cuota esté seleccionada
    const checkbox = document.querySelector(`input[data-installment-id="${installmentId}"]`);
    if (!checkbox || !checkbox.checked) {
        alert('Por favor seleccione la cuota para procesar el pago.');
        return;
    }
    
    // Obtener datos del formulario
    const amountInput = document.querySelector(`input[name="installment_amount_${installmentId}"]`);
    const paymentMethodInput = document.querySelector(`input[name="installment_method_${installmentId}"]:checked`);
    
    console.log('🔍 Elementos encontrados:', {
        amountInput: !!amountInput,
        paymentMethodInput: !!paymentMethodInput,
        amount: amountInput ? amountInput.value : 'no encontrado',
        method: paymentMethodInput ? paymentMethodInput.value : 'no encontrado'
    });
    
    const amount = amountInput ? amountInput.value : null;
    const paymentMethod = paymentMethodInput ? paymentMethodInput.value : null;
    
    // Validaciones básicas
    if (!amount || parseFloat(amount) <= 0) {
        alert('Por favor ingrese un monto válido.');
        return;
    }
    
    if (!paymentMethod) {
        alert('Por favor seleccione un método de pago.');
        return;
    }
    
    if (paymentMethod === 'yape') {
        const proofInput = document.getElementById(`installment_proof_${installmentId}`);
        console.log('🔍 Validando comprobante Yape:', {
            proofInput: !!proofInput,
            hasFiles: proofInput ? proofInput.files.length : 0
        });
        
        if (!proofInput || !proofInput.files || proofInput.files.length === 0) {
            alert('Por favor adjunte el comprobante de Yape.');
            return;
        }
    }
    
    // Mostrar modal de confirmación
    const paymentMethodText = paymentMethod === 'efectivo' ? 'Efectivo' : 'Yape';
    const confirmMessage = `Cuota: #${installmentId}
Monto: S/. ${parseFloat(amount).toFixed(2)}
Método de pago: ${paymentMethodText}

Esta acción no se puede deshacer.`;
    
    showConfirmModal(confirmMessage, () => {
        // Crear FormData para enviar al servidor
        const formData = new FormData();
        formData.append('installment_id', installmentId);
        formData.append('amount', amount);
        formData.append('payment_method', paymentMethod);
        formData.append('client_id', '{{ $client->id }}');
        
        console.log('📋 FormData creado:', {
            installment_id: installmentId,
            amount: amount,
            payment_method: paymentMethod,
            client_id: '{{ $client->id }}'
        });
        
        // Agregar comprobante si es Yape
        if (paymentMethod === 'yape') {
            const proofInput = document.getElementById(`installment_proof_${installmentId}`);
            formData.append('payment_proof', proofInput.files[0]);
            console.log('📎 Comprobante Yape agregado:', proofInput.files[0].name);
        }
        
        console.log('🚀 Enviando solicitud a:', '{{ route("asesor.collection.payment") }}');
        
        // Enviar al servidor
        fetch('{{ route("asesor.collection.payment") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('📡 Respuesta recibida:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });
            
            if (!response.ok) {
                // Intentar leer el cuerpo como JSON primero (para errores 400 con JSON)
                return response.text().then(text => {
                    try {
                        const jsonData = JSON.parse(text);
                        console.log('❌ Error del servidor (JSON):', jsonData);
                        // Si es JSON, lanzar error con el mensaje del servidor
                        throw new Error(jsonData.message || `HTTP error! status: ${response.status}`);
                    } catch (e) {
                        // Si no es JSON, mostrar el texto plano
                        console.error('❌ Error del servidor (texto):', text);
                        throw new Error(`HTTP error! status: ${response.status} - ${text}`);
                    }
                });
            }
            
            return response.json();
        })
        .then(data => {
            console.log('📊 Datos recibidos:', data);
            
            if (data.success) {
                alert('Pago procesado exitosamente');
                
                // Actualizar la cuota pagada como "pagada" en el DOM
                const paidCheckbox = document.querySelector(`input[data-installment-id="${installmentId}"]`);
                if (paidCheckbox) {
                    paidCheckbox.checked = false;
                    paidCheckbox.disabled = true;
                    paidCheckbox.classList.remove('text-red-600');
                    paidCheckbox.classList.add('text-green-600');
                    
                    // Actualizar el contenedor de la cuota
                    const installmentContainer = paidCheckbox.closest('.flex.items-start');
                    if (installmentContainer) {
                        installmentContainer.classList.remove('border-red-200');
                        installmentContainer.classList.add('border-green-200');
                        installmentContainer.classList.remove('opacity-60');
                        
                        // Actualizar badges
                        const badges = installmentContainer.querySelectorAll('.px-2.py-1.text-xs');
                        badges.forEach(badge => {
                            if (badge.textContent.includes('Más antigua')) {
                                badge.textContent = 'Pagado';
                                badge.classList.remove('bg-red-100', 'text-red-800');
                                badge.classList.add('bg-green-100', 'text-green-800');
                            }
                            if (badge.textContent.includes('Bloqueada')) {
                                badge.remove();
                            }
                        });
                        
                        // Ocultar opciones de pago
                        const paymentOptions = document.getElementById(`payment-options-${installmentId}`);
                        if (paymentOptions) {
                            paymentOptions.classList.add('hidden');
                        }
                        
                        // Mostrar mensaje de pago procesado
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'text-sm text-green-700 bg-green-50 p-2 rounded-lg';
                        messageDiv.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i>Pago procesado correctamente';
                        
                        // Reemplazar el contenido de opciones de pago con el mensaje
                        const parentDiv = paymentOptions.parentNode;
                        if (parentDiv) {
                            parentDiv.replaceChild(messageDiv, paymentOptions);
                        }
                    }
                }
                
                // Desbloquear la siguiente cuota si existe
                setTimeout(() => {
                    checkAndUnlockNextInstallment(installmentId);
                }, 500);
                
            } else {
                alert('Error al procesar el pago: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('❌ Error detallado:', error);
            console.error('❌ Stack trace:', error.stack);
            alert('Error al procesar el pago: ' + error.message);
        });
    });
};

// Función para actualizar el total seleccionado
window.updateSelectedTotal = function() {
    const selectedCheckboxes = document.querySelectorAll('.overdue-checkbox:checked');
    let total = 0;
    
    selectedCheckboxes.forEach(checkbox => {
        total += parseFloat(checkbox.dataset.amount);
    });

    if (total > 0) {
        const paymentAmount = document.getElementById('payment-amount');
        const suggestedPayment = document.getElementById('suggested-payment');
        
        if (paymentAmount) {
            paymentAmount.value = total.toFixed(2);
            paymentAmount.setAttribute('readonly', true);
        }
        
        if (suggestedPayment) {
            suggestedPayment.textContent = total.toFixed(2);
        }
    } else {
        const paymentAmount = document.getElementById('payment-amount');
        const suggestedPayment = document.getElementById('suggested-payment');
        
        if (paymentAmount) {
            paymentAmount.removeAttribute('readonly');
            paymentAmount.value = '';
        }
        
        if (suggestedPayment) {
            const monthlyPayment = document.getElementById('monthly-payment');
            if (monthlyPayment) {
                suggestedPayment.textContent = monthlyPayment.value || '0.00';
            }
        }
    }
};

// Función para desbloquear la siguiente cuota
window.checkAndUnlockNextInstallment = function(paidInstallmentId) {
    console.log('🔓 Buscando siguiente cuota para desbloquear...');
    
    // Encontrar todas las cuotas bloqueadas
    const blockedCheckboxes = document.querySelectorAll('.overdue-checkbox:disabled');
    let nextInstallment = null;
    
    blockedCheckboxes.forEach(checkbox => {
        const container = checkbox.closest('.flex.items-start');
        if (container) {
            // Buscar si esta cuota está marcada como "Bloqueada"
            const blockedBadge = container.querySelector('.bg-yellow-100');
            if (blockedBadge && blockedBadge.textContent.includes('Bloqueada')) {
                if (!nextInstallment) {
                    nextInstallment = {
                        checkbox: checkbox,
                        container: container,
                        id: checkbox.dataset.installmentId
                    };
                }
            }
        }
    });
    
    if (nextInstallment) {
        console.log('🔓 Desbloqueando cuota:', nextInstallment.id);
        
        // Habilitar el checkbox
        nextInstallment.checkbox.disabled = false;
        nextInstallment.checkbox.classList.remove('text-gray-400');
        nextInstallment.checkbox.classList.add('text-red-600');
        
        // Actualizar el contenedor
        nextInstallment.container.classList.remove('border-gray-200', 'opacity-60');
        nextInstallment.container.classList.add('border-red-200');
        
        // Actualizar badges
        const badges = nextInstallment.container.querySelectorAll('.px-2.py-1.text-xs');
        badges.forEach(badge => {
            if (badge.textContent.includes('Bloqueada')) {
                badge.textContent = 'Más antigua - Pagar primero';
                badge.classList.remove('bg-yellow-100', 'text-yellow-800');
                badge.classList.add('bg-red-100', 'text-red-800');
            }
        });
        
        // Eliminar mensaje de bloqueo
        const blockedMessage = nextInstallment.container.querySelector('.text-yellow-700');
        if (blockedMessage) {
            blockedMessage.remove();
        }
        
        // Verificar si las opciones de pago existen, si no, crearlas
        let paymentOptions = document.getElementById(`payment-options-${nextInstallment.id}`);
        if (!paymentOptions) {
            console.log('🔧 Creando opciones de pago para cuota desbloqueada...');
            paymentOptions = createPaymentOptionsForInstallment(nextInstallment.id, nextInstallment.checkbox.dataset.amount);
            
            // Insertar las opciones de pago después del mensaje de monto
            const amountDiv = nextInstallment.container.querySelector('.text-sm.font-medium.mb-3');
            if (amountDiv && amountDiv.nextSibling) {
                amountDiv.parentNode.insertBefore(paymentOptions, amountDiv.nextSibling);
            } else {
                nextInstallment.container.appendChild(paymentOptions);
            }
        } else {
            // Si ya existen, solo mostrarlas
            paymentOptions.classList.remove('hidden');
        }
        
        // Recrear íconos
        lucide.createIcons();
        
        console.log('✅ Cuota desbloqueada correctamente');
    } else {
        console.log('ℹ️ No hay más cuotas para desbloquear');
    }
};

// Función para crear opciones de pago para una cuota
window.createPaymentOptionsForInstallment = function(installmentId, amount) {
    const paymentOptionsDiv = document.createElement('div');
    paymentOptionsDiv.id = `payment-options-${installmentId}`;
    paymentOptionsDiv.className = 'space-y-3 p-3 bg-gray-50 rounded-lg';
    
    paymentOptionsDiv.innerHTML = `
        <div>
            <label class="block text-sm font-medium mb-1">Monto a pagar:</label>
            <input 
                type="number" 
                name="installment_amount_${installmentId}" 
                step="0.01" 
                min="0" 
                max="${amount}"
                value="${amount}"
                placeholder="0.00"
                class="w-full p-2 text-sm font-semibold border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500"
            >
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-1">Método de pago:</label>
            <div class="space-y-1">
                <label class="flex items-center space-x-2">
                    <input type="radio" name="installment_method_${installmentId}" value="efectivo" checked class="h-4 w-4">
                    <span class="text-sm">Efectivo</span>
                </label>
                <label class="flex items-center space-x-2">
                    <input type="radio" name="installment_method_${installmentId}" value="yape" class="h-4 w-4">
                    <span class="text-sm">Yape</span>
                </label>
            </div>
        </div>
        
        <div id="yape-proof-${installmentId}" class="hidden border-2 border-dashed border-green-300 rounded-lg p-4 bg-green-50">
            <div class="text-center">
                <div class="border-2 border-dashed border-green-400 rounded-lg p-4 cursor-pointer hover:bg-green-100 transition-colors" onclick="document.getElementById('installment_proof_${installmentId}').click()">
                    <i data-lucide="upload-cloud" class="w-8 h-8 text-green-500 mx-auto mb-2"></i>
                    <p class="text-green-700 text-sm font-medium">Comprobante Yape</p>
                    <p class="text-green-600 text-xs">Haz clic para seleccionar</p>
                </div>
                
                <input type="file" 
                       name="installment_proof_${installmentId}" 
                       id="installment_proof_${installmentId}" 
                       accept="image/jpeg,image/jpg,image/png" 
                       class="hidden"
                       onchange="previewInstallmentProof(${installmentId}, this)">
                       
                <div id="installment-preview-${installmentId}" class="mt-2 hidden">
                    <img src="" alt="Vista previa" class="mx-auto max-h-32 rounded-lg shadow-md">
                    <button type="button" class="mt-1 px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600" onclick="removeInstallmentProof(${installmentId})">
                        Quitar
                    </button>
                </div>
            </div>
        </div>
        
        <div class="pt-2 border-t border-gray-200">
            <button type="button" 
                    onclick="processIndividualInstallment(${installmentId})"
                    class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                <i data-lucide="credit-card" class="w-4 h-4"></i>
                Procesar Pago
            </button>
        </div>
    `;
    
    return paymentOptionsDiv;
};

// Función de depuración para mostrar estado de cuotas
window.debugInstallmentsStatus = function() {
    console.log('🔍 Estado actual de cuotas:');
    const allCheckboxes = document.querySelectorAll('.overdue-checkbox');
    
    allCheckboxes.forEach((checkbox, index) => {
        const container = checkbox.closest('.flex.items-start');
        const installmentId = checkbox.dataset.installmentId;
        const isDisabled = checkbox.disabled;
        const isChecked = checkbox.checked;
        
        // Buscar badges
        const badges = container.querySelectorAll('.px-2.py-1.text-xs');
        let statusText = 'unknown';
        let statusColor = 'unknown';
        
        badges.forEach(badge => {
            if (badge.textContent.includes('Pagado')) {
                statusText = 'Pagado';
                statusColor = 'green';
            } else if (badge.textContent.includes('Más antigua')) {
                statusText = 'Disponible';
                statusColor = 'red';
            } else if (badge.textContent.includes('Bloqueada')) {
                statusText = 'Bloqueada';
                statusColor = 'yellow';
            }
        });
        
        console.log(`  Cuota ${index + 1} (ID: ${installmentId}): ${statusText} | Disabled: ${isDisabled} | Checked: ${isChecked} | Color: ${statusColor}`);
    });
};

// Función para consultar estado real de cuotas desde el backend
window.checkRealInstallmentsStatus = function() {
    console.log('🔍 Consultando estado real de cuotas desde el backend...');
    
    fetch('{{ route("asesor.collection") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('📊 Estado real de cuotas desde backend:', data);
        
        // Si el backend devuelve datos de cuotas, mostrarlos en consola
        if (data.installments && Array.isArray(data.installments)) {
            data.installments.forEach((installment, index) => {
                console.log(`  Cuota ${index + 1} (ID: ${installment.id}): Status: ${installment.status} | Amount: ${installment.amount} | Paid: ${installment.paid_amount}`);
            });
        }
    })
    .catch(error => {
        console.error('❌ Error consultando estado:', error);
    });
};

// Función para resetear cuotas (para pruebas)
window.resetInstallmentsForTesting = function() {
    console.log('🔄 Reseteando cuotas para pruebas...');
    
    const allCheckboxes = document.querySelectorAll('.overdue-checkbox');
    
    allCheckboxes.forEach((checkbox, index) => {
        const container = checkbox.closest('.flex.items-start');
        const installmentId = checkbox.dataset.installmentId;
        
        // Resetear checkbox
        checkbox.checked = false;
        checkbox.disabled = false;
        checkbox.classList.remove('text-green-600', 'text-gray-400');
        checkbox.classList.add('text-red-600');
        
        // Resetear contenedor
        container.classList.remove('border-green-200', 'border-gray-200', 'opacity-60');
        container.classList.add('border-red-200');
        
        // Resetear badges a estado inicial
        const badges = container.querySelectorAll('.px-2.py-1.text-xs');
        badges.forEach(badge => {
            if (index === 0) {
                badge.textContent = 'Más antigua - Pagar primero';
                badge.classList.remove('bg-yellow-100', 'text-yellow-800', 'bg-green-100', 'text-green-800');
                badge.classList.add('bg-red-100', 'text-red-800');
            } else {
                badge.textContent = 'Bloqueada';
                badge.classList.remove('bg-red-100', 'text-red-800', 'bg-green-100', 'text-green-800');
                badge.classList.add('bg-yellow-100', 'text-yellow-800');
            }
        });
        
        // Eliminar mensajes de estado
        const statusMessages = container.querySelectorAll('.text-green-700, .text-yellow-700');
        statusMessages.forEach(msg => msg.remove());
        
        // Mostrar opciones de pago para la primera cuota, ocultar las demás
        const paymentOptions = document.getElementById(`payment-options-${installmentId}`);
        if (index === 0 && paymentOptions) {
            paymentOptions.classList.remove('hidden');
        } else if (paymentOptions) {
            paymentOptions.classList.add('hidden');
        }
    });
    
    // Recrear íconos
    lucide.createIcons();
    
    console.log('✅ Cuotas reseteadas para pruebas');
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM cargado, iniciando configuración');
    lucide.createIcons();

    console.log('🔍 Buscando elementos del DOM...');
    const loanCheckbox = document.querySelector('input[name="payment_type[]"][value="loan"]');
    const savingsCheckbox = document.querySelector('input[name="payment_type[]"][value="savings"]');
    const loanSection = document.getElementById('loan-section');
    const savingsSection = document.getElementById('savings-section');

    console.log('📋 Elementos encontrados:', {
        loanCheckbox: !!loanCheckbox,
        savingsCheckbox: !!savingsCheckbox,
        loanSection: !!loanSection,
        savingsSection: !!savingsSection
    });

    function toggleSections() {
        console.log('🔄 toggleSections() llamado');
        console.log('📊 Estado checkboxes:', {
            loanChecked: loanCheckbox ? loanCheckbox.checked : 'no existe',
            savingsChecked: savingsCheckbox ? savingsCheckbox.checked : 'no existe'
        });
        
        // Mostrar/ocultar sección de préstamos
        if (loanSection) {
            if (loanCheckbox.checked) {
                console.log('✅ Mostrando sección de préstamos');
                loanSection.style.display = 'block';
                // Hacer requeridos los campos de préstamos
                document.querySelector('input[name="amount"]').setAttribute('required', '');
                document.querySelector('input[name="payment_method"]').setAttribute('required', '');
            } else {
                console.log('❌ Ocultando sección de préstamos');
                loanSection.style.display = 'none';
                // Quitar required de campos de préstamos
                document.querySelector('input[name="amount"]').removeAttribute('required');
                document.querySelector('input[name="payment_method"]').removeAttribute('required');
            }
        }

        // Mostrar/ocultar sección de ahorros
        if (savingsSection) {
            if (savingsCheckbox.checked) {
                savingsSection.style.display = 'block';
                // Hacer requeridos los campos de ahorros
                document.querySelector('input[name="savings_amount"]').setAttribute('required', '');
                document.querySelector('input[name="savings_payment_method"]').setAttribute('required', '');
            } else {
                savingsSection.style.display = 'none';
                // Quitar required de campos de ahorros
                document.querySelector('input[name="savings_amount"]').removeAttribute('required');
                document.querySelector('input[name="savings_payment_method"]').removeAttribute('required');
            }
        }
    }

    // Event listeners para los checkboxes
    if (loanCheckbox) {
        loanCheckbox.addEventListener('change', toggleSections);
    }
    if (savingsCheckbox) {
        savingsCheckbox.addEventListener('change', toggleSections);
    }

    // Estado inicial
    toggleSections();

    // Vista previa de imágenes para préstamos
    const paymentProofInput = document.getElementById('payment_proof');
    const uploadArea = document.getElementById('upload-area');
    const previewContainer = document.getElementById('preview-container');
    const imagePreview = document.getElementById('image-preview');
    const removeImageBtn = document.getElementById('remove-image');

    // Vista previa de imágenes para ahorros
    const savingsPaymentProofInput = document.getElementById('savings_payment_proof');
    const savingsUploadArea = document.getElementById('savings-upload-area');
    const savingsPreviewContainer = document.getElementById('savings-preview-container');
    const savingsImagePreview = document.getElementById('savings-image-preview');
    const savingsRemoveImageBtn = document.getElementById('savings-remove-image');

    // Función para configurar vista previa
    function setupImagePreview(input, uploadArea, previewContainer, imagePreview, removeBtn) {
        if (!input || !uploadArea) return;

        // Click en el área de subida
        uploadArea.addEventListener('click', function() {
            input.click();
        });

        // Cambio en el input de archivo
        input.addEventListener('change', function(e) {
            handleFileSelect(e.target.files[0], previewContainer, imagePreview, uploadArea, removeBtn);
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('bg-green-200', 'border-green-500');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('bg-green-200', 'border-green-500');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('bg-green-200', 'border-green-500');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0], previewContainer, imagePreview, uploadArea, removeBtn);
            }
        });

        // Botón de quitar imagen
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                input.value = '';
                previewContainer.classList.add('hidden');
                uploadArea.classList.remove('hidden');
                lucide.createIcons();
            });
        }
    }

    function handleFileSelect(file, previewContainer, imagePreview, uploadArea, removeBtn) {
        // Validar tipo de archivo
        if (!file.type.match('image.*')) {
            alert('Por favor selecciona un archivo de imagen (JPG o PNG)');
            return;
        }

        // Validar tamaño (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('El archivo no debe ser mayor a 5MB');
            return;
        }

        // Mostrar vista previa
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            previewContainer.classList.remove('hidden');
            uploadArea.classList.add('hidden');
            lucide.createIcons();
        };
        reader.readAsDataURL(file);
    }

    // Configurar vista previa para préstamos
    setupImagePreview(paymentProofInput, uploadArea, previewContainer, imagePreview, removeImageBtn);
    
    // Configurar vista previa para ahorros
    setupImagePreview(savingsPaymentProofInput, savingsUploadArea, savingsPreviewContainer, savingsImagePreview, savingsRemoveImageBtn);

    // Show/hide Yape proof section based on payment method
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    const savingsPaymentMethodRadios = document.querySelectorAll('input[name="savings_payment_method"]');
    const yapeProofSection = document.getElementById('yape-proof-section');
    const savingsYapeProofSection = document.getElementById('savings-yape-proof-section');

    function toggleYapeProof() {
        const loanPaymentMethod = document.querySelector('input[name="payment_method"]:checked');
        const savingsPaymentMethod = document.querySelector('input[name="savings_payment_method"]:checked');
        
        // Toggle Yape proof for loans
        if (loanPaymentMethod && loanPaymentMethod.value === 'yape') {
            yapeProofSection.classList.remove('hidden');
        } else {
            yapeProofSection.classList.add('hidden');
        }
        
        // Toggle Yape proof for savings
        if (savingsPaymentMethod && savingsPaymentMethod.value === 'yape') {
            savingsYapeProofSection.classList.remove('hidden');
        } else {
            savingsYapeProofSection.classList.add('hidden');
        }
    }

    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', toggleYapeProof);
    });

    savingsPaymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', toggleYapeProof);
    });

    // Funcionalidad para cuotas retrasadas
    console.log('🔍 Inicializando funcionalidad de cuotas retrasadas...');
    const loanSelect = document.getElementById('loan-select');
    const loanIdHidden = document.getElementById('loan-id');
    const overdueInstallmentsSection = document.getElementById('overdue-installments-section');
    const overdueInstallmentsList = document.getElementById('overdue-installments-list');
    const regularPaymentSection = document.getElementById('regular-payment-section');
    const regularPaymentMethod = document.getElementById('regular-payment-method');
    const paymentAmount = document.getElementById('payment-amount');
    const suggestedPayment = document.getElementById('suggested-payment');

    console.log('📋 Elementos de cuotas retrasadas:', {
        loanSelect: !!loanSelect,
        loanIdHidden: !!loanIdHidden,
        overdueInstallmentsSection: !!overdueInstallmentsSection,
        overdueInstallmentsList: !!overdueInstallmentsList,
        regularPaymentSection: !!regularPaymentSection,
        regularPaymentMethod: !!regularPaymentMethod,
        paymentAmount: !!paymentAmount,
        suggestedPayment: !!suggestedPayment
    });

    // Datos de préstamos (pasados desde Laravel)
    const loansData = @json($client->loans);

    function checkOverdueInstallments(loanId) {
        console.log('🔍 Verificando cuotas retrasadas para préstamo ID:', loanId);
        
        const loan = loansData.find(l => l.id == loanId);
        console.log('📋 Préstamo encontrado:', loan);
        
        if (!loan) {
            console.log('❌ Préstamo no encontrado');
            return [];
        }
        
        console.log('📊 Todas las cuotas:', loan.installments);

        const today = new Date();
        // Configurar al final del día para incluir cuotas de hoy
        today.setHours(23, 59, 59, 999);

        // Filtrar cuotas que realmente estén pendientes y retrasadas
        const overdueInstallments = loan.installments.filter(installment => {
            const dueDate = new Date(installment.due_date);
            
            // Debug extendido para todas las cuotas
            console.log(`🔍 Analizando cuota #${installment.installment_number}:`, {
                due_date: installment.due_date,
                due_date_obj: dueDate,
                due_date_valid: !isNaN(dueDate.getTime()),
                status: installment.status,
                today: today,
                today_string: today.toDateString(),
                comparison: dueDate <= today,
                will_be_overdue: installment.status === 'pending' && dueDate <= today
            });
            
            // Una cuota está retrasada si su fecha de vencimiento es anterior o igual a hoy
            // y su estado es 'pending'
            const isOverdue = installment.status === 'pending' && dueDate <= today;
            
            return isOverdue;
        });

        console.log('✅ Cuotas retrasadas encontradas:', overdueInstallments.length);
        return overdueInstallments;
    }

    function displayOverdueInstallments(overdueInstallments) {
        if (!overdueInstallments || overdueInstallments.length === 0) {
            overdueInstallmentsSection.classList.add('hidden');
            regularPaymentSection.classList.remove('hidden');
            return;
        }

        regularPaymentSection.classList.add('hidden');
        overdueInstallmentsSection.classList.remove('hidden');

        let html = '';
        overdueInstallments.sort((a, b) => new Date(a.due_date) - new Date(b.due_date));

        // Identificar la cuota más antigua (primera en la lista ordenada)
        const oldestInstallment = overdueInstallments[0];
        console.log('📅 Cuota más antigua:', oldestInstallment);

        overdueInstallments.forEach((installment, index) => {
            // Debug del formato de fecha original
            console.log(`🔍 Fecha original cuota #${installment.installment_number}:`, installment.due_date);
            
            // Parsear fecha directamente (viene en formato ISO 8601)
            const dueDate = new Date(installment.due_date);
            const now = new Date();
            
            // Calcular días de retraso comparando solo fechas (sin horas)
            const dueDateOnly = new Date(dueDate.getFullYear(), dueDate.getMonth(), dueDate.getDate());
            const todayOnly = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            
            const diffTime = todayOnly - dueDateOnly;
            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
            
            console.log(`🔍 Cálculo días retraso - Cuota #${installment.installment_number}:`, {
                due_date: installment.due_date,
                due_date_obj: dueDate,
                due_date_only: dueDateOnly,
                now: now,
                now_only: todayOnly,
                diffTime: diffTime,
                diffDays: diffDays,
                daysOverdue: diffDays > 0 ? diffDays : 0,
                comparison: dueDateOnly <= todayOnly
            });
            
            // Determinar si esta cuota puede ser seleccionada
            const isOldest = installment.id === oldestInstallment.id;
            const isDisabled = !isOldest;
            
            // Usar el cálculo correcto de días de retraso
            const daysOverdue = diffDays > 0 ? diffDays : 0;
            
            console.log(`🔍 Cuota #${installment.installment_number}:`, {
                isOldest: isOldest,
                isDisabled: isDisabled,
                dueDate: dueDate,
                daysOverdue: daysOverdue
            });
            
            html += `
                <div class="flex items-start gap-3 p-4 bg-white border ${isDisabled ? 'border-gray-200 opacity-60' : 'border-red-200'} rounded-lg">
                    <div class="pt-1">
                        <input type="checkbox" 
                               class="overdue-checkbox h-4 w-4 ${isDisabled ? 'text-gray-400' : 'text-red-600'}" 
                               data-installment-id="${installment.id}" 
                               data-amount="${installment.amount}"
                               ${isDisabled ? 'disabled' : ''}
                               onchange="toggleInstallmentPayment(${installment.id}, this.checked)">
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm font-medium">Cuota #${installment.installment_number}</span>
                            <span class="px-2 py-1 text-xs ${isOldest ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600'} rounded-full">
                                ${isOldest ? 'Más antigua - Pagar primero' : `${daysOverdue} días retrasado`}
                            </span>
                            ${isDisabled ? '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Bloqueada</span>' : ''}
                        </div>
                        <div class="text-sm text-gray-600 mb-2">
                            Vencimiento: ${dueDate.toLocaleDateString('es-PE')}
                        </div>
                        <div class="text-sm font-medium mb-3">
                            Monto original: S/. ${parseFloat(installment.amount).toFixed(2)}
                        </div>
                        ${isDisabled ? `
                            <div class="text-sm text-yellow-700 bg-yellow-50 p-2 rounded-lg">
                                <i data-lucide="lock" class="w-4 h-4 inline mr-1"></i>
                                Debe pagar la cuota más antigua primero
                            </div>
                        ` : `
                            <div id="payment-options-${installment.id}" class="hidden space-y-3 p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Monto a pagar:</label>
                                    <input 
                                        type="number" 
                                        name="installment_amount_${installment.id}" 
                                        step="0.01" 
                                        min="0" 
                                        max="${installment.amount}"
                                        value="${installment.amount}"
                                        placeholder="0.00"
                                        class="w-full p-2 text-sm font-semibold border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                    >
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-1">Método de pago:</label>
                                    <div class="space-y-1">
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" name="installment_method_${installment.id}" value="efectivo" checked class="h-4 w-4">
                                            <span class="text-sm">Efectivo</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" name="installment_method_${installment.id}" value="yape" class="h-4 w-4">
                                            <span class="text-sm">Yape</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="yape-proof-${installment.id}" class="hidden border-2 border-dashed border-green-300 rounded-lg p-4 bg-green-50">
                                    <div class="text-center">
                                        <div class="border-2 border-dashed border-green-400 rounded-lg p-4 cursor-pointer hover:bg-green-100 transition-colors" onclick="document.getElementById('installment_proof_${installment.id}').click()">
                                            <i data-lucide="upload-cloud" class="w-8 h-8 text-green-500 mx-auto mb-2"></i>
                                            <p class="text-green-700 text-sm font-medium">Comprobante Yape</p>
                                            <p class="text-green-600 text-xs">Haz clic para seleccionar</p>
                                        </div>
                                        
                                        <input type="file" 
                                               name="installment_proof_${installment.id}" 
                                               id="installment_proof_${installment.id}" 
                                               accept="image/jpeg,image/jpg,image/png" 
                                               class="hidden"
                                               onchange="previewInstallmentProof(${installment.id}, this)">
                                               
                                        <div id="installment-preview-${installment.id}" class="mt-2 hidden">
                                            <img src="" alt="Vista previa" class="mx-auto max-h-32 rounded-lg shadow-md">
                                            <button type="button" class="mt-1 px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600" onclick="removeInstallmentProof(${installment.id})">
                                                Quitar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="pt-2 border-t border-gray-200">
                                    <button type="button" 
                                            onclick="processIndividualInstallment(${installment.id})"
                                            class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                                        Procesar Pago
                                    </button>
                                </div>
                            </div>
                        `}
                    </div>
                </div>
            `;
        });

        overdueInstallmentsList.innerHTML = html;
        lucide.createIcons();
    }

    // Event listeners
    function initializeLoanCheck() {
        console.log('🚀 Inicializando verificación de préstamos...');
        
        let selectedLoanId = null;
        
        if (loanSelect) {
            console.log('📋 Usando loan-select (múltiples préstamos)');
            selectedLoanId = loanSelect.value;
            console.log('🔄 Préstamo seleccionado del select:', selectedLoanId);
            
            loanSelect.addEventListener('change', function() {
                const selectedLoanId = this.value;
                console.log('🔄 Cambió selección de préstamo. ID seleccionado:', selectedLoanId);
                
                if (selectedLoanId) {
                    console.log('🔍 Buscando cuotas retrasadas...');
                    const overdueInstallments = checkOverdueInstallments(selectedLoanId);
                    console.log('📊 Resultado de cuotas retrasadas:', overdueInstallments);
                    displayOverdueInstallments(overdueInstallments);
                    
                    // Actualizar cuota sugerida
                    const selectedOption = this.options[this.selectedIndex];
                    suggestedPayment.textContent = selectedOption.dataset.monthlyPayment || '0.00';
                    console.log('💰 Cuota sugerida actualizada:', selectedOption.dataset.monthlyPayment);
                } else {
                    // Si no hay préstamo seleccionado, mostrar sección de pago regular
                    overdueInstallmentsSection.classList.add('hidden');
                    regularPaymentSection.classList.remove('hidden');
                    regularPaymentMethod.classList.remove('hidden');
                }
            });
        } else if (loanIdHidden) {
            console.log('📋 Usando loan-id (un solo préstamo)');
            selectedLoanId = loanIdHidden.value;
            console.log('🔄 Préstamo único detectado, ID:', selectedLoanId);
            
            // Para préstamo único, verificar inmediatamente
            if (selectedLoanId) {
                console.log('🔍 Buscando cuotas retrasadas para préstamo único...');
                const overdueInstallments = checkOverdueInstallments(selectedLoanId);
                console.log('📊 Resultado de cuotas retrasadas:', overdueInstallments);
                displayOverdueInstallments(overdueInstallments);
                
                // Actualizar cuota sugerida
                const monthlyPayment = document.getElementById('monthly-payment');
                if (monthlyPayment) {
                    suggestedPayment.textContent = monthlyPayment.value || '0.00';
                    console.log('� Cuota sugerida actualizada:', monthlyPayment.value);
                }
            }
        } else {
            console.log('❌ No se encontró ningún selector de préstamo');
        }
    }

    // Inicializar cuando se muestra la sección de préstamos
    if (loanCheckbox) {
        loanCheckbox.addEventListener('change', function() {
            if (this.checked) {
                console.log('✅ Préstamo seleccionado, inicializando verificación');
                setTimeout(initializeLoanCheck, 100); // Pequeño delay para asegurar que el DOM esté listo
            }
        });
        
        // Si ya está seleccionado al cargar
        if (loanCheckbox.checked) {
            console.log('🚀 Préstamo ya estaba seleccionado, inicializando ahora');
            setTimeout(initializeLoanCheck, 100);
        }
    }

    // Interceptar el envío del formulario para pagos de ahorros y préstamos regulares
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('🔍 Formulario submit iniciado');
            
            // Verificar si es un pago de ahorros
            const savingsCheckbox = document.querySelector('input[name="payment_type[]"][value="savings"]');
            const savingsAmount = document.querySelector('input[name="savings_amount"]');
            const loanCheckbox = document.querySelector('input[name="payment_type[]"][value="loan"]');
            
            console.log('🔍 Estado de checkboxes:', {
                savingsCheckbox: !!savingsCheckbox,
                savingsChecked: savingsCheckbox ? savingsCheckbox.checked : 'no encontrado',
                savingsAmount: !!savingsAmount,
                savingsAmountValue: savingsAmount ? savingsAmount.value : 'no encontrado',
                loanCheckbox: !!loanCheckbox,
                loanChecked: loanCheckbox ? loanCheckbox.checked : 'no encontrado'
            });
            
            if (savingsCheckbox && savingsCheckbox.checked && savingsAmount && parseFloat(savingsAmount.value) > 0) {
                console.log('✅ Detectado pago de ahorros, mostrando confirmación');
                e.preventDefault(); // Prevenir el envío normal
                
                // Mostrar confirmación para pago de ahorros
                const savingsPaymentMethod = document.querySelector('input[name="savings_payment_method"]:checked');
                const paymentMethodText = savingsPaymentMethod ? (savingsPaymentMethod.value === 'efectivo' ? 'Efectivo' : 'Yape') : 'No seleccionado';
                const confirmMessage = `Depósito de Ahorros
Monto: S/. ${parseFloat(savingsAmount.value).toFixed(2)}
Método de pago: ${paymentMethodText}

Esta acción no se puede deshacer.`;
                
                console.log('📋 Mensaje de confirmación:', confirmMessage);
                
                showConfirmModal(confirmMessage, () => {
                    console.log('🚀 Usuario confirmó, enviando AJAX...');
                    // Crear FormData para el pago de ahorros
                    const formData = new FormData(form);
                    
                    // Enviar con AJAX
                    fetch('{{ route("asesor.collection.payment") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('📊 Respuesta AJAX:', data);
                        if (data.success) {
                            // Mostrar mensaje de éxito
                            showSuccessMessage('Depósito de ahorros procesado con éxito');
                            
                            // Resetear formulario de ahorros
                            savingsAmount.value = '';
                            const savingsPaymentMethod = document.querySelector('input[name="savings_payment_method"]:checked');
                            if (savingsPaymentMethod) {
                                savingsPaymentMethod.checked = false;
                            }
                            document.querySelector('input[name="savings_payment_method"][value="efectivo"]').checked = true;
                            
                            // Ocultar sección de comprobante Yape si está visible
                            const savingsYapeSection = document.getElementById('savings-yape-proof-section');
                            if (savingsYapeSection) {
                                savingsYapeSection.classList.add('hidden');
                            }
                            
                            // Limpiar preview de imagen
                            const savingsPreview = document.getElementById('savings-preview-container');
                            const savingsImage = document.getElementById('savings-image-preview');
                            if (savingsPreview) {
                                savingsPreview.classList.add('hidden');
                            }
                            if (savingsImage) {
                                savingsImage.src = '';
                            }
                            
                        } else {
                            // Mostrar mensaje de error
                            showErrorMessage(data.message || 'Error al procesar el depósito');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showErrorMessage('Error de conexión al procesar el depósito');
                    });
                });
                
            } else if (loanCheckbox && loanCheckbox.checked) {
                console.log('✅ Detectado pago regular de préstamo, mostrando confirmación');
                e.preventDefault(); // Prevenir el envío normal
                
                // Validar y mostrar confirmación para pago regular de préstamo
                const paymentAmount = document.getElementById('payment-amount');
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
                
                if (!paymentAmount || parseFloat(paymentAmount.value) <= 0) {
                    alert('Por favor ingrese un monto válido.');
                    return;
                }
                
                if (!paymentMethod) {
                    alert('Por favor seleccione un método de pago.');
                    return;
                }
                
                if (paymentMethod.value === 'yape') {
                    const proofInput = document.getElementById('payment-proof');
                    if (!proofInput || !proofInput.files || proofInput.files.length === 0) {
                        alert('Por favor adjunte el comprobante de Yape.');
                        return;
                    }
                }
                
                // Mostrar confirmación para pago regular
                const paymentMethodText = paymentMethod.value === 'efectivo' ? 'Efectivo' : 'Yape';
                const confirmMessage = `Pago Regular de Préstamo
Monto: S/. ${parseFloat(paymentAmount.value).toFixed(2)}
Método de pago: ${paymentMethodText}

Esta acción no se puede deshacer.`;
                
                showConfirmModal(confirmMessage, () => {
                    // Enviar formulario normalmente
                    form.submit();
                });
            } else {
                console.log('❌ No se detectó ningún tipo de pago válido, enviando formulario normalmente');
                // Si no es ni pago de ahorros ni pago regular, dejar que el formulario se envíe normalmente
            }
        });
    }

    function showSuccessMessage(message) {
        // Crear y mostrar mensaje de éxito
        const successDiv = document.createElement('div');
        successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center gap-3';
        successDiv.innerHTML = `
            <i data-lucide="check-circle" class="w-6 h-6"></i>
            <div>
                <p class="font-medium">${message}</p>
                <p class="text-sm opacity-90">El depósito ha sido registrado correctamente</p>
            </div>
        `;
        
        document.body.appendChild(successDiv);
        lucide.createIcons();
        
        // Remover después de 5 segundos
        setTimeout(() => {
            if (successDiv.parentNode) {
                successDiv.parentNode.removeChild(successDiv);
            }
        }, 5000);
    }

    function showErrorMessage(message) {
        // Crear y mostrar mensaje de error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 flex items-center gap-3';
        errorDiv.innerHTML = `
            <i data-lucide="alert-circle" class="w-6 h-6"></i>
            <div>
                <p class="font-medium">${message}</p>
                <p class="text-sm opacity-90">Por favor intente nuevamente</p>
            </div>
        `;
        
        document.body.appendChild(errorDiv);
        lucide.createIcons();
        
        // Remover después de 5 segundos
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 5000);
    }

    if (selectAllOverdue) {
        selectAllOverdue.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.overdue-checkbox:not(:disabled)');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                // Disparar el evento onchange para mostrar/ocultar opciones
                const event = new Event('change', { bubbles: true });
                checkbox.dispatchEvent(event);
            });
            updateSelectedTotal();
        });
    }

    if (paySelectedOverdue) {
        paySelectedOverdue.addEventListener('click', function() {
            const selectedCheckboxes = document.querySelectorAll('.overdue-checkbox:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Por favor seleccione al menos una cuota para pagar.');
                return;
            }
            
            // El formulario se enviará normalmente con las cuotas seleccionadas
            // El backend procesará las cuotas marcadas
            document.querySelector('form').submit();
        });
    }

    // Event listener para checkboxes de cuotas vencidas
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('overdue-checkbox')) {
            updateSelectedTotal();
        }
    });

});
</script>
@endpush

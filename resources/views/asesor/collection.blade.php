@extends('layouts.app')

@section('title', 'Cobros - Asesor')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="p-2 bg-blue-600 rounded-lg">
                        <i data-lucide="dollar-sign" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Registrar Pagos</h1>
                        <p class="text-sm text-gray-500">
                            Cliente: <span class="font-medium text-gray-900">{{ $client->full_name }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a 
                        href="{{ route('asesor.dashboard') }}" 
                        class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors"
                    >
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                                        <select name="loan_id" class="w-full p-3 border border-blue-200 rounded-md bg-white text-sm">
                                            <option value="">Seleccione un préstamo...</option>
                                            @foreach($client->loans as $loan)
                                                <option value="{{ $loan->id }}">
                                                    {{ $loan->codigo ? 'Préstamo ' . $loan->codigo : 'Préstamo #' . $loan->id }} - 
                                                    Cuota mensual: {{ number_format($loan->monthly_payment, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="loan_id" value="{{ $client->loans->first()->id }}">
                                @endif

                                <div>
                                    <label class="block text-sm font-medium mb-2">Monto del pago</label>
                                    <input 
                                        type="number" 
                                        name="amount" 
                                        step="0.01" 
                                        min="0" 
                                        placeholder="0.00"
                                        class="w-full p-3 text-lg font-semibold border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                                    >
                                    <p class="text-xs text-gray-500 mt-1">
                                        Cuota sugerida: S/. {{ number_format($client->loans->first()->monthly_payment, 2) }}
                                    </p>
                                </div>

                                <div class="mt-4">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();

    const loanCheckbox = document.querySelector('input[name="payment_type[]"][value="loan"]');
    const savingsCheckbox = document.querySelector('input[name="payment_type[]"][value="savings"]');
    const loanSection = document.getElementById('loan-section');
    const savingsSection = document.getElementById('savings-section');

    function toggleSections() {
        // Mostrar/ocultar sección de préstamos
        if (loanSection) {
            if (loanCheckbox.checked) {
                loanSection.style.display = 'block';
                // Hacer requeridos los campos de préstamos
                document.querySelector('input[name="amount"]').setAttribute('required', '');
                document.querySelector('input[name="payment_method"]').setAttribute('required', '');
            } else {
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
});
</script>
@endpush

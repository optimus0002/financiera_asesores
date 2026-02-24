@extends('layouts.app')

@section('title', 'Panel de Control - Asesor')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center space-x-4">
                    <div class="p-2 bg-blue-600 rounded-lg">
                        <i data-lucide="home" class="w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Panel de Control</h1>
                        <p class="text-sm text-gray-500">
                            Bienvenido de vuelta, <span class="font-medium text-gray-900">{{ Auth::user()->full_name }}</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a
                        href="{{ route('asesor.reports') }}"
                        class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        Reportes del Día
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button
                            type="submit"
                            class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-300 transition-colors">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white border-0 shadow-lg rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Clientes</p>
                        <p class="text-3xl font-bold mt-2">{{ $totalClients }}</p>
                    </div>
                    <div class="p-3 bg-blue-400 bg-opacity-30 rounded-full">
                        <i data-lucide="users" class="w-8 h-8 text-white"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white border-0 shadow-lg rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Préstamos Activos</p>
                        <p class="text-3xl font-bold mt-2">{{ $activeLoans }}</p>
                    </div>
                    <div class="p-3 bg-green-400 bg-opacity-30 rounded-full">
                        <i data-lucide="trending-up" class="w-8 h-8 text-white"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white border-0 shadow-lg rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Pagos del Día</p>
                        <p class="text-3xl font-bold mt-2">{{ number_format($todayPaymentsTotal, 2) }}</p>
                    </div>
                    <div class="p-3 bg-purple-400 bg-opacity-30 rounded-full">
                        <i data-lucide="dollar-sign" class="w-8 h-8 text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Search Section -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-lg border-0 rounded-lg">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b p-6">
                        <h2 class="flex items-center gap-2 text-xl font-semibold">
                            <i data-lucide="search" class="w-5 h-5 text-blue-600"></i>
                            Búsqueda Rápida de Clientes
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Encuentre clientes por DNI, nombre o correo electrónico
                        </p>
                    </div>
                    <div class="p-6">
                        <form id="searchForm" class="space-y-6">
                            <div class="relative">
                                <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                                <input
                                    type="text"
                                    id="searchInput"
                                    placeholder="Ingrese DNI, nombre o correo del cliente..."
                                    class="w-full pl-12 pr-4 py-3 text-lg border border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <button
                                type="submit"
                                class="w-full h-12 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                                <i data-lucide="search" class="w-5 h-5 mr-2"></i>
                                <span id="searchButtonText">Buscar Cliente</span>
                            </button>
                        </form>

                        <!-- Loading State -->
                        <div id="loadingState" class="mt-8 space-y-4 hidden">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gray-200 rounded-full animate-pulse"></div>
                                <div class="space-y-2 flex-1">
                                    <div class="h-4 bg-gray-200 rounded w-48 animate-pulse"></div>
                                    <div class="h-3 bg-gray-200 rounded w-32 animate-pulse"></div>
                                </div>
                            </div>
                            <div class="h-20 bg-gray-200 rounded animate-pulse"></div>
                        </div>

                        <!-- Error State -->
                        <div id="errorState" class="mt-6 hidden">
                            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                <div class="flex items-center">
                                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-500 mr-2"></i>
                                    <span class="text-red-700" id="errorMessage"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Client Details -->
                        <div id="clientDetails" class="mt-8 space-y-6 hidden">
                            <!-- Content will be populated by JavaScript -->
                        </div>
                                                <div class="bg-white shadow-lg border-0 rounded-lg mt-6">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b p-6">
                                <h2 class="flex items-center gap-2 text-xl font-semibold">
                                    <i data-lucide="calculator" class="w-5 h-5 text-blue-600"></i>
                                    Cierre de Caja
                                </h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-green-500 rounded-lg">
                                                <i data-lucide="trending-up" class="w-5 h-5 text-white"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-green-600 font-medium">Total Ingresos</p>
                                                <p class="text-2xl font-bold text-green-700" id="totalIncome">S/. 0.00</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-blue-500 rounded-lg">
                                                <i data-lucide="credit-card" class="w-5 h-5 text-white"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-blue-600 font-medium">Pagos Yape</p>
                                                <p class="text-2xl font-bold text-blue-700" id="yapePayments">S/. 0.00</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2 bg-orange-500 rounded-lg">
                                                <i data-lucide="dollar-sign" class="w-5 h-5 text-white"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm text-orange-600 font-medium">Pagos Efectivo</p>
                                                <p class="text-2xl font-bold text-orange-700" id="cashPayments">S/. 0.00</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <div id="cashClosingButtonContainer">
                                        <button
                                            onclick="togglePaymentOptions()"
                                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                            <i data-lucide="calculator" class="w-5 h-5"></i>
                                            Realizar Cierre de Caja
                                        </button>
                                    </div>

                                    <div id="cashClosingAlreadyDoneContainer" class="hidden">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                            <div class="flex items-center gap-3">
                                                <div class="p-2 bg-yellow-500 rounded-lg">
                                                    <i data-lucide="check-circle" class="w-5 h-5 text-white"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-yellow-800 font-medium">Caja ya cerrada</p>
                                                    <p class="text-xs text-yellow-600">El cierre de caja ya fue realizado hoy. Vuelve mañana para realizar un nuevo cierre.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Options Section (Initially Hidden) -->
                                <div id="paymentOptionsSection" class="mt-6 hidden">
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirmar Métodos de Pago</h3>

                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-colors payment-option">
                                                <input type="radio" name="paymentConfirmation" value="efectivo"
                                                    style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; opacity: 1; position: relative; z-index: 10; accent-color: #3b82f6;"
                                                    onchange="console.log('Opción seleccionada: Efectivo'); console.log('Valor:', this.value); updatePaymentSelection(this); showAmountFields('efectivo');">
                                                <div>
                                                    <p class="font-medium text-gray-900">Efectivo</p>
                                                    <p class="text-sm text-gray-500">Todo en efectivo</p>
                                                </div>
                                            </label>

                                            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-colors payment-option">
                                                <input type="radio" name="paymentConfirmation" value="yape"
                                                    style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; opacity: 1; position: relative; z-index: 10; accent-color: #3b82f6;"
                                                    onchange="console.log('Opción seleccionada: Yape'); console.log('Valor:', this.value); updatePaymentSelection(this); showAmountFields('yape');">
                                                <div>
                                                    <p class="font-medium text-gray-900">Yape</p>
                                                    <p class="text-sm text-gray-500">Todo por Yape</p>
                                                </div>
                                            </label>

                                            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-colors payment-option">
                                                <input type="radio" name="paymentConfirmation" value="mixto"
                                                    style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; opacity: 1; position: relative; z-index: 10; accent-color: #3b82f6;"
                                                    onchange="console.log('Opción seleccionada: Mixto'); console.log('Valor:', this.value); updatePaymentSelection(this); showAmountFields('mixto');">
                                                <div>
                                                    <p class="font-medium text-gray-900">Mixto</p>
                                                    <p class="text-sm text-gray-500">Combinación</p>
                                                </div>
                                            </label>
                                        </div>

                                        <!-- Amount Fields (Initially Hidden) -->
                                        <div id="amountFields" class="hidden">
                                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                                <h4 class="text-md font-semibold text-gray-900 mb-4">Ingresar Montos</h4>

                                                <!-- Efectivo Fields -->
                                                <div id="efectivoField" class="hidden">
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Monto en Efectivo</label>
                                                        <input type="number"
                                                            id="efectivoAmount"
                                                            step="0.01"
                                                            min="0"
                                                            placeholder="0.00"
                                                            oninput="validateEfectivoAmount()"
                                                            class="w-full p-3 border border-gray-300 rounded-lg focus:border-green-500 focus:ring-green-500">
                                                    </div>

                                                    <!-- Validación de monto efectivo -->
                                                    <div id="efectivoValidation" class="hidden mt-3">
                                                        <div id="efectivoValidationError" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg">
                                                            <div class="flex items-center gap-2">
                                                                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                                                                <p class="text-sm text-red-700">
                                                                    <span class="font-medium">Error:</span>
                                                                    <span id="efectivoValidationMessage"></span>
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div id="efectivoValidationSuccess" class="hidden p-3 bg-green-50 border border-green-200 rounded-lg">
                                                            <div class="flex items-center gap-2">
                                                                <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                                                                <p class="text-sm text-green-700">
                                                                    <span class="font-medium">¡Perfecto!</span>
                                                                    El monto es correcto
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Sugerencia de monto Efectivo -->
                                                    <div id="efectivoSuggestion" class="hidden mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                                        <div class="flex items-center gap-2">
                                                            <i data-lucide="info" class="w-4 h-4 text-green-600"></i>
                                                            <p class="text-sm text-green-700">
                                                                <span class="font-medium">Sugerencia:</span>
                                                                Debes abonar <span id="efectivoSuggestedAmount" class="font-bold text-green-800">S/. 0.00</span>
                                                                en efectivo
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Yape Amount -->
                                                <div id="yapeField" class="hidden mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto por Yape</label>
                                                    <input type="number"
                                                        id="yapeAmount"
                                                        step="0.01"
                                                        min="0"
                                                        placeholder="0.00"
                                                        oninput="validateYapeAmount()"
                                                        class="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500">

                                                    <!-- Validación de monto yape -->
                                                    <div id="yapeValidation" class="hidden mt-3">
                                                        <div id="yapeValidationError" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg">
                                                            <div class="flex items-center gap-2">
                                                                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                                                                <p class="text-sm text-red-700">
                                                                    <span class="font-medium">Error:</span>
                                                                    <span id="yapeValidationMessage"></span>
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div id="yapeValidationSuccess" class="hidden p-3 bg-green-50 border border-green-200 rounded-lg">
                                                            <div class="flex items-center gap-2">
                                                                <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                                                                <p class="text-sm text-green-700">
                                                                    <span class="font-medium">¡Perfecto!</span>
                                                                    El monto es correcto
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Yape Payment Proof -->
                                                    <div class="mt-4">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Comprobante de Pago Yape</label>
                                                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition-colors cursor-pointer"
                                                            ondrop="handleDrop(event, 'yapeDashboard')"
                                                            ondragover="handleDragOver(event)"
                                                            ondragleave="handleDragLeave(event)"
                                                            onclick="document.getElementById('yapeDashboardFileInput').click()">

                                                            <div id="yapeDashboardPreview" class="hidden mb-4">
                                                                <img src="" alt="Vista previa del comprobante" class="mx-auto max-h-48 rounded-lg shadow-md">
                                                                <p class="text-sm text-gray-600 mt-2">Comprobante cargado: <span id="yapeFileName"></span></p>
                                                            </div>

                                                            <div id="yapeDashboardPlaceholder" class="text-center">
                                                                <i data-lucide="upload-cloud" class="w-12 h-12 text-gray-400 mx-auto mb-2"></i>
                                                                <p class="text-sm text-gray-600">Arrastra y suelta el comprobante aquí</p>
                                                                <p class="text-xs text-gray-500 mt-1">o haz clic para seleccionar</p>
                                                            </div>

                                                            <input type="file"
                                                                id="yapeDashboardFileInput"
                                                                accept="image/*"
                                                                class="hidden"
                                                                onchange="handleFileSelect(event, 'yapeDashboard')">
                                                        </div>
                                                    </div>

                                                    <!-- Sugerencia de monto Yape -->
                                                    <div id="yapeSuggestion" class="hidden mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                        <div class="flex items-center gap-2">
                                                            <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                                                            <p class="text-sm text-blue-700">
                                                                <span class="font-medium">Sugerencia:</span>
                                                                Debes abonar <span id="yapeSuggestedAmount" class="font-bold text-blue-800">S/. 0.00</span>
                                                                por Yape
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Mixto Fields -->
                                                <div id="mixtoField" class="hidden">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto en Efectivo</label>
                                                            <input type="number"
                                                                id="mixtoEfectivoAmount"
                                                                step="0.01"
                                                                min="0"
                                                                placeholder="0.00"
                                                                oninput="validateMixtoAmounts()"
                                                                class="w-full p-3 border border-gray-300 rounded-lg focus:border-green-500 focus:ring-green-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto por Yape</label>
                                                            <input type="number"
                                                                id="mixtoYapeAmount"
                                                                step="0.01"
                                                                min="0"
                                                                placeholder="0.00"
                                                                oninput="validateMixtoAmounts()"
                                                                class="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-blue-500">
                                                        </div>
                                                    </div>

                                                    <!-- Campo para subir comprobante de Yape en mixto -->
                                                    <div class="mt-4">
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Comprobante de Pago Yape</label>
                                                        <div id="mixtoDropZone"
                                                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors cursor-pointer"
                                                            ondrop="handleDrop(event, 'mixtoPreview')"
                                                            ondragover="handleDragOver(event)"
                                                            ondragleave="handleDragLeave(event)"
                                                            onclick="document.getElementById('mixtoFileInput').click()">

                                                            <input type="file"
                                                                id="mixtoFileInput"
                                                                accept="image/*"
                                                                class="hidden"
                                                                onchange="handleFileSelect(event, 'mixtoPreview')">

                                                            <div id="mixtoUploadContent">
                                                                <i data-lucide="upload-cloud" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                                                                <p class="text-gray-600 mb-2">Arrastra la imagen del comprobante aquí</p>
                                                                <p class="text-sm text-gray-500">o haz clic para seleccionar</p>
                                                                <p class="text-xs text-gray-400 mt-2">Formatos: JPG, PNG, GIF (Máx. 5MB)</p>
                                                            </div>

                                                            <div id="mixtoPreview" class="hidden">
                                                                <img id="mixtoPreviewImage" src="" alt="Vista previa del comprobante" class="max-w-full max-h-64 mx-auto rounded-lg shadow-md">
                                                                <div class="mt-3 flex justify-center gap-2">
                                                                    <button type="button"
                                                                        onclick="removeImage('mixtoPreview', 'mixtoFileInput', 'mixtoUploadContent')"
                                                                        class="px-3 py-1 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition-colors">
                                                                        <i data-lucide="trash-2" class="w-4 h-4 inline mr-1"></i>
                                                                        Eliminar
                                                                    </button>
                                                                    <button type="button"
                                                                        onclick="document.getElementById('mixtoFileInput').click()"
                                                                        class="px-3 py-1 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition-colors">
                                                                        <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-1"></i>
                                                                        Cambiar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Validación de montos mixtos -->
                                                    <div id="mixtoValidation" class="hidden mt-3">
                                                        <div id="mixtoValidationError" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg">
                                                            <div class="flex items-center gap-2">
                                                                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                                                                <p class="text-sm text-red-700">
                                                                    <span class="font-medium">Error:</span>
                                                                    <span id="mixtoValidationMessage"></span>
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div id="mixtoValidationSuccess" class="hidden p-3 bg-green-50 border border-green-200 rounded-lg">
                                                            <div class="flex items-center gap-2">
                                                                <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                                                                <p class="text-sm text-green-700">
                                                                    <span class="font-medium">¡Perfecto!</span>
                                                                    Los montos coinciden con los sugeridos
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Sugerencia de monto Mixto -->
                                                    <div id="mixtoSuggestion" class="hidden mt-3 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                                        <div class="flex items-center gap-2">
                                                            <i data-lucide="info" class="w-4 h-4 text-purple-600"></i>
                                                            <p class="text-sm text-purple-700">
                                                                <span class="font-medium">Sugerencia:</span>
                                                                Debes abonar <span id="mixtoSuggestedAmount" class="font-bold text-purple-800">S/. 0.00</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex justify-end gap-3">
                                            <button
                                                onclick="cancelCashClosing()"
                                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                                Cancelar
                                            </button>
                                            <button
                                                onclick="confirmCashClosing()"
                                                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                                                <i data-lucide="check-circle" class="w-5 h-5"></i>
                                                Confirmar Cierre
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white shadow-lg border-0 rounded-lg">
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-b p-6">
                                <h2 class="flex items-center gap-2 text-xl font-semibold">
                                    <i data-lucide="calendar" class="w-5 h-5 text-green-600"></i>
                                    Pagos de Hoy
                                </h2>
                            </div>
                            <div class="p-6">
                                <div id="todayPaymentsList">
                                    <!-- Content will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>

                        <!-- Cash Closing -->

                    </div>
                </div>
            </div>

      
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .payment-option input[type="radio"] {
        width: 20px !important;
        height: 20px !important;
        min-width: 20px !important;
        min-height: 20px !important;
        opacity: 1 !important;
        position: relative !important;
        z-index: 10 !important;
    }

    .payment-option input[type="radio"]:checked {
        accent-color: #3b82f6 !important;
    }

    .payment-option.selected {
        border-color: #3b82f6 !important;
        background-color: #eff6ff !important;
    }

    .payment-option:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
</style>
<script>
    // Funciones globales para que sean accesibles desde onclick
    function togglePaymentOptions() {
        const paymentOptionsSection = document.getElementById('paymentOptionsSection');
        paymentOptionsSection.classList.toggle('hidden');

        // Reset radio buttons when showing
        if (!paymentOptionsSection.classList.contains('hidden')) {
            const radioButtons = document.querySelectorAll('input[name="paymentConfirmation"]');
            radioButtons.forEach(radio => radio.checked = false);

            // Reset visual styles
            const paymentOptions = document.querySelectorAll('.payment-option');
            paymentOptions.forEach(option => {
                option.classList.remove('border-blue-500', 'bg-blue-50');
                option.classList.add('border-gray-200');
            });
        }

        // Re-create icons after DOM change
        lucide.createIcons();
    }

    function showAmountFields(option) {
        console.log('Mostrando campos para:', option);

        // Ocultar todos los campos primero
        const amountFields = document.getElementById('amountFields');
        const efectivoField = document.getElementById('efectivoField');
        const yapeField = document.getElementById('yapeField');
        const mixtoField = document.getElementById('mixtoField');

        // Ocultar todos
        efectivoField.classList.add('hidden');
        yapeField.classList.add('hidden');
        mixtoField.classList.add('hidden');

        // Mostrar el campo correspondiente
        switch (option) {
            case 'efectivo':
                efectivoField.classList.remove('hidden');
                amountFields.classList.remove('hidden');
                showAmountSuggestion('efectivo');
                break;
            case 'yape':
                yapeField.classList.remove('hidden');
                amountFields.classList.remove('hidden');
                showAmountSuggestion('yape');
                break;
            case 'mixto':
                mixtoField.classList.remove('hidden');
                amountFields.classList.remove('hidden');
                showAmountSuggestion('mixto');
                break;
        }

        console.log('Campos mostrados para:', option);
    }

    function validateEfectivoAmount() {
        // Obtener los valores actuales
        const efectivoInput = document.getElementById('efectivoAmount');
        const validationContainer = document.getElementById('efectivoValidation');
        const errorMessage = document.getElementById('efectivoValidationError');
        const successMessage = document.getElementById('efectivoValidationSuccess');
        const validationMessage = document.getElementById('efectivoValidationMessage');

        // Obtener el monto sugerido (del dashboard)
        const cashPaymentsElement = document.getElementById('cashPayments');

        if (!cashPaymentsElement) {
            console.log('No se encontraron los totales para validación de efectivo');
            return;
        }

        const suggestedAmount = parseFloat(cashPaymentsElement.textContent.replace('S/. ', '').replace(',', ''));
        const currentAmount = parseFloat(efectivoInput.value) || 0;

        console.log('Validando monto efectivo:', {
            currentAmount,
            suggestedAmount
        });

        // Mostrar contenedor de validación
        validationContainer.classList.remove('hidden');

        // Ocultar mensajes anteriores
        errorMessage.classList.add('hidden');
        successMessage.classList.add('hidden');

        // Validar si el monto coincide con el sugerido
        const amountMatches = Math.abs(currentAmount - suggestedAmount) < 0.01; // Tolerancia de 0.01

        if (amountMatches) {
            // Todo perfecto - el monto es correcto
            successMessage.classList.remove('hidden');
            errorMessage.classList.add('hidden');

            // Quitar bordes rojos si existen
            efectivoInput.classList.remove('border-red-500');

        } else {
            // Hay errores - el monto no coincide
            successMessage.classList.add('hidden');
            errorMessage.classList.remove('hidden');

            // Construir mensaje de error
            const errorText = `El monto es incorrecto. Deberías ingresar S/. ${suggestedAmount.toFixed(2)} (actualmente tienes S/. ${currentAmount.toFixed(2)}).`;

            validationMessage.textContent = errorText;

            // Marcar campo como incorrecto
            efectivoInput.classList.add('border-red-500');
        }

        // Re-crear iconos
        lucide.createIcons();
    }

    function validateYapeAmount() {
        // Obtener los valores actuales
        const yapeInput = document.getElementById('yapeAmount');
        const validationContainer = document.getElementById('yapeValidation');
        const errorMessage = document.getElementById('yapeValidationError');
        const successMessage = document.getElementById('yapeValidationSuccess');
        const validationMessage = document.getElementById('yapeValidationMessage');

        // Obtener el monto sugerido (del dashboard)
        const yapePaymentsElement = document.getElementById('yapePayments');

        if (!yapePaymentsElement) {
            console.log('No se encontraron los totales para validación de yape');
            return;
        }

        const suggestedAmount = parseFloat(yapePaymentsElement.textContent.replace('S/. ', '').replace(',', ''));
        const currentAmount = parseFloat(yapeInput.value) || 0;

        console.log('Validando monto yape:', {
            currentAmount,
            suggestedAmount
        });

        // Mostrar contenedor de validación
        validationContainer.classList.remove('hidden');

        // Ocultar mensajes anteriores
        errorMessage.classList.add('hidden');
        successMessage.classList.add('hidden');

        // Validar si el monto coincide con el sugerido
        const amountMatches = Math.abs(currentAmount - suggestedAmount) < 0.01; // Tolerancia de 0.01

        if (amountMatches) {
            // Todo perfecto - el monto es correcto
            successMessage.classList.remove('hidden');
            errorMessage.classList.add('hidden');

            // Quitar bordes rojos si existen
            yapeInput.classList.remove('border-red-500');

        } else {
            // Hay errores - el monto no coincide
            successMessage.classList.add('hidden');
            errorMessage.classList.remove('hidden');

            // Construir mensaje de error
            const errorText = `El monto es incorrecto. Deberías ingresar S/. ${suggestedAmount.toFixed(2)} (actualmente tienes S/. ${currentAmount.toFixed(2)}).`;

            validationMessage.textContent = errorText;

            // Marcar campo como incorrecto
            yapeInput.classList.add('border-red-500');
        }

        // Re-crear iconos
        lucide.createIcons();
    }

    function validateMixtoAmounts() {
        // Obtener los valores actuales
        const efectivoInput = document.getElementById('mixtoEfectivoAmount');
        const yapeInput = document.getElementById('mixtoYapeAmount');
        const validationContainer = document.getElementById('mixtoValidation');
        const errorMessage = document.getElementById('mixtoValidationError');
        const successMessage = document.getElementById('mixtoValidationSuccess');
        const validationMessage = document.getElementById('mixtoValidationMessage');

        // Obtener los montos sugeridos (del dashboard)
        const cashPaymentsElement = document.getElementById('cashPayments');
        const yapePaymentsElement = document.getElementById('yapePayments');

        if (!cashPaymentsElement || !yapePaymentsElement) {
            console.log('No se encontraron los totales para validación');
            return;
        }

        const cashPayments = parseFloat(cashPaymentsElement.textContent.replace('S/. ', '').replace(',', ''));
        const yapePayments = parseFloat(yapePaymentsElement.textContent.replace('S/. ', '').replace(',', ''));

        // El total sugerido es la suma de los pagos reales del día
        const suggestedTotal = cashPayments + yapePayments;

        const currentEfectivo = parseFloat(efectivoInput.value) || 0;
        const currentYape = parseFloat(yapeInput.value) || 0;
        const currentTotal = currentEfectivo + currentYape;

        console.log('Validando montos mixtos:', {
            currentEfectivo,
            currentYape,
            currentTotal,
            suggestedTotal
        });

        // Mostrar contenedor de validación
        validationContainer.classList.remove('hidden');

        // Ocultar mensajes anteriores
        errorMessage.classList.add('hidden');
        successMessage.classList.add('hidden');

        // Validar si la suma coincide con el total sugerido
        const totalMatches = Math.abs(currentTotal - cashPayments) < 0.01; // Tolerancia de 0.01

        if (totalMatches) {
            // Todo perfecto - la suma es correcta
            successMessage.classList.remove('hidden');
            errorMessage.classList.add('hidden');

            // Quitar bordes rojos si existen
            efectivoInput.classList.remove('border-red-500');
            yapeInput.classList.remove('border-red-500');

        } else {
            // Hay errores - la suma no coincide
            successMessage.classList.add('hidden');
            errorMessage.classList.remove('hidden');

            // Construir mensaje de error
            const errorText = `La suma de los montos es incorrecta. Deberías ingresar un total de S/. ${cashPayments.toFixed(2)} (actualmente tienes S/. ${currentTotal.toFixed(2)}).`;

            validationMessage.textContent = errorText;

            // Marcar ambos campos como incorrecto
            efectivoInput.classList.add('border-red-500');
            yapeInput.classList.add('border-red-500');
        }

        // Re-crear iconos
        lucide.createIcons();
    }

    function showAmountSuggestion(option) {
        // Obtener los totales del dashboard
        const totalIncomeElement = document.getElementById('totalIncome');
        const cashPaymentsElement = document.getElementById('cashPayments');
        const yapePaymentsElement = document.getElementById('yapePayments');

        if (!totalIncomeElement || !cashPaymentsElement || !yapePaymentsElement) {
            console.log('No se encontraron los totales del día');
            return;
        }

        // Extraer valores numéricos
        const totalIncomeText = totalIncomeElement.textContent;
        const cashPaymentsText = cashPaymentsElement.textContent;
        const yapePaymentsText = yapePaymentsElement.textContent;

        const totalIncome = parseFloat(totalIncomeText.replace('S/. ', '').replace(',', ''));
        const cashPayments = parseFloat(cashPaymentsText.replace('S/. ', '').replace(',', ''));
        const yapePayments = parseFloat(yapePaymentsText.replace('S/. ', '').replace(',', ''));

        console.log('=== DEPURACIÓN DE SUGERENCIAS ===');
        console.log('Texto del DOM totalIncome:', totalIncomeText);
        console.log('Texto del DOM cashPayments:', cashPaymentsText);
        console.log('Texto del DOM yapePayments:', yapePaymentsText);
        console.log('Valores numéricos:', {
            totalIncome,
            cashPayments,
            yapePayments
        });

        // Ocultar todas las sugerencias primero
        document.getElementById('efectivoSuggestion').classList.add('hidden');
        document.getElementById('yapeSuggestion').classList.add('hidden');
        document.getElementById('mixtoSuggestion').classList.add('hidden');

        // Mostrar sugerencia según la opción
        switch (option) {
            case 'efectivo':
                const efectivoSuggestion = document.getElementById('efectivoSuggestion');
                const efectivoSuggestedAmount = document.getElementById('efectivoSuggestedAmount');

                efectivoSuggestion.classList.remove('hidden');
                efectivoSuggestedAmount.textContent = `S/. ${cashPayments.toFixed(2)}`;

                // Autocompletar el input con el monto de efectivo
                document.getElementById('efectivoAmount').value = cashPayments.toFixed(2);
                console.log('Efectivo sugerido:', cashPayments.toFixed(2));
                break;

            case 'yape':
                const yapeSuggestion = document.getElementById('yapeSuggestion');
                const yapeSuggestedAmount = document.getElementById('yapeSuggestedAmount');

                yapeSuggestion.classList.remove('hidden');
                yapeSuggestedAmount.textContent = `S/. ${cashPayments.toFixed(2)}`;

                // Autocompletar el input con el monto de yape
                document.getElementById('yapeAmount').value = cashPayments.toFixed(2);
                console.log('Yape sugerido:', cashPayments.toFixed(2));
                break;

            case 'mixto':
                const mixtoSuggestion = document.getElementById('mixtoSuggestion');
                const mixtoSuggestedAmount = document.getElementById('mixtoSuggestedAmount');
                const mixtoEfectivoSuggested = document.getElementById('mixtoEfectivoSuggested');
                const mixtoYapeSuggested = document.getElementById('mixtoYapeSuggested');

                mixtoSuggestion.classList.remove('hidden');

                // Mostrar el total sugerido pero dejar campos en blanco para cualquier combinación
                const suggestedTotal = cashPayments + yapePayments;
                mixtoSuggestedAmount.textContent = `S/. ${cashPayments.toFixed(2)}`;
                mixtoEfectivoSuggested.textContent = `S/. 0.00`;
                mixtoYapeSuggested.textContent = `S/. 0.00`;

                // Limpiar los inputs para que el usuario ingrese cualquier combinación
                document.getElementById('mixtoEfectivoAmount').value = '';
                document.getElementById('mixtoYapeAmount').value = '';

                console.log('Mixto - Total sugerido:', cashPayments.toFixed(2));
                console.log('Mixto - Campos limpios para cualquier combinación');
                break;
        }

        // Re-crear iconos
        lucide.createIcons();
    }

    // Funciones para manejo de archivos Yape en Dashboard
    function handleDrop(event, previewId) {
        event.preventDefault();
        event.stopPropagation();

        const files = event.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0], previewId);
        }
    }

    function handleDragOver(event) {
        event.preventDefault();
        event.currentTarget.classList.add('border-blue-400', 'bg-blue-50');
    }

    function handleDragLeave(event) {
        event.preventDefault();
        event.currentTarget.classList.remove('border-blue-400', 'bg-blue-50');
    }

    function handleFileSelect(event, previewId) {
        const files = event.target.files;
        if (files.length > 0) {
            handleFile(files[0], previewId);
        }
    }

    function handleFile(file, previewId) {
        // Validar que sea una imagen
        if (!file.type.startsWith('image/')) {
            alert('Por favor selecciona un archivo de imagen válido');
            return;
        }

        // Validar tamaño (máximo 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('El archivo es demasiado grande. Máximo permitido: 5MB');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewContainer = document.getElementById(previewId + 'Preview');
            const placeholderContainer = document.getElementById(previewId + 'Placeholder');

            // Mostrar vista previa
            previewContainer.innerHTML = `
            <img src="${e.target.result}" alt="Vista previa del comprobante" class="mx-auto max-h-48 rounded-lg shadow-md">
            <p class="text-sm text-gray-600 mt-2">Comprobante cargado: ${file.name}</p>
        `;
            previewContainer.classList.remove('hidden');

            // Ocultar placeholder
            placeholderContainer.classList.add('hidden');

            // Guardar referencia al archivo para uso posterior
            window[previewId + 'File'] = file;

            console.log('Archivo Yape cargado:', file.name);
        };

        reader.readAsDataURL(file);
    }

    function updatePaymentSelection(radio) {
        console.log('updatePaymentSelection llamado con:', radio.value);

        // Reset all payment options
        const paymentOptions = document.querySelectorAll('.payment-option');
        paymentOptions.forEach(option => {
            option.classList.remove('selected', 'border-blue-500', 'bg-blue-50');
            option.classList.add('border-gray-200');
        });

        // Highlight selected option
        if (radio.checked) {
            const selectedLabel = radio.closest('.payment-option');
            console.log('Label encontrado:', selectedLabel);

            if (selectedLabel) {
                selectedLabel.classList.remove('border-gray-200');
                selectedLabel.classList.add('selected');

                console.log('Clases después de agregar selected:', selectedLabel.className);
            }

            // Forzar repintado
            setTimeout(() => {
                radio.style.display = 'none';
                setTimeout(() => {
                    radio.style.display = 'block';
                }, 10);
            }, 10);
        }
    }

    function cancelCashClosing() {
        const paymentOptionsSection = document.getElementById('paymentOptionsSection');
        paymentOptionsSection.classList.add('hidden');

        // Reset radio buttons
        const radioButtons = document.querySelectorAll('input[name="paymentConfirmation"]');
        radioButtons.forEach(radio => radio.checked = false);
    }

    async function confirmCashClosing() {
        const selectedOption = document.querySelector('input[name="paymentConfirmation"]:checked');

        if (!selectedOption) {
            alert('Por favor selecciona un método de pago');
            return;
        }

        const paymentMethod = selectedOption.value;
        let requestData = {
            payment_method: paymentMethod
        };

        console.log('Iniciando cierre de caja con método:', paymentMethod);

        // Obtener datos según el método de pago
        switch (paymentMethod) {
            case 'efectivo':
                const efectivoAmount = document.getElementById('efectivoAmount').value;
                console.log('Monto efectivo ingresado:', efectivoAmount);

                if (!efectivoAmount || parseFloat(efectivoAmount) <= 0) {
                    alert('Por favor ingresa un monto válido para efectivo');
                    return;
                }
                requestData.efectivo_amount = parseFloat(efectivoAmount);
                console.log('Datos a enviar (efectivo):', requestData);
                break;

            case 'yape':
                const yapeAmount = document.getElementById('yapeAmount').value;
                console.log('Monto yape ingresado:', yapeAmount);

                if (!yapeAmount || parseFloat(yapeAmount) <= 0) {
                    alert('Por favor ingresa un monto válido para Yape');
                    return;
                }
                requestData.yape_amount = parseFloat(yapeAmount);
                console.log('Datos a enviar (yape):', requestData);

                // Verificar si se subió el comprobante
                const yapeFileInput = document.getElementById('yapeDashboardFileInput');
                if (yapeFileInput && yapeFileInput.files.length > 0) {
                    console.log('Archivo yape encontrado:', yapeFileInput.files[0].name);
                    // Para archivos, necesitamos usar FormData
                    const formData = new FormData();
                    formData.append('payment_method', paymentMethod);
                    formData.append('yape_amount', parseFloat(yapeAmount));
                    formData.append('payment_proof', yapeFileInput.files[0]);

                    console.log('FormData (yape):');
                    for (let [key, value] of formData.entries()) {
                        console.log(key, value);
                    }

                    // Obtener CSRF token de forma segura
                    const getCsrfToken = () => {
                        const metaTag = document.querySelector('meta[name="csrf-token"]');
                        if (metaTag) {
                            return metaTag.getAttribute('content');
                        }

                        // Fallback: buscar en el DOM
                        const tokenInput = document.querySelector('input[name="_token"]');
                        if (tokenInput) {
                            return tokenInput.value;
                        }

                        console.error('No se encontró el CSRF token');
                        return null;
                    };

                    const csrfToken = getCsrfToken();
                    if (!csrfToken) {
                        alert('Error: No se encontró el token de seguridad. Por favor recarga la página.');
                        return;
                    }

                    try {
                        const response = await fetch('{{ route('asesor.cash-closing') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: formData
                            });

                        console.log('Response status (yape):', response.status);
                        const result = await response.json();
                        console.log('Response result (yape):', result);

                        if (result.success) {
                            alert('Cierre de caja realizado correctamente');
                            window.location.reload();
                        } else {
                            console.error('Backend error (yape):', result);
                            alert('Error al realizar el cierre de caja: ' + (result.message || 'Error desconocido'));
                        }
                    } catch (error) {
                        console.error('Error performing cash closing (yape):', error);
                        alert('Error al realizar el cierre de caja: ' + error.message);
                    }
                    return;
                } else {
                    console.log('No se encontró archivo yape');
                    alert('Por favor sube el comprobante de pago Yape');
                    return;
                }
                break;

            case 'mixto':
                const mixtoEfectivoAmount = document.getElementById('mixtoEfectivoAmount').value;
                const mixtoYapeAmount = document.getElementById('mixtoYapeAmount').value;

                if (!mixtoEfectivoAmount || parseFloat(mixtoEfectivoAmount) <= 0 ||
                    !mixtoYapeAmount || parseFloat(mixtoYapeAmount) <= 0) {
                    alert('Por favor ingresa montos válidos para efectivo y Yape');
                    return;
                }

                requestData.efectivo_amount = parseFloat(mixtoEfectivoAmount);
                requestData.yape_amount = parseFloat(mixtoYapeAmount);

                // Verificar si se subió el comprobante para mixto
                const mixtoFileInput = document.getElementById('mixtoFileInput');
                if (mixtoFileInput && mixtoFileInput.files.length > 0) {
                    // Usar FormData para el archivo
                    const formData = new FormData();
                    formData.append('payment_method', paymentMethod);
                    formData.append('efectivo_amount', parseFloat(mixtoEfectivoAmount));
                    formData.append('yape_amount', parseFloat(mixtoYapeAmount));
                    formData.append('payment_proof', mixtoFileInput.files[0]);

                    // Obtener CSRF token de forma segura
                    const getCsrfToken = () => {
                        const metaTag = document.querySelector('meta[name="csrf-token"]');
                        if (metaTag) {
                            return metaTag.getAttribute('content');
                        }

                        // Fallback: buscar en el DOM
                        const tokenInput = document.querySelector('input[name="_token"]');
                        if (tokenInput) {
                            return tokenInput.value;
                        }

                        console.error('No se encontró el CSRF token');
                        return null;
                    };

                    const csrfToken = getCsrfToken();
                    if (!csrfToken) {
                        alert('Error: No se encontró el token de seguridad. Por favor recarga la página.');
                        return;
                    }

                    try {
                        const response = await fetch('{{ route('asesor.cash-closing') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: formData
                            });

                        const result = await response.json();

                        if (result.success) {
                            alert('Cierre de caja realizado correctamente');
                            window.location.reload();
                        } else {
                            alert('Error al realizar el cierre de caja: ' + (result.message || 'Error desconocido'));
                        }
                    } catch (error) {
                        console.error('Error performing cash closing:', error);
                        alert('Error al realizar el cierre de caja');
                    }
                    return;
                } else {
                    alert('Por favor sube el comprobante de pago Yape');
                    return;
                }
                break;
        }

        // Obtener CSRF token de forma segura
        const getCsrfToken = () => {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                return metaTag.getAttribute('content');
            }

            // Fallback: buscar en el DOM
            const tokenInput = document.querySelector('input[name="_token"]');
            if (tokenInput) {
                return tokenInput.value;
            }

            console.error('No se encontró el CSRF token');
            return null;
        };

        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            alert('Error: No se encontró el token de seguridad. Por favor recarga la página.');
            return;
        }

        console.log('CSRF Token encontrado:', csrfToken.substring(0, 20) + '...');

        try {
            const response = await fetch('{{ route('asesor.cash-closing') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(requestData)
                });

            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

            // Si el status es 500, mostrar la respuesta como texto para depuración
            if (response.status === 500) {
                const errorText = await response.text();
                console.error('Error 500 response (HTML):', errorText.substring(0, 500) + '...');
                alert('Error del servidor (500): ' + errorText.substring(0, 200) + '...');
                return;
            }

            const result = await response.json();
            console.log('Response result:', result);

            if (result.success) {
                alert('Cierre de caja realizado correctamente');
                // Recargar la página para mostrar el estado actualizado
                window.location.reload();
            } else {
                console.error('Backend error:', result);
                alert('Error al realizar el cierre de caja: ' + (result.message || 'Error desconocido'));
            }
        } catch (error) {
            console.error('Error performing cash closing:', error);
            console.error('Error details:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
            alert('Error al realizar el cierre de caja: ' + error.message);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        // Función para formatear números (similar a number_format de PHP)
        function numberFormat(number, decimals = 2) {
            return new Intl.NumberFormat('es-PE', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        }

        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchInput');
        const loadingState = document.getElementById('loadingState');
        const errorState = document.getElementById('errorState');
        const errorMessage = document.getElementById('errorMessage');
        const clientDetails = document.getElementById('clientDetails');
        const searchButtonText = document.getElementById('searchButtonText');
        const todayPaymentsList = document.getElementById('todayPaymentsList');
        const recentClientsList = document.getElementById('recentClientsList');

        // Load today's payments and recent clients on page load
        loadTodayPayments();
        loadRecentClients();

        searchForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            try {
                console.log('Iniciando búsqueda...');
                const searchTerm = searchInput.value.trim();
                console.log('Término de búsqueda:', searchTerm);

                if (!searchTerm) {
                    showError('Por favor ingrese un término de búsqueda');
                    return;
                }

                showLoading();
                hideError();

                console.log('Enviando petición a:', '{{ route('asesor.client.search') }}?q=' + encodeURIComponent(searchTerm));

                const response = await fetch('{{ route('asesor.client.search') }}?q=' + encodeURIComponent(searchTerm));
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);

                const result = await response.json();
                console.log('Response JSON:', result);

                if (!response.ok) {
                    console.log('Response no es ok, status:', response.status);
                    throw new Error('Error en la respuesta del servidor');
                }

                if (!result.success) {
                    console.log('Response success es false:', result);
                    throw new Error(result.message || 'Error en la búsqueda');
                }

                if (!result.data || result.data.length === 0) {
                    console.log('No se encontraron clientes');
                    showError('No se encontraron clientes que coincidan con la búsqueda');
                    return;
                }

                console.log('Cliente encontrado:', result.data[0]);
                const client = result.data[0];
                showClientDetails(client);
            } catch (error) {
                console.error('Error completo:', error);
                console.error('Error message:', error.message);
                console.error('Error stack:', error.stack);
                showError('Error al conectar con el servidor. Intente nuevamente.');
            } finally {
                hideLoading();
            }
        });

        function showLoading() {
            loadingState.classList.remove('hidden');
            searchButtonText.textContent = 'Buscando...';
            searchInput.disabled = true;
        }

        function hideLoading() {
            loadingState.classList.add('hidden');
            searchButtonText.textContent = 'Buscar Cliente';
            searchInput.disabled = false;
        }

        function showError(message) {
            errorMessage.textContent = message;
            errorState.classList.remove('hidden');
        }

        function hideError() {
            errorState.classList.add('hidden');
        }

        function hideClientDetails() {
            clientDetails.classList.add('hidden');
        }

        function showClientDetails(client) {
            clientDetails.innerHTML = `
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 border-0 shadow-md rounded-lg p-6">
                <h3 class="flex items-center gap-2 text-lg font-semibold mb-4">
                    <i data-lucide="users" class="w-5 h-5 text-blue-600"></i>
                    Información del Cliente
                </h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nombre Completo</p>
                            <p class="font-semibold text-gray-900">${client.full_name}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <i data-lucide="file-text" class="w-4 h-4 text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">DNI</p>
                            <p class="font-mono font-semibold text-gray-900">${client.dni}</p>
                        </div>
                    </div>
                </div>
                
                ${client.loans && client.loans.length > 0 ? `
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <i data-lucide="dollar-sign" class="w-5 h-5 text-green-600"></i>
                            Préstamos Activos
                        </h4>
                        <div class="space-y-4">
                            ${client.loans.map(loan => `
                                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 space-y-2">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full ${loan.loan_status?.description === 'Activo' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'}">
                                                    ${loan.codigo ? 'Préstamo ' + loan.codigo : 'Préstamo #' + loan.id}
                                                </span>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full border border-green-200 text-green-600">
                                                    ${loan.loan_status?.description || 'Desconocido'}
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="dollar-sign" class="w-4 h-4 text-gray-400"></i>
                                                    <span class="text-gray-500">Monto original:</span>
                                                    <span class="font-semibold">${numberFormat(loan.amount)}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="trending-up" class="w-4 h-4 text-gray-400"></i>
                                                    <span class="text-gray-500">Saldo pendiente:</span>
                                                    <span class="font-semibold text-orange-600">${numberFormat(loan.amount - (loan.paid_amount || 0))}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                                    <span class="text-gray-500">Plazo:</span>
                                                    <span class="font-semibold">${loan.term_months} meses</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="check-circle" class="w-4 h-4 text-gray-400"></i>
                                                    <span class="text-gray-500">Progreso:</span>
                                                    <span class="font-semibold">${loan.installments ? loan.installments.filter(i => i.status === 'paid').length + '/' + loan.installments.length : 'N/A'}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="{{ route('asesor.collection') }}?clientId=${client.id}" class="ml-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                            Cobrar
                                        </a>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                
                ${client.savings && client.savings.length > 0 ? `
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <i data-lucide="piggy-bank" class="w-5 h-5 text-purple-600"></i>
                            Planes de Ahorro
                        </h4>
                        <div class="space-y-4">
                            ${client.savings.map(saving => `
                                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 space-y-2">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                                                    ${saving.codigo ? 'Ahorro ' + saving.codigo : 'Ahorro #' + saving.id}
                                                </span>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full border border-purple-200 text-purple-600">
                                                    ${saving.status || 'Activo'}
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="dollar-sign" class="w-4 h-4 text-gray-400"></i>
                                                    <span class="text-gray-500">Monto total:</span>
                                                    <span class="font-semibold">${numberFormat(saving.amount)}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                                    <span class="text-gray-500">Aporte diario:</span>
                                                    <span class="font-semibold">${numberFormat(saving.daily_contribution)}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="ml-4 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                                            <i data-lucide="plus" class="w-4 h-4"></i>
                                            Depositar
                                        </button>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
                
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center pt-6 border-t bg-gray-50 -mx-6 px-6 -mb-6 pb-6 rounded-b-lg gap-3 mt-6">
                    <button class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Volver a Búsqueda
                    </button>
                    <div class="flex flex-col sm:flex-row gap-3">
                        ${client.loans && client.loans.length > 0 ? `
                            <a href="{{ route('asesor.collection') }}?clientId=${client.id}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                                <i data-lucide="dollar-sign" class="w-4 h-4"></i>
                                Registrar Pago
                            </a>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;

            clientDetails.classList.remove('hidden');
            lucide.createIcons();
        }

        async function loadTodayPayments() {
            try {
                const response = await fetch('{{ route('asesor.today-payments') }}');
                const result = await response.json();

                if (result.success && result.data) {
                    todayPaymentsList.innerHTML = result.data.map(payment => `
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">${payment.client_name}</p>
                            <p class="text-sm text-gray-500">${payment.client_dni}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-green-600">${numberFormat(payment.amount, 2)}</p>
                            <div class="flex items-center gap-2 justify-end mt-1">
                                <span class="text-xs px-2 py-1 rounded-full ${
                                    payment.payment_method === 'yape' 
                                        ? 'bg-blue-100 text-blue-700' 
                                        : 'bg-green-100 text-green-700'
                                }">
                                    ${payment.payment_method === 'yape' ? 'Yape' : 'Efectivo'}
                                </span>
                                <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700">
                                    Pendiente
                                </span>
                            </div>
                        </div>
                    </div>
                `).join('');

                    // Función para verificar si ya se cerró caja hoy
                    async function checkCashClosingStatus() {
                        console.log('🔍 Iniciando verificación de estado de cierre de caja...');

                        try {
                            console.log('📡 Haciendo petición a:', '{{ route('asesor.cash-closing-status') }}');

                            const response = await fetch('{{ route('asesor.cash-closing-status') }}');
                            console.log('📥 Response status:', response.status);

                            if (!response.ok) {
                                console.error('❌ Error en la respuesta:', response.status, response.statusText);
                                return;
                            }

                            const result = await response.json();
                            console.log('📊 Resultado del servidor:', result);

                            if (result.success && result.already_closed) {
                                console.log('✅ Caja ya cerrada hoy - Ocultando botón y mostrando mensaje');

                                // Ocultar el botón de cierre de caja
                                const buttonContainer = document.getElementById('cashClosingButtonContainer');
                                const messageContainer = document.getElementById('cashClosingAlreadyDoneContainer');

                                console.log('🎯 Elementos encontrados:', {
                                    buttonContainer: !!buttonContainer,
                                    messageContainer: !!messageContainer
                                });

                                if (buttonContainer) {
                                    buttonContainer.classList.add('hidden');
                                    console.log('🙈 Botón ocultado');
                                }

                                // Mostrar el mensaje de caja ya cerrada
                                if (messageContainer) {
                                    messageContainer.classList.remove('hidden');
                                    console.log('👁️ Mensaje de caja cerrada mostrado');
                                }

                                console.log('📋 Info del cierre:', result.closing_info);

                            } else {
                                console.log('🔓 Caja no cerrada aún - Manteniendo botón visible');

                                // Asegurarse de que el botón esté visible
                                const buttonContainer = document.getElementById('cashClosingButtonContainer');
                                const messageContainer = document.getElementById('cashClosingAlreadyDoneContainer');

                                if (buttonContainer) {
                                    buttonContainer.classList.remove('hidden');
                                    console.log('👁️ Botón visible');
                                }

                                if (messageContainer) {
                                    messageContainer.classList.add('hidden');
                                    console.log('🙈 Mensaje oculto');
                                }
                            }
                        } catch (error) {
                            console.error('💥 Error al verificar estado de cierre de caja:', error);
                            console.error('📋 Detalles del error:', error.message, error.stack);

                            // En caso de error, mostrar el botón por defecto
                            const buttonContainer = document.getElementById('cashClosingButtonContainer');
                            const messageContainer = document.getElementById('cashClosingAlreadyDoneContainer');

                            if (buttonContainer) {
                                buttonContainer.classList.remove('hidden');
                                console.log('🔄 Error - Botón visible por defecto');
                            }

                            if (messageContainer) {
                                messageContainer.classList.add('hidden');
                                console.log('🔄 Error - Mensaje oculto por defecto');
                            }
                        }

                        console.log('🏁 Verificación de estado de cierre de caja completada');
                    }

                    // Llamar a la función cuando se cargue la página
                    document.addEventListener('DOMContentLoaded', function() {
                        console.log('🚀 DOMContentLoaded disparado - Iniciando checkCashClosingStatus');
                        checkCashClosingStatus();
                    });

                    // También llamar a la función directamente como fallback
                    console.log('🎯 Script cargado - Verificando si el DOM ya está listo');
                    if (document.readyState === 'loading') {
                        // El DOM todavía está cargando
                        console.log('⏳ DOM todavía cargando...');
                    } else {
                        // El DOM ya está cargado
                        console.log('✅ DOM ya cargado - Ejecutando checkCashClosingStatus directamente');
                        checkCashClosingStatus();
                    }

                    // Calcular totales para el cierre de caja
                    updateCashClosingTotals(result.data);

                    if (result.data.length === 0) {
                        todayPaymentsList.innerHTML = '<p class="text-gray-500 text-center py-4">No hay pagos registrados hoy</p>';
                        updateCashClosingTotals([]);
                    }
                } else {
                    todayPaymentsList.innerHTML = '<p class="text-gray-500 text-center py-4">Error al cargar pagos</p>';
                }
            } catch (error) {
                console.error('Error loading today payments:', error);
                todayPaymentsList.innerHTML = '<p class="text-gray-500 text-center py-4">Error al cargar pagos</p>';
            }
        }

        function updateCashClosingTotals(payments) {
            let totalIncome = 0;
            let yapePayments = 0;
            let cashPayments = 0;

            console.log('Pagos del día recibidos:', payments);

            payments.forEach(payment => {
                totalIncome += parseFloat(payment.amount);
                if (payment.payment_method.toLowerCase() === 'yape') {
                    yapePayments += parseFloat(payment.amount);
                } else if (payment.payment_method.toLowerCase() === 'efectivo') {
                    cashPayments += parseFloat(payment.amount);
                }
            });

            console.log('Totales calculados:', {
                totalIncome,
                yapePayments,
                cashPayments,
                paymentCount: payments.length
            });

            document.getElementById('totalIncome').textContent = `S/. ${numberFormat(totalIncome, 2)}`;
            document.getElementById('yapePayments').textContent = `S/. ${numberFormat(yapePayments, 2)}`;
            document.getElementById('cashPayments').textContent = `S/. ${numberFormat(cashPayments, 2)}`;
        }

        async function performCashClosing() {
            try {
                const response = await fetch('{{ route('asesor.cash-closing') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                const result = await response.json();

                if (result.success) {
                    alert('Cierre de caja realizado correctamente');

                    // Actualizar el estado del botón de cierre de caja
                    document.getElementById('cashClosingButtonContainer').classList.add('hidden');
                    document.getElementById('cashClosingAlreadyDoneContainer').classList.remove('hidden');

                    // Ocultar el formulario de opciones de pago
                    document.getElementById('paymentOptionsSection').classList.add('hidden');

                    // Recargar la página para mostrar el estado actualizado
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    alert('Error al realizar el cierre de caja: ' + (result.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error performing cash closing:', error);
                alert('Error al realizar el cierre de caja');
            }
        }
        async function loadRecentClients() {
            try {
                const response = await fetch('{{ route('asesor.recent-clients') }}');
                const result = await response.json();

                if (result.success && result.data) {
                    recentClientsList.innerHTML = result.data.map(client => `
                    <div class="p-3 border rounded-lg hover:bg-blue-50 cursor-pointer transition-colors" onclick="selectRecentClient(${client.id}, '${client.dni}', '${client.full_name}')">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">${client.full_name}</div>
                                <div class="text-sm text-gray-500">DNI: ${client.dni}</div>
                                ${client.phone ? `
                                    <div class="text-sm text-gray-500 flex items-center gap-1">
                                        <i data-lucide="phone" class="w-3 h-3"></i>
                                        ${client.phone}
                                    </div>
                                ` : ''}
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs font-medium rounded-full border border-blue-200 text-blue-600">
                                    ${client.loans_count || 0} préstamos
                                </span>
                            </div>
                        </div>
                    </div>
                `).join('');

                    if (result.data.length === 0) {
                        recentClientsList.innerHTML = `
                        <div class="text-center py-6">
                            <i data-lucide="users" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                            <p class="text-gray-600 font-medium">No hay clientes asignados</p>
                            <p class="text-sm text-gray-500 mt-1">Contacte al administrador</p>
                        </div>
                    `;
                    }

                    // Reinitialize Lucide icons for the new content
                    lucide.createIcons();
                } else {
                    recentClientsList.innerHTML = '<p class="text-gray-500 text-center py-4">Error al cargar clientes</p>';
                }
            } catch (error) {
                console.error('Error loading recent clients:', error);
                recentClientsList.innerHTML = '<p class="text-gray-500 text-center py-4">Error al cargar clientes</p>';
            }
        }

        function selectRecentClient(clientId, clientDni, clientName) {
            searchInput.value = clientDni;
            // Trigger search
            searchForm.dispatchEvent(new Event('submit'));
        }
    });
</script>
@endpush
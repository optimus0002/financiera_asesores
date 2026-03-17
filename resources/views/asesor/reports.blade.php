@extends('layouts.app')

@section('title', 'Reportes del Día')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="bg-white shadow-lg rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">
                    <i data-lucide="file-text" class="w-8 h-8 mr-3"></i>
                    Reportes del Día
                </h1>
                <p class="text-gray-600 mt-2">Estadísticas y pagos del día de hoy</p>
            </div>
        </div>

        <!-- Estadísticas Generales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Ingresos -->
            <div class="bg-gradient-to-r from-green-400 to-green-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Total Ingresos</p>
                        <p class="text-3xl font-bold mt-2">S/. {{ number_format($totalAmount, 2) }}</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-20 rounded-full">
                        <i data-lucide="dollar-sign" class="w-8 h-8 text-white"></i>
                    </div>
                </div>
            </div>

            <!-- Total Transacciones -->
            <div class="bg-gradient-to-r from-blue-400 to-blue-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Transacciones</p>
                        <p class="text-3xl font-bold mt-2">{{ $totalTransactions }}</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-20 rounded-full">
                        <i data-lucide="credit-card" class="w-8 h-8 text-white"></i>
                    </div>
                </div>
            </div>

            <!-- Promedio -->
            <div class="bg-gradient-to-r from-purple-400 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Promedio por Pago</p>
                        <p class="text-3xl font-bold mt-2">S/. {{ number_format($averagePayment, 2) }}</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-20 rounded-full">
                        <i data-lucide="trending-up" class="w-8 h-8 text-white"></i>
                    </div>
                </div>
            </div>

            <!-- Métodos de Pago -->
            <div class="bg-gradient-to-r from-orange-400 to-orange-600 text-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Métodos de Pago</p>
                        <p class="text-3xl font-bold mt-2">{{ $paymentsByMethod->count() }}</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-20 rounded-full">
                        <i data-lucide="smartphone" class="w-8 h-8 text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagos por Método -->
        <div class="bg-white shadow-lg rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Pagos por Método</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($paymentsByMethod as $method => $data)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-900 capitalize">{{ $method }}</h3>
                                    <p class="text-sm text-gray-600">{{ $data['count'] }} transacciones</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-gray-900">S/. {{ number_format($data['total'], 2) }}</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($data['total'] / $totalAmount * 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 text-center text-gray-500 py-8">
                            <i data-lucide="inbox" class="w-12 h-12 mx-auto text-gray-400 mb-3"></i>
                            <p>No hay pagos registrados hoy</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Tabla de Pagos del Día -->
        <div class="bg-white shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Pagos del Día</h2>
                    <div class="text-sm text-gray-500">
                        {{ $todayPayments->count() }} pagos hoy
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DNI</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($todayPayments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($payment->payment_date ?? $payment->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $payment->loan->client->full_name ?? $payment->savings->client->full_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $payment->loan->client->dni ?? $payment->savings->client->dni }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    S/. {{ number_format($payment->paid_amount ?? $payment->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($payment->payment_method === 'yape')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Yape
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Efectivo
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if(isset($payment->loan))
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                            Préstamo
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                            Ahorros
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-gray-400 mb-2"></i>
                                    <p>No hay pagos registrados hoy</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Inicializar iconos de Lucide
    lucide.createIcons();
</script>
@endpush

@extends('layouts.app')

@section('title', 'Historial de Pagos')

@section('content')
<div class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow-lg rounded-lg">
            <!-- Filtros -->
            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Búsqueda</h2>

                <form id="filterForm" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                            <div class="relative">
                                <input type="date" id="startDate" name="start_date"
                                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2"
                                    max="{{ now()->format('Y-m-d') }}">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                            <div class="relative">
                                <input type="date" id="endDate" name="end_date"
                                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2"
                                    max="{{ now()->format('Y-m-d') }}">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i data-lucide="calendar" class="w-5 h-5 text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Límite</label>
                            <div class="relative">
                                <select id="limit" name="limit"
                                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:ring-2">
                                    <option value="10">10 resultados</option>
                                    <option value="25">25 resultados</option>
                                    <option value="50">50 resultados</option>
                                    <option value="100">100 resultados</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between sm:space-x-4 gap-4">
                        <button type="button" onclick="applyFilters()"
                            class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <i data-lucide="search" class="w-5 h-5 sm:mr-2"></i>
                            <span class="sm:hidden">Aplicar Filtros</span>
                            <span class="hidden sm:inline">Aplicar</span>
                        </button>
                        <button type="button" onclick="resetFilters()"
                            class="w-full sm:w-auto px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <i data-lucide="refresh-cw" class="w-5 h-5 sm:mr-2"></i>
                            <span class="sm:hidden">Resetear</span>
                            <span class="hidden sm:inline">Resetear Filtros</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Estadísticas -->
            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas del Período</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Total Ingresos -->
                    <div class="bg-gradient-to-r from-green-400 to-green-600 p-6 rounded-lg border border-green-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-white rounded-full">
                                <i data-lucide="dollar-sign" class="w-8 h-8 text-green-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-green-800">S/. <span id="totalIncome">0.00</span></p>
                            <p class="text-sm text-green-600">Total Ingresos</p>
                        </div>
                    </div>

                    <!-- Métodos de Pago -->
                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 p-6 rounded-lg border border-blue-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-white rounded-full">
                                <i data-lucide="credit-card" class="w-8 h-8 text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-blue-800"><span id="yapePayments">0</span></p>
                            <p class="text-sm text-blue-600">Pagos con Yape</p>
                        </div>
                    </div>

                    <!-- Métodos de Pago -->
                    <div class="bg-gradient-to-r from-purple-400 to-purple-600 p-6 rounded-lg border border-purple-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-white rounded-full">
                                <i data-lucide="banknote" class="w-8 h-8 text-purple-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-purple-800"><span id="cashPayments">0</span></p>
                            <p class="text-sm text-purple-600">Pagos con Efectivo</p>
                        </div>
                    </div>

                    <!-- Clientes Activos -->
                    <div class="bg-gradient-to-r from-orange-400 to-orange-600 p-6 rounded-lg border border-orange-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-white rounded-full">
                                <i data-lucide="users" class="w-8 h-8 text-orange-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-orange-800"><span id="activeClients">0</span></p>
                            <p class="text-sm text-orange-600">Clientes Activos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos Estadísticos -->
            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Gráficos de Rendimiento</h2>
                    <div class="flex space-x-2">
                        <button onclick="changeChartPeriod('daily')" id="btnDaily" class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm">Diario</button>
                        <button onclick="changeChartPeriod('weekly')" id="btnWeekly" class="px-3 py-1 bg-gray-300 text-gray-700 rounded-md text-sm">Semanal</button>
                        <button onclick="changeChartPeriod('monthly')" id="btnMonthly" class="px-3 py-1 bg-gray-300 text-gray-700 rounded-md text-sm">Mensual</button>
                        <button onclick="changeChartPeriod('yearly')" id="btnYearly" class="px-3 py-1 bg-gray-300 text-gray-700 rounded-md text-sm">Anual</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Gráfico de Ingresos -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Evolución de Ingresos</h3>
                        <canvas id="incomeChart" width="400" height="200"></canvas>
                    </div>

                    <!-- Gráfico de Métodos de Pago -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Distribución por Método de Pago</h3>
                        <canvas id="paymentMethodChart" width="400" height="200"></canvas>
                    </div>

                    <!-- Gráfico de Comparación de Períodos -->
                    <div class="bg-gray-50 p-6 rounded-lg lg:col-span-2">
                        <h3 class="text-md font-semibold text-gray-800 mb-4">Comparación de Períodos</h3>
                        <canvas id="comparisonChart" width="800" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Calendario -->
            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Calendario de Pagos</h2>

                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <!-- Controles de navegación del calendario -->
                    <div class="flex items-center justify-between mb-4">
                        <button onclick="changeCalendarMonth(-1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </button>
                        <h3 id="calendarTitle" class="text-lg font-semibold text-gray-800"></h3>
                        <div class="flex space-x-2">
                            <button onclick="goToToday()" class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Hoy</button>
                            <button onclick="changeCalendarMonth(1)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                <i data-lucide="chevron-right" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Días de la semana -->
                    <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-gray-600 mb-2">
                        <div>Dom</div>
                        <div>Lun</div>
                        <div>Mar</div>
                        <div>Mié</div>
                        <div>Jue</div>
                        <div>Vie</div>
                        <div>Sáb</div>
                    </div>

                    <!-- Días del calendario -->
                    <div id="paymentCalendar" class="grid grid-cols-7 gap-1 text-sm">
                        <!-- Los días del calendario se generarán dinámicamente -->
                    </div>

                    <!-- Leyenda -->
                    <div class="mt-4 flex items-center justify-center space-x-4 text-sm">
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-green-100 border rounded mr-2"></div>
                            <span class="text-gray-600">Con pagos</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-gray-50 border rounded mr-2"></div>
                            <span class="text-gray-600">Sin pagos</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 bg-blue-100 border rounded mr-2"></div>
                            <span class="text-gray-600">Hoy</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Resultados -->
            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Resultados de Pagos</h2>
                    <div class="text-sm text-gray-500">
                        Mostrando <span id="resultCount">0</span> resultados
                    </div>
                    <button onclick="exportToExcel()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-md transition-colors">
                        <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                        Exportar a Excel
                    </button>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            </tr>
                        </thead>
                        <tbody id="paymentHistoryTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Las filas se generarán dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // =============================
    // Variables globales
    // =============================
    let paymentHistoryData = [];

    let currentFilters = {
        startDate: null,
        endDate: null,
        limit: 50
    };

    let currentChartPeriod = 'daily';
    let incomeChart, paymentMethodChart, comparisonChart;
    let currentCalendarDate = new Date();


    // =============================
    // Cargar historial de pagos
    // =============================
    async function loadPaymentHistory() {

        try {

            let query = new URLSearchParams(currentFilters).toString();

            const response = await fetch(`/asesor/payment-history?${query}`);
            const result = await response.json();

            if (result.success) {

                paymentHistoryData = result.data;

                updateStatistics(result.data);
                updatePaymentTable(result.data);
                updateResultCount(result.data.length);
                generateCalendar();
                updateCharts(result.data);

            } else {

                document.getElementById('paymentHistoryTableBody').innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        No se encontraron pagos para el período seleccionado
                    </td>
                </tr>
            `;

                updateResultCount(0);
            }

        } catch (error) {

            console.error('Error loading payment history:', error);

            document.getElementById('paymentHistoryTableBody').innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                    Error al cargar el historial de pagos
                </td>
            </tr>
        `;
        }
    }


    // =============================
    // Aplicar filtros
    // =============================
    function applyFilters() {

        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const limit = document.getElementById('limit').value;

        currentFilters = {
            startDate,
            endDate,
            limit
        };

        console.log('Aplicando filtros:', currentFilters);

        loadPaymentHistory();
    }


    // =============================
    // Resetear filtros
    // =============================
    function resetFilters() {

        document.getElementById('startDate').value = '';
        document.getElementById('endDate').value = '';
        document.getElementById('limit').value = '50';

        currentFilters = {
            startDate: null,
            endDate: null,
            limit: 50
        };

        console.log('Filtros reseteados');

        loadPaymentHistory();
    }


    // =============================
    // Actualizar estadísticas
    // =============================
    function updateStatistics(data) {

        const totalIncome = data.reduce((sum, payment) => sum + parseFloat(payment.amount), 0);

        const yapePayments = data.filter(
            payment => payment.payment_method.toLowerCase() === 'yape'
        ).length;

        const cashPayments = data.filter(
            payment => payment.payment_method.toLowerCase() === 'efectivo'
        ).length;

        const activeClients = new Set(data.map(payment => payment.client_name));

        document.getElementById('totalIncome').textContent = `S/. ${totalIncome.toFixed(2)}`;
        document.getElementById('yapePayments').textContent = yapePayments;
        document.getElementById('cashPayments').textContent = cashPayments;
        document.getElementById('activeClients').textContent = activeClients.size;
    }


    // =============================
    // Actualizar tabla
    // =============================
    function updatePaymentTable(data) {

        const tableBody = document.getElementById('paymentHistoryTableBody');

        if (data.length === 0) {

            tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                    No se encontraron pagos
                </td>
            </tr>
        `;

            return;
        }

        const rows = data.map(payment => {

            const statusClass =
                payment.status === 'pending_review' ?
                'bg-orange-100 text-orange-700' :
                payment.status === 'paid' ?
                'bg-green-100 text-green-700' :
                'bg-yellow-100 text-yellow-700';

            const statusText =
                payment.status === 'pending_review' ?
                'En revisión' :
                payment.status === 'paid' ?
                'Pagado' :
                'Pendiente';

            const methodClass =
                payment.payment_method === 'yape' ?
                'bg-blue-100 text-blue-700' :
                'bg-green-100 text-green-700';

            const methodIcon =
                payment.payment_method === 'yape' ?
                'smartphone' :
                'dollar-sign';

            return `
        <tr class="hover:bg-gray-50">

            <td class="px-6 py-4">
                ${payment.payment_date}
            </td>

            <td class="px-6 py-4">
                <div class="font-medium">${payment.client_name}</div>
                <div class="text-sm text-gray-500">${payment.client_dni}</div>
            </td>

            <td class="px-6 py-4 text-right font-semibold">
                S/. ${parseFloat(payment.amount).toFixed(2)}
            </td>

            <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs rounded-full ${methodClass}">
                    ${payment.payment_method === 'yape' ? 'Yape' : 'Efectivo'}
                </span>
            </td>

            <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs rounded-full ${statusClass}">
                    ${statusText}
                </span>
            </td>

            <td class="px-6 py-4">
                <span class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-600">
                    ${
                        payment.type === 'préstamo'
                        ? 'Préstamo #' + (payment.installment_number || payment.loan_id)
                        : 'Ahorro #' + (payment.savings_id || 'N/A')
                    }
                </span>
            </td>

        </tr>
        `;
        }).join('');

        tableBody.innerHTML = rows;
    }


    // =============================
    // Contador resultados
    // =============================
    function updateResultCount(count) {
        document.getElementById('resultCount').textContent = count;
    }


    // =============================
    // Generar calendario
    // =============================
    function generateCalendar() {
        const calendar = document.getElementById('paymentCalendar');
        const year = currentCalendarDate.getFullYear();
        const month = currentCalendarDate.getMonth();

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0).getDate();
        const today = new Date();

        // Actualizar título del calendario
        document.getElementById('calendarTitle').textContent =
            currentCalendarDate.toLocaleDateString('es-ES', {
                month: 'long',
                year: 'numeric'
            });

        let html = '';

        const startDay = firstDay.getDay();

        // Días vacíos al inicio
        for (let i = 0; i < startDay; i++) {
            html += `<div></div>`;
        }

        // Generar días del mes
        for (let day = 1; day <= lastDay; day++) {
            const date = new Date(year, month, day);
            const dateStr = date.toDateString();
            const todayStr = today.toDateString();

            const dayPayments = paymentHistoryData.filter(p => {
                return new Date(p.payment_date).toDateString() === dateStr;
            });

            const hasPayments = dayPayments.length > 0;
            const isToday = dateStr === todayStr;

            // Determinar clases CSS
            let bgClass = 'bg-gray-50';
            if (isToday) {
                bgClass = 'bg-blue-100';
            } else if (hasPayments) {
                bgClass = 'bg-green-100';
            }

            let cursorClass = 'cursor-default';
            if (hasPayments) {
                cursorClass = 'cursor-pointer hover:bg-green-200';
            }

            html += `
            <div class="p-2 border rounded-lg ${bgClass} ${cursorClass} transition-colors"
                 onclick="${hasPayments ? `showDayPayments('${dateStr}')` : ''}"
                 title="${hasPayments ? `${dayPayments.length} pago(s)` : 'Sin pagos'}">
                <div class="font-bold text-center">${day}</div>
                ${hasPayments ? `<div class="text-xs text-center">✓</div>` : ''}
                ${isToday ? '<div class="text-xs text-center text-blue-600">Hoy</div>' : ''}
            </div>
        `;
        }

        calendar.innerHTML = html;

        // Reinicializar iconos de Lucide
        lucide.createIcons();
    }

    // =============================
    // Funciones de navegación del calendario
    // =============================
    function changeCalendarMonth(direction) {
        currentCalendarDate.setMonth(currentCalendarDate.getMonth() + direction);
        generateCalendar();
    }

    function goToToday() {
        currentCalendarDate = new Date();
        generateCalendar();
    }

    function showDayPayments(dateStr) {
        // Convertir la fecha a formato YYYY-MM-DD
        const selectedDate = new Date(dateStr);
        const formattedDate = selectedDate.toISOString().split('T')[0];

        // Actualizar los filtros con el día seleccionado
        currentFilters = {
            startDate: formattedDate,
            endDate: formattedDate,
            limit: 50
        };

        // Actualizar los campos del formulario
        document.getElementById('startDate').value = formattedDate;
        document.getElementById('endDate').value = formattedDate;

        // Volver a cargar los datos con los filtros actualizados
        loadPaymentHistory();

        // Hacer scroll a la tabla de resultados
        setTimeout(() => {
            document.querySelector('#resultCount').scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }, 500);

        // Mostrar notificación informativa
        const dateFormatted = selectedDate.toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        showNotification(`Filtrando pagos de ${dateFormatted}`);
    }

    function showNotification(message) {
        // Crear notificación
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-opacity duration-300';
        notification.textContent = message;

        document.body.appendChild(notification);

        // Remover después de 3 segundos
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }


    // =============================
    // Exportar Excel
    // =============================
    function exportToExcel() {

        if (paymentHistoryData.length === 0) {
            alert('No hay datos para exportar');
            return;
        }

        const headers = ['Fecha', 'Cliente', 'DNI', 'Monto', 'Método', 'Estado', 'Tipo'];

        const csv = [
            headers.join(','),
            ...paymentHistoryData.map(p => [
                p.payment_date,
                p.client_name,
                p.client_dni,
                p.amount,
                p.payment_method,
                p.status,
                p.type
            ].join(','))
        ].join('\n');

        const blob = new Blob([csv], {
            type: 'text/csv;charset=utf-8;'
        });

        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = `historial_pagos_${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
    }


    // =============================
    // Funciones de Gráficos
    // =============================
    function updateCharts(data) {
        updateIncomeChart(data);
        updatePaymentMethodChart(data);
        updateComparisonChart(data);
    }

    function changeChartPeriod(period) {
        currentChartPeriod = period;

        // Actualizar botones
        document.querySelectorAll('[id^="btn"]').forEach(btn => {
            btn.className = 'px-3 py-1 bg-gray-300 text-gray-700 rounded-md text-sm';
        });
        document.getElementById('btn' + period.charAt(0).toUpperCase() + period.slice(1)).className = 'px-3 py-1 bg-blue-600 text-white rounded-md text-sm';

        // Actualizar gráficos
        updateCharts(paymentHistoryData);
    }

    function updateIncomeChart(data) {
        const ctx = document.getElementById('incomeChart').getContext('2d');

        const chartData = processChartData(data, 'income');

        if (incomeChart) {
            incomeChart.destroy();
        }

        incomeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Ingresos (S/.)',
                    data: chartData.values,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/. ' + value.toFixed(0);
                            }
                        }
                    }
                }
            }
        });
    }

    function updatePaymentMethodChart(data) {
        const ctx = document.getElementById('paymentMethodChart').getContext('2d');

        const yapeCount = data.filter(p => p.payment_method.toLowerCase() === 'yape').length;
        const cashCount = data.filter(p => p.payment_method.toLowerCase() === 'efectivo').length;

        if (paymentMethodChart) {
            paymentMethodChart.destroy();
        }

        paymentMethodChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Yape', 'Efectivo'],
                datasets: [{
                    data: [yapeCount, cashCount],
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)'
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    function updateComparisonChart(data) {
        const ctx = document.getElementById('comparisonChart').getContext('2d');

        const comparisonData = processComparisonData(data);

        if (comparisonChart) {
            comparisonChart.destroy();
        }

        comparisonChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: comparisonData.labels,
                datasets: [{
                        label: 'Préstamos',
                        data: comparisonData.loans,
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderColor: 'rgb(99, 102, 241)',
                        borderWidth: 1
                    },
                    {
                        label: 'Ahorros',
                        data: comparisonData.savings,
                        backgroundColor: 'rgba(251, 146, 60, 0.8)',
                        borderColor: 'rgb(251, 146, 60)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function processChartData(data, type) {
        const groupedData = {};

        data.forEach(payment => {
            const date = new Date(payment.payment_date);
            let key;

            switch (currentChartPeriod) {
                case 'daily':
                    key = date.toLocaleDateString('es-ES');
                    break;
                case 'weekly':
                    const weekStart = new Date(date.setDate(date.getDate() - date.getDay()));
                    key = `Semana ${Math.ceil((date.getDate() + new Date(date.getFullYear(), date.getMonth(), 1).getDay()) / 7)}`;
                    break;
                case 'monthly':
                    key = date.toLocaleDateString('es-ES', {
                        month: 'long',
                        year: 'numeric'
                    });
                    break;
                case 'yearly':
                    key = date.getFullYear().toString();
                    break;
            }

            if (!groupedData[key]) {
                groupedData[key] = 0;
            }

            if (type === 'income') {
                groupedData[key] += parseFloat(payment.amount);
            }
        });

        return {
            labels: Object.keys(groupedData),
            values: Object.values(groupedData)
        };
    }

    function processComparisonData(data) {
        const groupedData = {};

        data.forEach(payment => {
            const date = new Date(payment.payment_date);
            let key;

            switch (currentChartPeriod) {
                case 'daily':
                    key = date.toLocaleDateString('es-ES');
                    break;
                case 'weekly':
                    const weekStart = new Date(date.setDate(date.getDate() - date.getDay()));
                    key = `Semana ${Math.ceil((date.getDate() + new Date(date.getFullYear(), date.getMonth(), 1).getDay()) / 7)}`;
                    break;
                case 'monthly':
                    key = date.toLocaleDateString('es-ES', {
                        month: 'short'
                    });
                    break;
                case 'yearly':
                    key = date.getFullYear().toString();
                    break;
            }

            if (!groupedData[key]) {
                groupedData[key] = {
                    loans: 0,
                    savings: 0,
                    amounts: {
                        loans: 0,
                        savings: 0
                    }
                };
            }

            if (payment.type === 'préstamo') {
                groupedData[key].loans++;
                groupedData[key].amounts.loans += parseFloat(payment.amount);
            } else {
                groupedData[key].savings++;
                groupedData[key].amounts.savings += parseFloat(payment.amount);
            }
        });

        return {
            labels: Object.keys(groupedData),
            loans: Object.values(groupedData).map(d => d.amounts.loans),
            savings: Object.values(groupedData).map(d => d.amounts.savings)
        };
    }

    // =============================
    // Carga inicial
    // =============================
    document.addEventListener("DOMContentLoaded", function() {
        loadPaymentHistory();
    });
</script>
@endpush
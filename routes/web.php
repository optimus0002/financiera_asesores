<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Asesor\AsesorDashboardController;
use App\Http\Controllers\Asesor\ClientSearchController;
use App\Http\Controllers\Asesor\CollectionController;
use App\Http\Controllers\Asesor\ReportController;
use App\Http\Controllers\Admin\AdminReportController;

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



// Ruta principal que redirige según el rol del usuario
Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.reports');
        } else {
            return redirect()->route('asesor.dashboard');
        }
    }
    return redirect()->route('login');
});

// Rutas protegidas para asesores
Route::middleware(['auth'])->prefix('asesor')->name('asesor.')->group(function () {
    Route::get('/dashboard', [AsesorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/search-client', [ClientSearchController::class, 'search'])->name('client.search');
    Route::get('/recent-clients', [ClientSearchController::class, 'recentClients'])->name('recent-clients');
    Route::get('/client/{id}', [ClientSearchController::class, 'getClient'])->name('client.show');
    Route::get('/collection', [CollectionController::class, 'index'])->name('collection');
    Route::post('/collection/payment', [CollectionController::class, 'processPayment'])->name('collection.payment');
    Route::post('/savings-deposit', [CollectionController::class, 'savingsDeposit'])->name('savings.deposit');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/today-payments', [ReportController::class, 'todayPayments'])->name('today-payments');
    Route::post('/cash-closing', [ReportController::class, 'cashClosing'])->name('cash-closing');
    Route::get('/cash-closing-status', [ReportController::class, 'checkCashClosingStatus'])->name('cash-closing-status');
});

// Rutas protegidas para administradores
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports');
});

<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SolarFarmController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/periodo', [DashboardController::class, 'periodStats'])->name('dashboard.period');
    Route::post('/cerrar-sesion', [AuthController::class, 'logout'])->name('logout');
    Route::get('/granjas', [SolarFarmController::class, 'index'])->name('farms.index');
    Route::get('/granjas/nueva', [SolarFarmController::class, 'create'])->name('farms.create');
    Route::post('/granjas', [SolarFarmController::class, 'store'])->name('farms.store');
    Route::get('/granjas/{farm}', [SolarFarmController::class, 'show'])->name('farms.show');
    Route::get('/granjas/{farm}/editar', [SolarFarmController::class, 'edit'])->name('farms.edit');
    Route::put('/granjas/{farm}', [SolarFarmController::class, 'update'])->name('farms.update');
    Route::patch('/granjas/{farm}/desactivar', [SolarFarmController::class, 'deactivate'])->name('farms.deactivate');
    Route::post('/granjas/{farm}/paneles', [SolarFarmController::class, 'storeFarmPanel'])->name('farms.panels.store');
    Route::put('/granjas/{farm}/paneles/{farmPanel}', [SolarFarmController::class, 'updateFarmPanel'])->name('farms.panels.update');
    Route::delete('/granjas/{farm}/paneles/{farmPanel}', [SolarFarmController::class, 'destroyFarmPanel'])->name('farms.panels.destroy');
    Route::get('/paneles', [SolarFarmController::class, 'panels'])->name('panels.index');
    Route::get('/paneles/nuevo', [SolarFarmController::class, 'createPanel'])->name('panels.create');
    Route::post('/paneles', [SolarFarmController::class, 'storePanel'])->name('panels.store');
    Route::get('/paneles/{panel}/editar', [SolarFarmController::class, 'editPanel'])->name('panels.edit');
    Route::put('/paneles/{panel}', [SolarFarmController::class, 'updatePanel'])->name('panels.update');
    Route::patch('/paneles/{panel}/desactivar', [SolarFarmController::class, 'deactivatePanel'])->name('panels.deactivate');
    Route::patch('/paneles/instalaciones/{farmPanel}/estado', [SolarFarmController::class, 'togglePanelInstallation'])->name('panels.installations.toggle');
    Route::get('/generacion', [SolarFarmController::class, 'generation'])->name('records.index');
    Route::get('/reportes', [DashboardController::class, 'reports'])->name('reports.index');
    Route::get('/reportes/excel', [DashboardController::class, 'reportCsv'])->name('reports.csv');
    Route::get('/reportes/pdf', [DashboardController::class, 'reportPrint'])->name('reports.print');
    Route::get('/alertas', [DashboardController::class, 'alerts'])->name('alerts.index');
    Route::patch('/alertas/{alert}', [DashboardController::class, 'updateAlertStatus'])->name('alerts.update');
    Route::get('/proyecciones', [DashboardController::class, 'projections'])->name('projections.index');
    Route::get('/proyecciones/exportar', [DashboardController::class, 'projectionCsv'])->name('projections.csv');
    Route::get('/mapa', [DashboardController::class, 'map'])->name('map.index');
});

Route::prefix('api')->group(function () {
    Route::get('/docs', [ApiController::class, 'docs']);
    Route::get('/departments', [ApiController::class, 'departments']);
    Route::get('/farms', [ApiController::class, 'farms']);
    Route::get('/generation', [ApiController::class, 'generation']);
    Route::get('/stats', [DashboardController::class, 'apiStats']);
});

<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SolarFarmController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/granjas/nueva', [SolarFarmController::class, 'create'])->name('farms.create');
Route::post('/granjas', [SolarFarmController::class, 'store'])->name('farms.store');

Route::prefix('api')->group(function () {
    Route::get('/docs', [ApiController::class, 'docs']);
    Route::get('/departments', [ApiController::class, 'departments']);
    Route::get('/farms', [ApiController::class, 'farms']);
    Route::get('/generation', [ApiController::class, 'generation']);
    Route::get('/stats', [DashboardController::class, 'apiStats']);
});

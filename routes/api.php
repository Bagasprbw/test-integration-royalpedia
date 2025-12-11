<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Transaction API - No authentication required for testing
Route::post('/transaction', [TransactionController::class, 'store'])->name('api.transaction.store');
Route::get('/transaction/history', [TransactionController::class, 'history'])->name('api.transaction.history');

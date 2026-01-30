<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

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

// Informações da API
Route::get('/info', [TicketController::class, 'info']);

// Rotas de chamados (com rate limiting)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/chamados', [TicketController::class, 'store']);
});

Route::get('/chamados', [TicketController::class, 'index']);

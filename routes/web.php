<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));
Route::middleware('guest')->group(function () {
    Route::view('/entrar', 'auth.login')->name('login');
    Route::post('/entrar', [AuthController::class, 'login'])->middleware('throttle:20,1');
    Route::view('/cadastro', 'auth.register')->name('register');
    Route::post('/cadastro', [AuthController::class, 'register'])->middleware('throttle:5,1');
});
Route::middleware('auth')->group(function () {
    Route::post('/sair', [AuthController::class, 'logout'])->name('logout');
    Route::get('/painel', DashboardController::class)->name('dashboard');
    Route::get('/chamados', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/chamados/novo', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/chamados', [TicketController::class, 'store'])->middleware('throttle:15,1')->name('tickets.store');
    Route::get('/chamados/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/chamados/{ticket}/comentarios', [TicketController::class, 'comment'])->middleware('throttle:30,1')->name('tickets.comment');
    Route::patch('/chamados/{ticket}/status', [TicketController::class, 'status'])->name('tickets.status');
    Route::patch('/chamados/{ticket}/encerrar', [TicketController::class, 'close'])->name('tickets.close');
    Route::patch('/chamados/{ticket}/responsavel', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::get('/anexos/{attachment}', [TicketController::class, 'download'])->name('attachments.download');
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::post('/usuarios', [UserController::class, 'store'])->name('users.store');
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

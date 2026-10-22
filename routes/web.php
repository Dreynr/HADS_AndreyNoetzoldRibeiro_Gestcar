<?php

use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Client\AppointmentController;
use App\Http\Controllers\Client\VehicleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Pagina publica: abre sem login (RNF09).
Route::view('/politica-de-privacidade', 'privacy')->name('privacy');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rotas do cliente
Route::middleware(['auth', 'role:cliente'])->group(function () {
    Route::resource('vehicles', VehicleController::class)->except(['show']);

    Route::get('/agendamentos', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/agendamentos/novo', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/agendamentos', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::patch('/agendamentos/{appointment}/cancelar', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/agendamentos/periodos-disponiveis', [AppointmentController::class, 'availablePeriods'])->name('appointments.available-periods');
    Route::get('/agendamentos/datas-lotadas', [AppointmentController::class, 'bookedDates'])->name('appointments.booked-dates');

    Route::get('/historico', [AppointmentController::class, 'history'])->name('history.index');
});

// Rotas do admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('clients', ClientController::class)->parameters(['clients' => 'client']);
    Route::patch('/clients/{client}/toggle-active', [ClientController::class, 'toggleActive'])->name('clients.toggle-active');

    Route::post('/clients/{client}/notes', [ClientController::class, 'storeNote'])->name('clients.notes.store');
    Route::delete('/clients/{client}/notes/{note}', [ClientController::class, 'destroyNote'])->name('clients.notes.destroy');
    Route::post('/clients/{client}/follow-ups', [ClientController::class, 'storeFollowUp'])->name('clients.follow-ups.store');
    Route::patch('/clients/{client}/follow-ups/{followUp}', [ClientController::class, 'completeFollowUp'])->name('clients.follow-ups.complete');

    Route::resource('services', ServiceController::class)->except(['show']);
    Route::patch('/services/{service}/toggle-active', [ServiceController::class, 'toggleActive'])->name('services.toggle-active');

    Route::get('/agenda', [AdminAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/agenda/{appointment}', [AdminAppointmentController::class, 'show'])->name('appointments.show');
    Route::patch('/agenda/{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])->name('appointments.update-status');
    Route::get('/estoque', fn () => view('admin.stock.index'))->name('stock.index');
    Route::get('/relatorios', fn () => view('admin.reports.index'))->name('reports.index');
});

require __DIR__.'/auth.php';

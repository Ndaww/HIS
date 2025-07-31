<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use App\Notifications\TelegramTicketNotification;
use Illuminate\Support\Facades\Notification;

Route::get('/', function () {
    return view('layouts.app');
});

Route::get('/tes-telegram', function () {
    Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
        ->notify(new TelegramTicketNotification('Test pesan dari Laravel!'));

    return 'Terkirim!';
});


Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



Route::get('/ticketing/list-ticket', [TicketController::class, 'getDataTiketSaya'])->name('list-ticket');
Route::get('/api/ticket/{id}', [TicketController::class, 'getSingleTicketSaya']);
Route::get('/ticketing/list-ticket-dept', [TicketController::class, 'getDataTiketDept'])->name('list-ticket-dept');
Route::get('/api/ticket-dept/{id}', [TicketController::class, 'getSingleTicketDept']);
Route::get('/ticketing/dept', [TicketController::class, 'indexMyDept']);
Route::post('/ticketing/delegasi', [TicketController::class, 'delegasi']);
Route::post('/ticketing/progress', [TicketController::class, 'progress']);
Route::post('/ticketing/pending', [TicketController::class, 'pending']);
Route::post('/ticketing/solve', [TicketController::class, 'solve']);
Route::post('/ticketing/eskalasi', [TicketController::class, 'escalate']);
Route::post('/ticketing/selesai', [TicketController::class, 'selesai']);
Route::resource('/ticketing',TicketController::class)->middleware('auth');


Route::get('/reports/ticket', [ReportController::class, 'indexTicket']);
Route::get('/reports/ticket/get', [ReportController::class, 'getAllTicket'])->name('list-report-ticket');
Route::get('/reports/api/ticket/{id}', [ReportController::class, 'getSingleReportTicket']);
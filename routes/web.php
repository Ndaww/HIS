<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PreventiveTaskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WhatsappController;
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


// Ticketing
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


// Preventive
Route::get('/ajax/get-equipment-by-rooms', [PreventiveTaskController::class, 'getEquipmentByRooms'])->name('ajax.getEquipmentByRooms');
Route::get('/preventive/task',[PreventiveTaskController::class, 'indexTask']);
Route::get('/preventive-task/task/{id}/form', [PreventiveTaskController::class, 'createTask'])->name('preventive-task.task');
Route::post('/preventive-task/task/{id}/submit', [PreventiveTaskController::class, 'storeResult'])->name('preventive-task.store-task');
Route::get('/preventive/history', [PreventiveTaskController::class, 'history'])->name('preventive-task.history');
Route::get('/preventive-task/history/data', [PreventiveTaskController::class, 'historyData'])->name('preventive-task.history.data');
Route::resource('/preventive',PreventiveTaskController::class);


// Reports
// Ticketing
Route::get('/reports/ticket', [ReportController::class, 'indexTicket']);
Route::get('/reports/ticket/get', [ReportController::class, 'getAllTicket'])->name('list-report-ticket');
Route::get('/reports/api/ticket/{id}', [ReportController::class, 'getSingleReportTicket']);

// Preventive
Route::get('/reports/preventive', [ReportController::class, 'indexPreventive']);
Route::get('/reports/preventive/get', [ReportController::class, 'getAllPreventive'])->name('list-report-preventive');




// WA
Route::get('/kirim-whatsapp', [WhatsappController::class, 'kirim']);
<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('layouts.app');
});

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



Route::get('/ticketing/list-ticket', [TicketController::class, 'getDataTiketSaya'])->name('list-ticket');
Route::get('/api/ticket/{id}', [TicketController::class, 'getSingleTicketSaya']);
Route::post('/ticketing/selesai', [TicketController::class, 'selesai']);
Route::resource('/ticketing',TicketController::class)->middleware('auth');
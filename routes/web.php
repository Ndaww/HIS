<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('layouts.app');
});


Route::get('/ticketing/list-ticket', [TicketController::class, 'getData'])->name('list-ticket');
Route::resource('/ticketing',TicketController::class);
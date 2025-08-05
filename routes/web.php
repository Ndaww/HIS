<?php

use App\Http\Controllers\KonfirmasiPerawatController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MasterPatientController;
use App\Http\Controllers\MasterRoomController;
use App\Http\Controllers\PksController;
use App\Http\Controllers\PreventiveTaskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomBookingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ValidasiGAController;
use App\Http\Controllers\WhatsappController;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;
use App\Notifications\TelegramTicketNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    // return view('layouts.app');
    return redirect ('login');
})->middleware('guest');

Route::get('/dashboard', function(){
      $tickets = Ticket::all();

    // Summary
    $total = $tickets->count();
    $open = $tickets->where('status', 'open')->count();
    $priority = [
        'low' => $tickets->where('priority', 'low')->count(),
        'medium' => $tickets->where('priority', 'medium')->count(),
        'high' => $tickets->where('priority', 'high')->count(),
    ];

    // Tiket per hari
    $grouped = $tickets->groupBy(function ($item) {
        return Carbon::parse($item->created_at)->format('Y-m-d');
    });

    $dates = [];
    $counts = [];

    foreach ($grouped as $date => $items) {
        $dates[] = $date;
        $counts[] = $items->count();
    }

    return view('dashboard', compact('total', 'open', 'priority', 'dates', 'counts'));
})->middleware('auth')->name('dashboard');

// Route::get('/tes-telegram', function () {
//     Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
//         ->notify(new TelegramTicketNotification('Test pesan dari Laravel!'));

//     return 'Terkirim!';
// });


Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// Ticketing
Route::get('/ticketing/list-ticket', [TicketController::class, 'getDataTiketSaya'])->name('list-ticket');
Route::get('/api/ticket/{id}', [TicketController::class, 'getSingleTicketSaya']);
Route::get('/ticketing/list-ticket-dept', [TicketController::class, 'getDataTiketDept'])->name('list-ticket-dept');
Route::get('/api/ticket-dept/{id}', [TicketController::class, 'getSingleTicketDept']);
Route::get('/ticketing/dept', [TicketController::class, 'indexMyDept'])->middleware('auth');
Route::post('/ticketing/delegasi', [TicketController::class, 'delegasi']);
Route::post('/ticketing/progress', [TicketController::class, 'progress']);
Route::post('/ticketing/pending', [TicketController::class, 'pending']);
Route::post('/ticketing/solve', [TicketController::class, 'solve']);
Route::post('/ticketing/eskalasi', [TicketController::class, 'escalate']);
Route::post('/ticketing/selesai', [TicketController::class, 'selesai']);
Route::resource('/ticketing',TicketController::class)->middleware('auth');


// Preventive
Route::get('/ajax/get-equipment-by-rooms', [PreventiveTaskController::class, 'getEquipmentByRooms'])->name('ajax.getEquipmentByRooms');
Route::get('/preventive/task',[PreventiveTaskController::class, 'indexTask'])->middleware('auth');
Route::get('/preventive-task/task/{id}/form', [PreventiveTaskController::class, 'createTask'])->name('preventive-task.task');
Route::post('/preventive-task/task/{id}/submit', [PreventiveTaskController::class, 'storeResult'])->name('preventive-task.store-task');
Route::get('/preventive/history', [PreventiveTaskController::class, 'history'])->name('preventive-task.history');
Route::get('/preventive-task/history/data', [PreventiveTaskController::class, 'historyData'])->name('preventive-task.history.data');
Route::resource('/preventive',PreventiveTaskController::class);

// PKS
// dept
Route::get('/pks/create', [PksController::class, 'create']);
Route::get('/pks/pengajuan-saya', [PksController::class, 'mypks'])->name('pks.pengajuan-saya');
// legal
Route::get('/pks/verify', [PksController::class, 'indexSubmitted']);
Route::post('/pks/resubmit', [PksController::class, 'resubmit'])->name('pks.resubmit');
Route::post('/pks/verify', [PksController::class, 'verify'])->name('pks.verify');
Route::post('/pks/reupload-draft', [PksController::class, 'reuploadDraft'])->name('pks.reupload.draft');
Route::post('/pks/reject', [PksController::class, 'reject'])->name('pks.reject');
// direksi
Route::get('/pks/approval', [PksController::class, 'approval'])->name('pks.approval')->middleware('auth');
Route::post('/pks/approve', [PksController::class, 'approve'])->name('pks.approve')->middleware('auth');
Route::post('/pks/reject-approval', [PksController::class, 'rejectApproval'])->name('pks.rejectApproval')->middleware('auth');
Route::post('/pks/upload-final', [PksController::class, 'uploadFinal'])->name('pks.uploadFinal');
Route::get('/pks/{id}', [PksController::class, 'edit']);
Route::post('/pks/{id}/update', [PksController::class, 'update']);
Route::resource('/pks',PksController::class);
// Route::get('/pks/verify', [PksController::class, 'indexSubmitted']);


// Reports
// Ticketing
Route::get('/reports/ticket', [ReportController::class, 'indexTicket']);
Route::get('/reports/ticket/get', [ReportController::class, 'getAllTicket'])->name('list-report-ticket');
Route::get('/reports/api/ticket/{id}', [ReportController::class, 'getSingleReportTicket']);

// Preventive
Route::get('/reports/preventive', [ReportController::class, 'indexPreventive']);
Route::get('/reports/preventive/get', [ReportController::class, 'getAllPreventive'])->name('list-report-preventive');

// PKS
Route::get('/reports/pks', [ReportController::class, 'indexPKS']);
Route::get('/reports/pks/get', [ReportController::class, 'getAllPKS'])->name('list-report-pks');


// Registrasi pasien
// Route::resource('/registrasi',MasterPatientController::class);
// Route::get('/patients', [MasterPatientController::class, 'index'])->name('patients.index');
// Route::get('/patients/data', [MasterPatientController::class, 'indexData'])->name('patients.index.data');
// Route::post('/patients/checkout/{id}', [MasterPatientController::class, 'checkout'])->name('patients.checkout');

// Route::get('/registrasi', [MasterPatientController::class, 'create']);
// Route::post('/registrasi', [MasterPatientController::class, 'store'])->name('registrasi.store');

// master ruangan
Route::get('/master/rooms', [MasterRoomController::class, 'index'])->name('rooms.index');
Route::get('/master/rooms/data', [MasterRoomController::class, 'data'])->name('rooms.data');
Route::resource('/master/rooms', MasterRoomController::class);
Route::put('/master/rooms/{id}', [MasterRoomController::class, 'update'])->name('rooms.update');

// master pasien
Route::get('/master/patients', [MasterPatientController::class, 'index'])->name('patients.index');
Route::get('/master/patients/data', [MasterPatientController::class, 'data'])->name('patients.data');
Route::resource('/master/patients', MasterPatientController::class);
Route::put('/master/patients/{id}', [MasterPatientController::class, 'update'])->name('patients.update');

// room booking 
Route::get('/kamar-kosong/bookings', [RoomBookingController::class, 'index'])->name('bookings.index');
Route::post('/bookings', [RoomBookingController::class, 'store'])->name('bookings.store');
Route::get('/bookings/data', [RoomBookingController::class, 'data'])->name('bookings.data');
Route::delete('/bookings/{id}', [RoomBookingController::class, 'cancel'])->name('bookings.cancel');
Route::post('/bookings/{id}/checkout', [RoomBookingController::class, 'checkout'])->name('bookings.checkout');

// validasi ga
Route::get('/kamar-kosong/validasi', [ValidasiGAController::class, 'index'])->name('ga.rooms.index');
Route::get('/kamar-kosong/validasi/datatable', [ValidasiGAController::class, 'datatable'])->name('ga.rooms.datatable');
Route::post('/kamar-kosong/validasi/validasi', [ValidasiGAController::class, 'validateRoom'])->name('ga.rooms.validate');

// konfirmasi perawat
Route::get('/kamar-kosong/konfirmasi', [KonfirmasiPerawatController::class, 'index'])->name('nurse.confirm.index');
Route::get('/kamar-kosong/konfirmasi/datatable', [KonfirmasiPerawatController::class, 'datatable'])->name('nurse.confirm.datatable');
Route::post('/kamar-kosong/konfirmasi/store', [KonfirmasiPerawatController::class, 'store'])->name('nurse.confirm.store');







// WA
// Route::get('/kirim-whatsapp', [WhatsappController::class, 'kirim']);

Route::get('/zawa/qr', function () {
    $response = Http::post('https://api-zawa.azickri.com/authorize');

    $data = $response->json();

    // Tambahkan delay 2 detik
    sleep(5);


    $response = Http::get('https://api-zawa.azickri.com/qrcode?id='.$data['id'].'&session-id='.$data['sessionId']);

    $response = Http::withHeaders([
        'id' => $data['id'],
        'session-id' => $data['sessionId'],
        'Accept' => '*/*',
    ])->get('https://api-zawa.azickri.com/qrcode');
    $qr = $response->json();

    Session::put('zawa_id', $data['id']);
    Session::put('zawa_session_id', $data['sessionId']);
    Session::put('zawa_qr', $qr['qrcode']);

    dd(session()->all(), $qr, $response,$response->status(), $response->json(), $response->body()); 

    return view('zawa.qr', ['qr' => $qr['qrcode'] ?? null]);
});

Route::get('/zawa/qr/send', function() {
    $response = Http::withHeaders([
        'id' => Session::get('zawa_id'),
        'session-id' => Session::get('zawa_session_id'),
        'Accept' => '*/*',
        'Content-Type' => 'application/json',
    ])->post('https://api-zawa.azickri.com/message',[
        'phone' => '6287889643945',
        // 'group' => '6287889643945',
        'type' => 'text',
        'text' => 'TES WA
        *ASKDNAKJSDNAS*
        ~ANSDJNASJKDN~
        _ASNKDJNASJDN_
        ASDKJNASKDJNASJ',
    ]);
 $data = $response->json();

 return $data;

});



<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\KonfirmasiPerawatController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MasterEquipmentController;
use App\Http\Controllers\MasterPatientController;
use App\Http\Controllers\MasterRoomController;
use App\Http\Controllers\PksController;
use App\Http\Controllers\PreventiveEqController;
use App\Http\Controllers\PreventiveTaskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomBookingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketingV2Controller;
use App\Http\Controllers\ValidasiGAController;
use App\Http\Controllers\WhatsappController;
use App\Http\Controllers\ZawaController;
use App\Models\MasterEquipment;
use App\Models\MasterPreventive;
use App\Models\PreventiveTask;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;
use App\Notifications\TelegramTicketNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;


Route::get('/', function () {
    // return view('layouts.app');
    return redirect ('login');
})->middleware('guest');

// Route::get('/dashboard', function(){
//       $tickets = Ticket::all();

//     // Summary
//     $total = $tickets->count();
//     $open = $tickets->where('status', 'open')->count();
//     $priority = [
//         'low' => $tickets->where('priority', 'low')->count(),
//         'medium' => $tickets->where('priority', 'medium')->count(),
//         'high' => $tickets->where('priority', 'high')->count(),
//     ];

//     // Tiket per hari
//     $grouped = $tickets->groupBy(function ($item) {
//         return Carbon::parse($item->created_at)->format('Y-m-d');
//     });

//     $dates = [];
//     $counts = [];

//     foreach ($grouped as $date => $items) {
//         $dates[] = $date;
//         $counts[] = $items->count();
//     }

//     return view('dashboard', compact('total', 'open', 'priority', 'dates', 'counts'));
// })->middleware('auth')->name('dashboard');

// Route::get('/tes-telegram', function () {
//     Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
//         ->notify(new TelegramTicketNotification('Test pesan dari Laravel!'));

//     return 'Terkirim!';
// });

Route::get('/dashboard', function(){
    $tickets = Ticket::all();

    // --- Ringkasan (Info Cards) ---
    $total = $tickets->count();
    $open = $tickets->where('status', 'open')->count();
    $priority = [
        'low' => $tickets->where('priority', 'low')->count(),
        'medium' => $tickets->where('priority', 'medium')->count(),
        'high' => $tickets->where('priority', 'high')->count(),
    ];
    $closedToday = $tickets->where('status', 'closed')
                           ->whereBetween('updated_at', [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()])
                           ->count();

    $dailyStatus = [
        'open' => $tickets->where('status', 'open')->count(),
        'solved' => $tickets->where('status', 'solved')->count(),
        'in_progress' => $tickets->where('status', 'in_progress')->count(),
        'closed' => $tickets->where('status', 'closed')->count(),
        'pending' => $tickets->where('status', 'pending')->count(),
    ];

    $startDate = Carbon::now()->subMonth()->startOfMonth();
    $endDate = Carbon::now()->endOfMonth();

    $monthlyTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])
                            ->orderBy('created_at', 'asc')
                            ->get();

    $groupedMonthly = $monthlyTickets->groupBy(function ($item) {
        return Carbon::parse($item->created_at)->format('d-M-y');
    });

    $monthlyDates = [];
    $monthlyCounts = [];

    foreach ($groupedMonthly as $date => $items) {
        $monthlyDates[] = $date;
        $monthlyCounts[] = $items->count();
    }

    $latestTickets = Ticket::orderBy('created_at', 'desc')->limit(10)->get();

    $monthsForSelect = [];
    for ($i = 0; $i < 11; $i++) {
        $month = Carbon::now()->subMonths($i);
        $monthsForSelect[] = [
            'value' => $month->format('Y-m'),
            'label' => $month->isoFormat('MMMM YYYY')
        ];
    }
    $monthsForSelect = array_reverse($monthsForSelect);


    return view('dashboard', compact(
        'total', 'open', 'priority', 'closedToday',
        'dailyStatus',
        'monthlyDates', 'monthlyCounts',
        'latestTickets',
        'monthsForSelect'
    ));
})->middleware('auth')->name('dashboard');

Route::get('/dashboard/monthly-tickets', function (Request $request) {
    try {
        $monthYear = $request->query('month', Carbon::now()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $monthYear)) {
            return response()->json(['error' => 'Invalid month format.'], 400);
        }

        $dateInstance = Carbon::parse($monthYear);

        $startDate = $dateInstance->copy()->startOfMonth();
        // kalo bulan ini, ambil hari ini
        if ($monthYear == Carbon::now()->format('Y-m')) {
            $endDate = Carbon::now()->endOfDay();
        } else {
            $endDate = $dateInstance->copy()->endOfMonth();
        }

        // dd('StartDate:', $startDate->toDateTimeString(), 'EndDate:', $endDate->toDateTimeString());

        $tickets = Ticket::whereBetween('created_at', [$startDate, $endDate])
                         ->orderBy('created_at', 'asc')
                         ->get();

        $grouped = $tickets->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('d-M-y');
        });

        $dates = [];
        $counts = [];

        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $formattedDate = $d->format('d-M-y');
            $dates[] = $formattedDate;
            $counts[] = $grouped->has($formattedDate) ? $grouped[$formattedDate]->count() : 0;
        }

        return response()->json([
            'labels' => $dates,
            'data' => $counts
        ]);

    } catch (\Exception $e) {
        \Log::error('Error fetching monthly ticket data: ' . $e->getMessage());
        return response()->json(['error' => 'An unexpected error occurred. Please check the server logs.'], 500);
    }
})->middleware('auth')->name('dashboard.monthly-tickets');





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
Route::get('/ticketing/{ticket}/show', [TicketController::class, 'show']);
Route::resource('/ticketing',TicketController::class)->middleware('auth');

// Ticketing V2
Route::get('/ticket/v2/ticket-counts', [TicketingV2Controller::class, 'getTicketCounts']);
Route::get('/ticket/v2/ticket-depts-counts', [TicketingV2Controller::class, 'getTicketDeptsCounts']);
Route::get('/ticket/v2/list-ticket-dept', [TicketingV2Controller::class, 'getDataTiketDept'])->name('list-ticket-dept-v2');
Route::get('/ticket/v2/dept', [TicketingV2Controller::class, 'indexMyDept'])->middleware('auth');
Route::resource('/ticket/v2',TicketingV2Controller::class)->middleware('auth');


// Preventive
Route::get('/ajax/get-equipment-by-rooms', [PreventiveTaskController::class, 'getEquipmentByRooms'])->name('ajax.getEquipmentByRooms');
Route::get('/preventive/task',[PreventiveTaskController::class, 'indexTask'])->middleware('auth');
Route::get('/preventive-task/task/{id}/form', [PreventiveTaskController::class, 'createTask'])->name('preventive-task.task');
Route::post('/preventive-task/task/{id}/submit', [PreventiveTaskController::class, 'storeResult'])->name('preventive-task.store-task');
Route::get('/preventive/history', [PreventiveTaskController::class, 'history'])->name('preventive-task.history');
Route::get('/preventive-task/history/data', [PreventiveTaskController::class, 'historyData'])->name('preventive-task.history.data');
Route::resource('/preventive',PreventiveTaskController::class);

// Preventive Equipment
Route::get('/preventive-task/equipment/{id}/form', [PreventiveEqController::class, 'createTask'])->name('preventive-task.equipment');
Route::get('/get-equipments/{id}/{roomId}', [PreventiveEqController::class, 'getEquipments'])->name('get-equipments');
Route::get('/get-preventive-tasks/{eqTypeId}', [PreventiveEqController::class, 'getPreventiveTasks'])->name('get-preventive-tasks');
Route::get('/check-preventive-status/{equipmentId}', [PreventiveEqController::class, 'checkPreventiveStatus'])->name('check-preventive-status');
Route::post('/preventive/store-task', [PreventiveEqController::class, 'storeTask'])->name('preventive-task-equipment.store-task');


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

// master dept
Route::get('/master/depts', [DepartmentController::class, 'index'])->name('master.depts.index');
Route::post('/master/depts', [DepartmentController::class, 'store'])->name('master.depts.store');
Route::get('/master/depts/{department}', [DepartmentController::class, 'show'])->name('master.depts.show');
Route::put('/master/depts/{department}', [DepartmentController::class, 'update'])->name('master.depts.update');
Route::delete('/master/depts/{department}', [DepartmentController::class, 'destroy'])->name('master.depts.destroy');

// Master Equipment Type Routes
Route::get('/master/equipments',[MasterEquipmentController::class,'index'])->name('master-equipment.index');

Route::get('/type-datatable', [MasterEquipmentController::class, 'getTypeDatatable'])->name('master-equipment-type.datatable');
Route::post('/type', [MasterEquipmentController::class, 'storeType'])->name('master-equipment-type.store');
Route::get('/type/{id}', [MasterEquipmentController::class, 'showType'])->name('master-equipment-type.show');
Route::delete('/type/{id}', [MasterEquipmentController::class, 'destroyType'])->name('master-equipment-type.destroy');

// Master Equipment Routes
Route::get('/equipment-datatable', [MasterEquipmentController::class, 'getEquipmentDatatable'])->name('master-equipment.datatable');
Route::post('/equipment', [MasterEquipmentController::class, 'storeEquipment'])->name('master-equipment.store');
Route::get('/equipment/{id}', [MasterEquipmentController::class, 'showEquipment'])->name('master-equipment.show');
Route::delete('/equipment/{id}', [MasterEquipmentController::class, 'destroyEquipment'])->name('master-equipment.destroy');

// master pasien
// Route::get('/master/patients', [MasterPatientController::class, 'index'])->name('patients.index');
// Route::get('/master/patients/data', [MasterPatientController::class, 'data'])->name('patients.data');
// Route::resource('/master/patients', MasterPatientController::class);
// Route::put('/master/patients/{id}', [MasterPatientController::class, 'update'])->name('patients.update');

// room booking
// Route::get('/kamar-kosong/bookings', [RoomBookingController::class, 'index'])->name('bookings.index');
// Route::post('/bookings', [RoomBookingController::class, 'store'])->name('bookings.store');
// Route::get('/bookings/data', [RoomBookingController::class, 'data'])->name('bookings.data');
// Route::delete('/bookings/{id}', [RoomBookingController::class, 'cancel'])->name('bookings.cancel');
// Route::post('/bookings/{id}/checkout', [RoomBookingController::class, 'checkout'])->name('bookings.checkout');

// validasi ga
// Route::get('/kamar-kosong/validasi', [ValidasiGAController::class, 'index'])->name('ga.rooms.index');
// Route::get('/kamar-kosong/validasi/datatable', [ValidasiGAController::class, 'datatable'])->name('ga.rooms.datatable');
// Route::post('/kamar-kosong/validasi/validasi', [ValidasiGAController::class, 'validateRoom'])->name('ga.rooms.validate');

// konfirmasi perawat
// Route::get('/kamar-kosong/konfirmasi', [KonfirmasiPerawatController::class, 'index'])->name('nurse.confirm.index');
// Route::get('/kamar-kosong/konfirmasi/datatable', [KonfirmasiPerawatController::class, 'datatable'])->name('nurse.confirm.datatable');
// Route::post('/kamar-kosong/konfirmasi/store', [KonfirmasiPerawatController::class, 'store'])->name('nurse.confirm.store');







// WA
// Route::get('/kirim-whatsapp', [WhatsappController::class, 'kirim']);

// Route::get('/zawa/qr', function () {
//     $response = Http::post('https://api-zawa.azickri.com/authorize');

//     $data = $response->json();

//     // delay 5 detik menunggu response
//     sleep(5);

//     $response = Http::get('https://api-zawa.azickri.com/qrcode?id='.$data['id'].'&session-id='.$data['sessionId']);

//     $response = Http::withHeaders([
//         'id' => $data['id'],
//         'session-id' => $data['sessionId'],
//         'Accept' => '*/*',
//     ])->get('https://api-zawa.azickri.com/qrcode');
//     $qr = $response->json();

//     Session::put('zawa_id', $data['id']);
//     Session::put('zawa_session_id', $data['sessionId']);
//     Session::put('zawa_qr', $qr['qrcode']);

//     // dd(session()->all(), $qr, $response,$response->status(), $response->json(), $response->body());

//     return view('zawa.qr', ['qr' => $qr['qrcode'] ?? null]);
// });

Route::get('/zawa/qr', function () {
    // Langkah 1: Cek apakah sesi Zawa sudah ada di .env
    // Jika sudah, langsung tampilkan pesan sukses dan keluar
    if (env('ZAWA_ID') && env('ZAWA_SESSION_ID')) {
        return "Zawa sudah terhubung. Sesi telah tersimpan di file .env.";
    }

    // Langkah 2: Jika belum ada, panggil API untuk mendapatkan QR Code
    $response = Http::post('https://api-zawa.azickri.com/authorize');

    if ($response->failed()) {
        return "Gagal menginisiasi otorisasi Zawa. Coba lagi.";
    }

    $data = $response->json();
    $id = $data['id'];
    $sessionId = $data['sessionId'];

    // Menunggu beberapa detik agar QR code siap
    sleep(5);

    // Langkah 3: Ambil gambar QR Code
    $qrResponse = Http::withHeaders([
        'id' => $id,
        'session-id' => $sessionId,
        'Accept' => '*/*',
    ])->get('https://api-zawa.azickri.com/qrcode');

    if ($qrResponse->failed()) {
        return "Gagal mendapatkan QR Code. Silakan muat ulang halaman.";
    }

    $qr = $qrResponse->json();
    $qrCodeImage = $qr['qrcode'] ?? null;

    // Langkah 4: Simpan id dan sessionId ke file .env
    $envPath = base_path('.env');
    $envContent = File::get($envPath);

    // Tambahkan baris baru ke file .env
    $envContent .= "\nZAWA_ID={$id}";
    $envContent .= "\nZAWA_SESSION_ID={$sessionId}";

    File::put($envPath, $envContent);

    return view('zawa.qr', ['qr' => $qrCodeImage]);
});

// Route::get('/zawa/qr/send', function() {
//     $id = env('ZAWA_ID');
//     $sessionId = env('ZAWA_SESSION_ID');

//     $response = Http::withHeaders([
//         // 'id' => Session::get('zawa_id'),
//         // 'session-id' => Session::get('zawa_session_id'),
//         'id' => $id,
//         'session-id' => $sessionId,
//         'Accept' => '*/*',
//         'Content-Type' => 'application/json',
//     ])->post('https://api-zawa.azickri.com/message',[
//         'phone' => '6287889643945',
//         // 'group' => '6287889643945',
//         'type' => 'text',
//         'text' => 'TES WA
//         *ASKDNAKJSDNAS*
//         ~ANSDJNASJKDN~
//         _ASNKDJNASJDN_
//         ASDKJNASKDJNASJ',
//     ]);
//  $data = $response->json();

//  return $data;

// });

Route::get('/zawa/create-session', [ZawaController::class, 'createSession']);
Route::get('/zawa/check-status', [ZawaController::class, 'checkStatus']);
Route::get('/zawa/qr/send', [ZawaController::class, 'sendTestNotification']);
Route::get('/zawa/reconnect-session', [ZawaController::class, 'reconnectSession']);

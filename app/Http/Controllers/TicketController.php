<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Department;
use App\Models\TicketAttachment;
use App\Models\TicketSubstitution;
use App\Models\User;
use App\Notifications\TelegramTicketNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
// use Illuminate\Notifications\Notification;
use Yajra\DataTables\Facades\DataTables;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tickets = Ticket::all();
        // dd($tickets);

        return view ('pages.ticketing.index',[
            'tickets' => $tickets
        ]);
    }

    public function indexMyDept()
    {
        $tickets = Ticket::all();
        // dd($tickets);

        return view ('pages.ticketing.indexMyDept2',[
            'tickets' => $tickets
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $response = Notification::route('telegram', '-4926836909')->notify(new TelegramTicketNotification("Test"));
        // dd($response);
        $users = User::all();
        $departments = Department::all();
        return view('pages.ticketing.create',[
            'users' => $users,
            'departments' => $departments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required',
                'priority' => 'required|in:low,medium,high',
                'department_id' => 'required|exists:departments,id',
                'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,webp|max:2048' // max 2mb
            ]);

            $department = Department::find($request->department_id);
            $nomorTiket = $this->generateNomorTiket(strtoupper($department->short_name ?? $department->name));

            $ticket = Ticket::create([
                'ticket_number' => $nomorTiket,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'requester_id' => auth()->user()->id,
                'department_id' => $validated['department_id'],
                'priority' => $validated['priority'],
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = $file->store('attachments', 'public');

                    TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'file_path' => $filename
                    ]);
                }
            }

            // $ticket->load(['requester', 'dept.head','assigned']);
            // \Log::info('Ticket created', ['ticket' => $ticket->toArray()]);
            // Notification::route('telegram', env('TELEGRAM_CHAT_ID'))->notify(new TelegramTicketNotification($ticket));

            // try {
            //     Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
            //     ->notify(new TelegramTicketNotification($ticket));
            // } catch (\Throwable $e) {
            //     Log::info('Kirim Telegram', ['ticket' => $ticket->toArray()]);
            //     Log::info(env('TELEGRAM_CHAT_ID'));

            //     return response()->json([
            //         'message' => 'Gagal kirim notifikasi Telegram',
            //         'error' => $e->getMessage()
            //     ], 500);
            // }

            $ticket->load(['requester', 'dept.head','assigned']);

            $message = $ticket->generateMessage();
            $response = Http::withHeaders([
                'id' => Session::get('zawa_id'),
                'session-id' => Session::get('zawa_session_id'),
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ])->post('https://api-zawa.azickri.com/message',[
                'phone' => '6287889643945',
                // 'group' => '6287889643945',
                'type' => 'text',
                'text' => $message,
            ]);

            return response()->json([
                'message' => 'Tiket berhasil dikirim!',
                'ticket_number' => $ticket->ticket_number
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan tiket <br> Pastikan masing-masing file berukuran maks. 2MB',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        //
    }

    public function delegasi(Request $request)
    {

        try {

            $ticket = Ticket::findOrFail($request->id);
            $ticket->update([
                'status' => 'in_progress',
                'updated_at' => now(),
                'assigned_employee_id' => $request->delegated_employee
            ]);

            $ticket->load(['requester', 'dept','assigned']);

            // try {
            //     Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
            //     ->notify(new TelegramTicketNotification($ticket));
            // } catch (\Throwable $e) {
            //     \Log::info($ticket);
            //     return response()->json([
            //         'message' => 'Gagal kirim notifikasi Telegram',
            //         'error' => $e->getMessage()
            //     ], 500);
            // }

            $ticket->load(['requester', 'dept.head','assigned']);

            $message = $ticket->generateMessage();
            $response = Http::withHeaders([
                'id' => Session::get('zawa_id'),
                'session-id' => Session::get('zawa_session_id'),
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ])->post('https://api-zawa.azickri.com/message',[
                'phone' => '6287889643945',
                // 'group' => '6287889643945',
                'type' => 'text',
                'text' => $message,
            ]);

            return response()->json([
                'message' => 'Tiket Berhasil Didelegasikan!',
                'ticket_number' => $ticket->ticket_number,
                'assigned' => $ticket->assigned->name
            ], 200);


        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function progress(Request $request)
    {

        try {

            $ticket = Ticket::findOrFail($request->id);
            $ticket->update([
                'status' => 'in_progress',
                'updated_at' => now(),
                'assigned_employee_id' => auth()->user()->id
            ]);

            $ticket->load(['requester', 'dept','assigned']);

            // try {
            //     Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
            //     ->notify(new TelegramTicketNotification($ticket));
            // } catch (\Throwable $e) {
            //     \Log::info($ticket);
            //     return response()->json([
            //         'message' => 'Gagal kirim notifikasi Telegram',
            //         'error' => $e->getMessage()
            //     ], 500);
            // }

            $ticket->load(['requester', 'dept.head','assigned']);

            $message = $ticket->generateMessage();
            $response = Http::withHeaders([
                'id' => Session::get('zawa_id'),
                'session-id' => Session::get('zawa_session_id'),
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ])->post('https://api-zawa.azickri.com/message',[
                'phone' => '6287889643945',
                // 'group' => '6287889643945',
                'type' => 'text',
                'text' => $message,
            ]);

            return response()->json([
                'message' => 'Tiket Diproses!',
                'ticket_number' => $ticket->ticket_number,
                'assigned' => $ticket->assigned->name
            ], 200);


        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function pending(Request $request)
    {

        try {

            $ticket = Ticket::findOrFail($request->id);
            $ticket->update([
                'status' => 'pending',
                'updated_at' => now(),
                'pending_reason' => $request->reason
            ]);

            // $ticket->load(['requester', 'dept','assigned']);
            // try {
            //     Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
            //     ->notify(new TelegramTicketNotification($ticket));
            // } catch (\Throwable $e) {
            //     \Log::info($ticket);
            //     return response()->json([
            //         'message' => 'Gagal kirim notifikasi Telegram',
            //         'error' => $e->getMessage()
            //     ], 500);
            // }

            $ticket->load(['requester', 'dept.head','assigned']);

            $message = $ticket->generateMessage();
            $response = Http::withHeaders([
                'id' => Session::get('zawa_id'),
                'session-id' => Session::get('zawa_session_id'),
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ])->post('https://api-zawa.azickri.com/message',[
                'phone' => '6287889643945',
                // 'group' => '6287889643945',
                'type' => 'text',
                'text' => $message,
            ]);

            return response()->json([
                'message' => 'Tiket Dipending!',
                'ticket_number' => $ticket->ticket_number,
                'assigned' => $ticket->assigned->name
            ], 200);


        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function solve(Request $request)
    {

        try {
            $request->validate([
                'keterangan' => 'nullable|string|max:1000',
                'attachments.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            $ticket = Ticket::findOrFail($request->id);
            $ticket->update([
                'status' => 'solved',
                'updated_at' => now(),
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = $file->store('attachments', 'public');

                    TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'type' => 'solved',
                        'file_path' => $filename
                    ]);
                }
            }

            // $ticket->load(['requester', 'dept','assigned']);

            // try {
            //     Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
            //     ->notify(new TelegramTicketNotification($ticket));
            // } catch (\Throwable $e) {
            //     \Log::info($ticket);
            //     return response()->json([
            //         'message' => 'Gagal kirim notifikasi Telegram',
            //         'error' => $e->getMessage()
            //     ], 500);
            // }

            $ticket->load(['requester', 'dept.head','assigned']);

            $message = $ticket->generateMessage();
            $response = Http::withHeaders([
                'id' => Session::get('zawa_id'),
                'session-id' => Session::get('zawa_session_id'),
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ])->post('https://api-zawa.azickri.com/message',[
                'phone' => '6287889643945',
                // 'group' => '6287889643945',
                'type' => 'text',
                'text' => $message,
            ]);

            return response()->json([
                'message' => 'Tiket Dipending!',
                'ticket_number' => $ticket->ticket_number,
                'assigned' => $ticket->assigned->name
            ], 200);


        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function escalate(Request $request)
    {

        try {

            $ticket = Ticket::findOrFail($request->id);
            $ticket->update([
                'status' => 'escalated',
                'updated_at' => now(),
                'assigned_employee_id' => $request->escalated_employee
            ]);

            // $ticket->load(['requester', 'dept','assigned']);

            // try {
            //     Notification::route('telegram', env('TELEGRAM_CHAT_ID'))
            //     ->notify(new TelegramTicketNotification($ticket));
            // } catch (\Throwable $e) {
            //     \Log::info($ticket);
            //     return response()->json([
            //         'message' => 'Gagal kirim notifikasi Telegram',
            //         'error' => $e->getMessage()
            //     ], 500);
            // }

            $ticket->load(['requester', 'dept.head','assigned']);

            $message = $ticket->generateMessage();
            $response = Http::withHeaders([
                'id' => Session::get('zawa_id'),
                'session-id' => Session::get('zawa_session_id'),
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ])->post('https://api-zawa.azickri.com/message',[
                'phone' => '6287889643945',
                // 'group' => '6287889643945',
                'type' => 'text',
                'text' => $message,
            ]);

            TicketSubstitution::create([
                'ticket_id' => $request->id,
                'from_user_id' => auth()->user()->id,
                'to_user_id' => $request->escalated_employee,
                'reason' => $request->escalated_reason
            ]);

            $ticket->update([
                'status' => 'in_progress',
            ]);

            return response()->json([
                'message' => 'Tiket Berhasil dieskalasi ke '.$ticket->assigned->name,
                'ticket_number' => $ticket->ticket_number,
                'assigned' => $ticket->assigned->name
            ], 200);


        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function selesai(Request $request)
    {

        try {
            $ticket = Ticket::where('id',$request->id)->get()[0];
            Ticket::where('id',$request->id)->update([
                'status' => 'closed',
                'updated_at' => now()
            ]);

            $ticket->load(['requester', 'dept.head','assigned']);

            $message = $ticket->generateMessage();
            $response = Http::withHeaders([
                'id' => Session::get('zawa_id'),
                'session-id' => Session::get('zawa_session_id'),
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ])->post('https://api-zawa.azickri.com/message',[
                'phone' => '6287889643945',
                // 'group' => '6287889643945',
                'type' => 'text',
                'text' => $message,
            ]);

            return response()->json([
                'message' => 'Tiket Selesai!',
                'ticket_number' => $ticket->ticket_number
            ], 200);


        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function getDataTiketSaya(Request $request)
    {
        try {
            $user = auth()->user()->id;
            $tickets = Ticket::query()->where('requester_id',$user);

            $startInput = $request->query('start_date');
            $endInput = $request->query('end_date');
            $statusInput = $request->query('status');

            if (!empty($startInput) && !empty($endInput)) {
                \Log::info('Start Date: ' . $startInput);
                \Log::info('End Date: ' . $endInput);

                try {
                    $start = Carbon::parse($startInput)->startOfDay();
                    $end = Carbon::parse($endInput)->endOfDay();

                    $tickets = $tickets->whereBetween('created_at', [$start, $end]);
                } catch (\Exception $e) {
                    \Log::error('Gagal parsing tanggal:', [$e->getMessage()]);
                }
            }

            if(!empty($statusInput)){
                \Log::info('status: ' . $statusInput);

                try {

                    $tickets = $tickets->where('status',$statusInput);
                } catch (\Exception $e) {
                    \Log::error('Gagal parsing tanggal:', [$e->getMessage()]);
                }
            }


            return DataTables::of($tickets)
                ->addIndexColumn()
                ->addColumn('action', function ($ticket) {
                    // $isDisabled = $ticket->status !== 'closed' ? 'disabled' : '';
                     return '
                        <a href="javascript:void(0)"
                            class="btn btn-sm btn-outline-info btn-view"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Lihat"
                            title="Lihat">
                            <i class="ri-sm ri-eye-line"></i>
                        </a>
                    ';
                })

                ->addColumn('requester_name', function ($ticket) {
                return optional($ticket->requester)->name ?? '-';
                })
                ->addColumn('dept_name', function ($ticket) {
                return optional($ticket->dept)->name ?? '-';
                })
                ->addColumn('assigned_name', function ($ticket) {
                return optional($ticket->assigned)->name ?? '-';
                })
                ->editColumn('created_at', function($ticket){
                    return $ticket->created_at->format('d-m-Y H:i');
                })
                ->editColumn('updated_at', function($ticket){
                    return $ticket->updated_at->format('d-m-Y H:i');
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getSingleTicketSaya($id)
    {
        $ticket = Ticket::with(['requester', 'dept','attachmentsOpen','attachmentsClose'])->findOrFail($id);

        return response()->json([
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'created_at' => $ticket->created_at->format('d-m-Y H:i'),
            'requester_name' => optional($ticket->requester)->name,
            'department_name' => optional($ticket->dept)->name,
            'attachments_open' => $ticket->attachmentsOpen->map(function ($a) {
                return [
                    'file_path' => asset('/storage/'.$a->file_path),
                    'type' => $a->type,
                ];
            }),
            'attachments_close' => $ticket->attachmentsClose->map(function ($a) {
                return [
                    'file_path' => asset('/storage/'.$a->file_path),
                    'type' => $a->type,
                ];
            })
        ]);
    }

    public function getDataTiketDept(Request $request)
    {
        try {
            $user = auth()->user();
            $head = Department::where('head_id',$user->id)->count();

            $tickets = Ticket::query()->where('department_id',$user->department_id);
            $myTicket = Ticket::where('assigned_employee_id', auth()->user()->id)->pluck('id')->toArray();

            $startInput = $request->query('start_date');
            $endInput = $request->query('end_date');
            $statusInput = $request->query('status');

            if (!empty($startInput) && !empty($endInput)) {
                try {
                    $start = Carbon::parse($startInput)->startOfDay();
                    $end = Carbon::parse($endInput)->endOfDay();

                    $tickets = $tickets->whereBetween('created_at', [$start, $end]);
                } catch (\Exception $e) {
                    \Log::error('Gagal parsing tanggal:', [$e->getMessage()]);
                }
            }

            if(!empty($statusInput)){
                try {

                    $tickets = $tickets->where('status',$statusInput);
                } catch (\Exception $e) {
                    \Log::error('Gagal parsing tanggal:', [$e->getMessage()]);
                }
            }


            return DataTables::of($tickets)
                ->addIndexColumn()
                ->addColumn('action', function ($ticket) use($head,$myTicket) {
                    $isOpen = $ticket->status === 'open' ? '' : 'disabled';
                    $isHead = $head > 0 && $ticket->status =='open' ? '' : 'disabled';
                    $isMyTicket = in_array($ticket->id, $myTicket) ? '' : 'disabled';

                     return '
                        <button href="javascript:void(0)"
                            class="btn btn-sm btn-info btn-view"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Lihat"
                            title="Lihat" >
                            <i class="ri-sm ri-eye-line"></i>
                        </button>

                        <button href="javascript:void(0)"
                            class="btn btn-sm btn-primary btn-delegasi"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Delegasikan"
                            title="Delegasikan" '."$isHead".' >
                            <i class="ri-sm ri-send-plane-line"></i>
                        </button>

                        <button href="javascript:void(0)"
                            class="btn btn-sm btn-danger btn-pending"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Pending"
                            title="Pending" '." $isMyTicket ".'>
                            <i class="ri-sm ri-compass-4-line"></i>
                        </button>

                        <button href="javascript:void(0)"
                            class="btn btn-sm btn-success btn-solve"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Solved"
                            title="Solved" '." $isMyTicket ".'>
                            <i class="ri-sm ri-check-line"></i>
                        </button>

                        <button href="javascript:void(0)"
                            class="btn btn-sm btn-warning btn-eskalasi"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Eskalasi"
                            title="Eskalasi" '." $isMyTicket ".'>
                            <i class="ri-sm ri-exchange-2-line"></i>
                        </button>
                    ';
                })

                ->addColumn('requester_name', function ($ticket) {
                return optional($ticket->requester)->name ?? '-';
                })
                ->addColumn('dept_name', function ($ticket) {
                return optional($ticket->dept)->name ?? '-';
                })
                ->addColumn('assigned_name', function ($ticket) {
                return optional($ticket->assigned)->name ?? '-';
                })
                ->editColumn('created_at', function($ticket){
                    return $ticket->created_at->format('d-m-Y H:i');
                })
                ->editColumn('updated_at', function($ticket){
                    return $ticket->updated_at->format('d-m-Y H:i');
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getSingleTicketDept($id)
    {
        $ticket = Ticket::with(['requester', 'dept','attachmentsOpen','attachmentsClose'])->findOrFail($id);
        $isHead = Department::where('head_id', auth()->user()->id)->count();

        $response = [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'title' => $ticket->title,
            'description' => $ticket->description,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'created_at' => $ticket->created_at->format('d-m-Y H:i'),
            'requester_name' => optional($ticket->requester)->name,
            'department_name' => optional($ticket->dept)->name,
            'attachments_open' => $ticket->attachmentsOpen->map(function ($a) {
                return [
                    'file_path' => asset('/storage/' . $a->file_path),
                    'type' => $a->type,
                ];
            }),
            'attachments_close' => $ticket->attachmentsClose->map(function ($a) {
                return [
                    'file_path' => asset('/storage/' . $a->file_path),
                    'type' => $a->type,
                ];
            }),
        ];

        if ($isHead > 0) {
            $employees = User::where('department_id', auth()->user()->department_id)->get(['id', 'name']);
            $response['employees'] = $employees;
        }

            $teams = User::where('department_id', auth()->user()->department_id)->get(['id', 'name']);
            $response['teams'] = $teams;

        return response()->json($response);
    }



    private function generateNomorTiket($departmentName)
    {
        $lastTicket = Ticket::orderBy('id', 'desc')->first();
        $nextNumber = $lastTicket ? $lastTicket->id + 1 : 1;
        $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $bulan = now()->format('n');
        $bulanRomawi = [1=>'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$bulan];
        $tahun = now()->format('Y');

        return "TIK-{$formattedNumber}/{$departmentName}/{$bulanRomawi}/{$tahun}";
    }


}

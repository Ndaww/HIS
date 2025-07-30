<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Department;
use App\Models\TicketAttachment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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

            return response()->json([
                'message' => 'Tiket berhasil dikirim!',
                'ticket_number' => $ticket->ticket_number
            ], 200);

        } catch (\Throwable $e) {
            // Jika error terjadi
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan tiket.',
                'error' => $e->getMessage() // untuk debug, bisa dihilangkan saat production
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

    public function selesai(Request $request)
    {
        
        try {
            $ticket = Ticket::where('id',$request->id)->get()[0];
            Ticket::where('id',$request->id)->update([
                'status' => 'closed',
                'updated_at' => now()
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

                        <a href="javascript:void(0)" 
                            class="btn btn-sm btn-outline-primary  btn-delegasi" 
                            data-id="'.$ticket->id.'" 
                            data-bs-toggle="popover" 
                            data-bs-content="Delegasikan" 
                            title="Delegasikan">
                            <i class="ri-sm ri-send-plane-line"></i>
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

<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;

class TicketingV2Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tickets = Ticket::all();
        // dd($tickets);

        return view ('pages.ticketingv2.index',[
            'tickets' => $tickets
        ]);
    }

    public function indexMyDept()
    {
        $tickets = Ticket::all();
        $assigneds = User::where('department_id',auth()->user()->dept->id)
        ->where('id', '!=', auth()->user()->id)
        ->get();
        // dd(auth()->user()->dept->id,$assigneds);
        // dd($tickets);

        return view ('pages.ticketingv2.indexMyDept2',[
            'tickets' => $tickets,
            'assigneds' => $assigneds,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, Ticket $ticket)
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

    public function getTicketCounts()
    {
        // Ambil ID user
        $userId = auth()->id();

        // Hitung jumlah tiket untuk setiap status
        $counts = [
            'open' => Ticket::where('status', 'open')->where('requester_id', $userId)->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->where('requester_id', $userId)->count(),
            'pending' => Ticket::where('status', 'pending')->where('requester_id', $userId)->count(),
            'solved' => Ticket::where('status', 'solved')->where('requester_id', $userId)->count(),
            'closed' => Ticket::where('status', 'closed')->where('requester_id', $userId)->count(),
        ];

        return response()->json($counts);
    }

    public function getTicketDeptsCounts()
    {
        // Ambil id user
        $userId = auth()->id();

        // Ambil id dept user
        $deptId = auth()->user()->dept->id;

        // Hitung jumlah tiket untuk setiap status
        $counts = [
            'open' => Ticket::where('status', 'open')->where('department_id', $deptId)->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->where('assigned_employee_id', $userId)->count(),
            'pending' => Ticket::where('status', 'pending')->where('assigned_employee_id', $userId)->count(),
            'solved' => Ticket::where('status', 'solved')->where('assigned_employee_id', $userId)->count(),
            'closed' => Ticket::where('status', 'closed')->where('assigned_employee_id', $userId)->count(),
        ];

        return response()->json($counts);
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
            $assignedInput = $request->query('assigned_employee_id');

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
                    \Log::error('Gagal pilih status:', [$e->getMessage()]);
                }
            }

            if(!empty($assignedInput)){
                try {

                    $tickets = $tickets->where('assigned_employee_id',$assignedInput);
                } catch (\Exception $e) {
                    \Log::error('Gagal pilih assigner:', [$e->getMessage()]);
                }
            }


            return DataTables::of($tickets)
                ->addIndexColumn()
                ->addColumn('action', function ($ticket) use($head,$myTicket) {
                    $isOpen = $ticket->status === 'open' ? '' : 'disabled';
                    $isHead = $head > 0 && $ticket->status =='open' ? '' : 'disabled';
                    $isMyTicket = in_array($ticket->id, $myTicket) ? '' : 'disabled';
                    $isMyTicket2 = in_array($ticket->status, ['solved','closed'])? '' : 'disabled';
                    $solveClose = '';
                    // kalo ticket gue
                    if($isMyTicket > 0 ){
                        // kalo udah solve & close tidak bisa eskalasi
                        $solveClose = in_array($ticket->status, ['solved','closed'])? 'disabled' : '';
                    }

                    $button = '<button href="javascript:void(0)"
                            class="btn btn-sm btn-info btn-view me-1"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Lihat"
                            title="Lihat" >
                            <i class="ri-sm ri-eye-line"></i>
                        </button>';

                    if ($ticket->status == 'in_progress' && $ticket->assigned_employee_id == auth()->user()->id){
                        $button .= '<button href="javascript:void(0)"
                            class="btn btn-sm btn-danger btn-pending"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Pending"
                            title="Pending" '." $isMyTicket ".'>
                            <i class="ri-sm ri-compass-4-line"></i>
                        </button> <button href="javascript:void(0)"
                            class="btn btn-sm btn-success btn-solve"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Solved"
                            title="Solved" '." $isMyTicket ".'>
                            <i class="ri-sm ri-check-line"></i>
                        </button>';
                    }

                    if($ticket->status =='pending'){
                        $button .= '<button href="javascript:void(0)"
                            class="btn btn-sm btn-success btn-solve"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Solved"
                            title="Solved" '." $isMyTicket ".'>
                            <i class="ri-sm ri-check-line"></i>
                        </button>     <button href="javascript:void(0)"
                            class="btn btn-sm btn-warning btn-eskalasi"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Eskalasi"
                            title="Eskalasi" '." $isMyTicket $solveClose ".'>
                            <i class="ri-sm ri-exchange-2-line"></i>
                        </button>';
                    }

                    // kalo head dept
                    if ($head > 0){
                        $button .= '<button href="javascript:void(0)"
                            class="btn btn-sm btn-primary btn-delegasi"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Delegasikan"
                            title="Delegasikan" '."$isHead".' >
                            <i class="ri-sm ri-send-plane-line"></i>
                        </button>';
                    }

                        return $button;

                    //  return '
                    //     <button href="javascript:void(0)"
                    //         class="btn btn-sm btn-info btn-view"
                    //         data-id="'.$ticket->id.'"
                    //         data-bs-toggle="popover"
                    //         data-bs-content="Lihat"
                    //         title="Lihat" >
                    //         <i class="ri-sm ri-eye-line"></i>
                    //     </button>

                    //     <button href="javascript:void(0)"
                    //         class="btn btn-sm btn-primary btn-delegasi"
                    //         data-id="'.$ticket->id.'"
                    //         data-bs-toggle="popover"
                    //         data-bs-content="Delegasikan"
                    //         title="Delegasikan" '."$isHead".' >
                    //         <i class="ri-sm ri-send-plane-line"></i>
                    //     </button>

                    //     <button href="javascript:void(0)"
                    //         class="btn btn-sm btn-danger btn-pending"
                    //         data-id="'.$ticket->id.'"
                    //         data-bs-toggle="popover"
                    //         data-bs-content="Pending"
                    //         title="Pending" '." $isMyTicket ".'>
                    //         <i class="ri-sm ri-compass-4-line"></i>
                    //     </button>

                    //     <button href="javascript:void(0)"
                    //         class="btn btn-sm btn-success btn-solve"
                    //         data-id="'.$ticket->id.'"
                    //         data-bs-toggle="popover"
                    //         data-bs-content="Solved"
                    //         title="Solved" '." $isMyTicket ".'>
                    //         <i class="ri-sm ri-check-line"></i>
                    //     </button>

                        // <button href="javascript:void(0)"
                        //     class="btn btn-sm btn-warning btn-eskalasi"
                        //     data-id="'.$ticket->id.'"
                        //     data-bs-toggle="popover"
                        //     data-bs-content="Eskalasi"
                        //     title="Eskalasi" '." $isMyTicket $solveClose ".'>
                        //     <i class="ri-sm ri-exchange-2-line"></i>
                        // </button>
                    // ';
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


}

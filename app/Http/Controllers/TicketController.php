<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Department;
use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Support\Facades\Request;
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
            'requester_id' =>  1 ,//auth()->id(),
            'department_id' => $validated['department_id'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $filename = $file->store('attachments', 'public');

            // Simpan ke tabel ticket_attachments jika ada
            TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'file_path' => $filename
            ]);
        }
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

    public function getData(Request $request)
    {
        try {
            $tickets = Ticket::query();

            return DataTables::of($tickets)
                ->addIndexColumn()
                ->addColumn('action', function ($ticket) {
                    return '<a href="#" class="btn btn-sm btn-outline-info"><i class="ri-sm ri-eye-line"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-primary"><i class="ri-sm ri-send-plane-line"></i></a>
                    ';
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

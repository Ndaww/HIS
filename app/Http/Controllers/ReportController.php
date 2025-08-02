<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\MasterEquipmentType;
use App\Models\MasterRoom;
use App\Models\PreventiveTask;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function indexTicket()
    {
        $depts = Department::all();
        return view ('pages.reports.ticket',[
            'depts' => $depts
        ]);
    }

    public function getAllTicket(Request $request)
    {
        try {

            $tickets = Ticket::query();

            $startInput = $request->query('start_date');
            $endInput = $request->query('end_date');
            $deptInput = $request->query('department');
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

            if(!empty($deptInput)){
                try {
                    $tickets = $tickets->where('department_id',$deptInput);
                } catch (\Exception $e) {
                    \Log::error('Gagal parsing tanggal:', [$e->getMessage()]);
                }
            }


            return DataTables::of($tickets)
                ->addIndexColumn()
                ->addColumn('action', function ($ticket) {
                     return '
                        <button href="javascript:void(0)"
                            class="btn btn-sm btn-info btn-view"
                            data-id="'.$ticket->id.'"
                            data-bs-toggle="popover"
                            data-bs-content="Lihat"
                            title="Lihat" >
                            <i class="ri-sm ri-file-line"></i>
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

    public function getSingleReportTicket($id)
    {
        $ticket = Ticket::with(['requester', 'dept','attachmentsOpen','attachmentsClose'])->findOrFail($id);

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

        return response()->json($response);
    }

    public function indexPreventive()
    {
        $rooms = MasterRoom::all();
        $equipmentTypes = MasterEquipmentType::all();

        return view('pages.reports.preventive', [
            'rooms' => $rooms,
            'equipmentTypes' => $equipmentTypes,
        ]);

    }

    public function getAllPreventive(Request $request)
    {
        $query = PreventiveTask::with(['equipment.type', 'room', 'executor', 'details.preventiveType'])
            ->whereNotNull('performed_date');

        if ($request->filled('start_date')) {
            $query->whereDate('performed_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('performed_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('floor')) {
            $query->whereHas('room', fn($q) => $q->where('floor', $request->floor));
        }

        if ($request->filled('equipment_type_id')) {
            $query->whereHas('equipment', fn($q) =>
                $q->where('equipment_type_id', $request->equipment_type_id)
            );
        }

        return DataTables::of($query)
            ->addColumn('ruangan', fn($task) => $task->room->floor . ' - ' . $task->room->name)
            ->addColumn('alat', fn($task) => $task->equipment->name)
            ->addColumn('tindakan', function($task) {
                return '<ul>' . collect($task->details)->map(fn($d) =>
                    '<li>' . $d->preventiveType->equipmentPreventive->name .
                    ($d->note ? '<br><small class="text-muted">📝 ' . $d->note . '</small>' : '') .
                    '</li>'
                )->implode('') . '</ul>';
            })
            ->addColumn('tanggal', fn($task) => Carbon::parse($task->performed_date)->format('d M Y'))
            ->addColumn('teknisi', fn($task) => $task->executor?->name ?? '-')
            ->rawColumns(['tindakan'])
            ->make(true);
        }
}

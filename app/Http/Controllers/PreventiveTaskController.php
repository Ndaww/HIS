<?php

namespace App\Http\Controllers;

use App\Models\PreventiveTask;
use App\Http\Requests\StorePreventiveTaskRequest;
use App\Http\Requests\UpdatePreventiveTaskRequest;
use App\Models\EquipmentPreventiveType;
use App\Models\MasterEquipment;
use App\Models\MasterRoom;
use App\Models\PreventiveTaskDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PreventiveTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rooms = MasterRoom::where('status','preventive')->get();
        $equipments = MasterEquipment::all();
        return view('pages.preventive.create',[
            'rooms'=> $rooms,
            'equipments' => $equipments
        ]);
    }

    public function indexTask()
    {
       $tasks = PreventiveTask::with(['room', 'equipment'])
       ->where(function ($q) {
            $q->where('executor_id', auth()->id())
            ->orWhereNull('executor_id');
        })
       ->whereDate('start_date', '<=', now())
       ->whereDate('end_date', '>=', now())
       ->whereIn('status', ['pending', 'in_progress'])
       ->orderBy('start_date')
       ->get();

        return view('pages.preventive.index-task', [
            'tasks'=> $tasks
        ]);
    }

    public function createTask($id)
    {
        $task = PreventiveTask::with(['room', 'equipment', 'details.preventiveType'])
        ->where('id', $id)
        ->where(function ($q) {
            $q->where('executor_id', auth()->id())
              ->orWhereNull('executor_id');
        })
        ->firstOrFail();

        return view('pages.preventive.form-task', [
            'task' => $task
        ]);

    }

    public function history()
    {
        return view('pages.preventive.history.index');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePreventiveTaskRequest $request)
    {
        // dd($request);
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'room_ids'   => 'required|array|min:1',
            'equipment_ids' => 'required|array|min:1',
        ]);

        try {
            foreach ($request->equipment_ids as $equipmentId) {
                $equipment = MasterEquipment::findOrFail($equipmentId);

                // Buat task preventif utama
                $task = PreventiveTask::create([
                    'equipment_id' => $equipment->id,
                    'room_id' => $equipment->room_id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'status' => 'pending',
                ]);

                // Ambil checklist tindakan berdasarkan jenis equipment
                $preventiveTypeIds = EquipmentPreventiveType::where('equipment_type_id', $equipment->equipment_type_id)
                    ->pluck('preventive_type_id');

                foreach ($preventiveTypeIds as $ptypeId) {
                    PreventiveTaskDetail::create([
                        'task_id' => $task->id,
                        'preventive_type_id' => $ptypeId,
                        'status' => 'pending',
                    ]);
                }
            }

            return redirect()->route('preventive.create')->with('success', 'Jadwal berhasil dibuat!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat jadwal: ' . $e->getMessage());
        }
    }

    public function storeResult(Request $request, $id)
    {
        $task = PreventiveTask::with('details')->findOrFail($id);

        // cek task yang belum diassign
        if ($task->executor_id && $task->executor_id !== auth()->id()) {
            return back()->with('error', 'Tugas ini bukan milik Anda.');
        }

        // Assign user jika belum ada
        if (!$task->executor_id) {
            $task->executor_id = auth()->id();
        }

        // Update setiap detail
        foreach ($task->details as $detail) {
            $status = $request->input("actions.{$detail->id}.status") === 'done' ? 'done' : 'pending';
            $note = $request->input("actions.{$detail->id}.note");
            $detail->update([
                'status' => $status,
                'note' => $note,
            ]);
        }

        // Tandai task selesai jika semua detail done
        if ($task->details->every(fn($d) => $d->status === 'done')) {
            $task->status = 'done';
            $task->performed_date = now();
            MasterRoom::where('id',$task->room_id)->update(['status'=>'done preventive','preventive_done_at'=>now()]);
        } else {
            $task->status = 'in_progress';
        }

        $task->save();

        return redirect('/preventive/task')->with('success', 'Tugas berhasil disubmit.');
    }


    /**
     * Display the specified resource.
     */
    public function show(PreventiveTask $preventiveTask)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PreventiveTask $preventiveTask)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePreventiveTaskRequest $request, PreventiveTask $preventiveTask)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PreventiveTask $preventiveTask)
    {
        //
    }

    public function getEquipmentByRooms(Request $request)
    {
        $equipment = MasterEquipment::whereIn('room_id', $request->room_ids)->get(['id', 'name', 'serial_number']);
        return response()->json($equipment);
    }

    public function historyData(Request $request)
    {
        $query = PreventiveTask::with(['room', 'equipment', 'executor', 'details.preventiveType'])
            ->where('status', 'done');

        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('performed_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('performed_date', '<=', $request->end_date);
        }

        return DataTables::of($query)
            ->addColumn('ruangan', fn($task) => $task->room->floor . ' - ' . $task->room->name)
            ->addColumn('alat', fn($task) => $task->equipment->name . '<br><small class="text-muted">' . $task->equipment->serial_number . '</small>')
            ->addColumn('tindakan', function($task) {
                return '<ul>' . collect($task->details)->map(fn($d) =>
                    '<li>' . $d->preventiveType->equipmentPreventive->name .
                    ($d->note ? '<br><small class="text-muted">📝 ' . $d->note . '</small>' : '') .
                    '</li>'
                )->implode('') . '</ul>';
            })
            ->addColumn('tanggal', fn($task) => Carbon::parse($task->performed_date)->format('d M Y'))
            ->addColumn('teknisi', fn($task) => $task->executor?->name ?? '-')
            ->rawColumns(['alat', 'tindakan'])
            ->make(true);
    }


}

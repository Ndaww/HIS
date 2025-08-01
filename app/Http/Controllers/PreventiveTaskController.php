<?php

namespace App\Http\Controllers;

use App\Models\PreventiveTask;
use App\Http\Requests\StorePreventiveTaskRequest;
use App\Http\Requests\UpdatePreventiveTaskRequest;
use App\Models\EquipmentPreventiveType;
use App\Models\MasterEquipment;
use App\Models\MasterRoom;
use App\Models\PreventiveTaskDetail;
use Illuminate\Http\Request;

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
        $rooms = MasterRoom::all();
        $equipments = MasterEquipment::all();   
        return view('pages.preventive.create',[
            'rooms'=> $rooms,
            'equipments' => $equipments
        ]);
    }

    public function createPreventive()
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

        return view('pages.preventive.create-preventive', compact('tasks'));
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

}

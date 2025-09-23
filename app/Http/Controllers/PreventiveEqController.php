<?php

namespace App\Http\Controllers;

use App\Models\MasterEquipment;
use App\Models\MasterEquipmentType;
use App\Models\MasterPreventive;
use App\Models\MasterRoom;
use App\Models\PreventiveTask;
use App\Models\PreventiveTaskDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreventiveEqController extends Controller
{
    public function create()
    {
        $rooms = MasterRoom::where('status','preventive')->get();
        $equipments = MasterEquipment::all();
        return view('pages.preventive.create',[
            'rooms'=> $rooms,
            'equipments' => $equipments
        ]);
    }

    public function createTask($id)
    {
        $eqType = MasterEquipmentType::with('equipments')
        ->where('id', $id)
        ->firstOrFail();

        $equipments = MasterEquipment::with('room')->where('equipment_type_id',$id)->get();
        $rooms = MasterEquipment::select('room_id')->where('equipment_type_id',$id)->distinct()->get();
        // dd($rooms->count());
        return view('pages.preventive.equipment.form-task', [
            'eqType' => $eqType,
            'equipments' => $equipments,
            'rooms' => $rooms
        ]);

    }

    public function getEquipments($id, $roomId)
    {
        $equipments = MasterEquipment::with('room')
            ->where('equipment_type_id', $id)
            ->where('room_id', $roomId)
            ->get();

        return response()->json($equipments);
    }

    public function getPreventiveTasks($eqTypeId)
    {
        $preventiveTasks = MasterPreventive::join(
            'equipment_preventive_types',
            'master_preventives.id',
            '=',
            'equipment_preventive_types.preventive_type_id'
        )
        ->where('equipment_preventive_types.equipment_type_id', $eqTypeId)
        ->select('master_preventives.id', 'master_preventives.name')
        ->get();

        return response()->json($preventiveTasks);
    }

    public function checkPreventiveStatus($equipmentId)
    {
        $lastTask = PreventiveTask::where('equipment_id', $equipmentId)
            ->whereBetween('created_at', [
                Carbon::now()->subMonths(2)->startOfDay(),
                Carbon::now()->endOfDay()
            ])
            ->latest('performed_date')
            ->first();

        if ($lastTask && $lastTask->status === 'done') {
            return response()->json([
                'status' => 'done',
                'message' => 'Terakhir dilakukan preventif pada ' . Carbon::parse($lastTask->performed_date)->isoFormat('LL'),
            ]);
        } else if ($lastTask && ($lastTask->status == 'in_progress' || $lastTask->status == 'pending') ) {
            return response()->json([
                'status' => 'pp',
                'message' => 'Ada Tindakan yang belum selesai untuk equipment ini, silahkan cek di halaman "Tugas Saya" ',
            ]);
        } else {
            return response()->json([
                'status' => 'new',
                'message' => 'Equipment ini tidak ada pada jadwal preventive, ingin lanjutkan tindakan preventive?',
            ]);
        }
    }

    public function storeTask(Request $request)
    {
        // dd($request);

        try {
            DB::beginTransaction();

            $task = PreventiveTask::create([
                'equipment_id' => $request->equipment_id,
                'room_id' => $request->room_id,
                'start_date' => Carbon::today(),
                'end_date' => Carbon::today(),
                'status' => 'in_progress',
                'performed_date' => Carbon::now(),
                'executor_id' => auth()->user()->id,
            ]);

            $preventiveTypeIds = $request->input('preventive_task_ids', []);
            $taskNotes = $request->input('task_notes', []);

            foreach ($preventiveTypeIds as $preventiveId) {
                PreventiveTaskDetail::create([
                    'task_id' => $task->id,
                    'preventive_type_id' => $preventiveId,
                    'status' => 'done',
                    'note' => $taskNotes[$preventiveId] ?? null,
                ]);
            }

            // Perbarui status task utama menjadi 'done' jika semua detailnya done
            $task->status = 'done';
            $task->save();

            DB::commit();

            // Redirect dengan pesan sukses
            return redirect('/preventive/task')->with('success', 'Tindakan preventif berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();

            // Redirect dengan pesan error
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data. ' . $e->getMessage());
        }
    }


}

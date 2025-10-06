<?php

namespace App\Http\Controllers;

use App\Models\Masterpmtask;
use App\Http\Requests\StoreMasterpmtaskRequest;
use App\Http\Requests\UpdateMasterpmtaskRequest;
use App\Models\MasterEquipmentType;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class MasterpmtaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil semua tugas dan preload (eager load) relasi equipmentType
        $tasks = MasterPmTask::with('equipmentType')
                            ->orderBy('equipment_type_id')
                            ->get();
        
        $equipmentTypes = MasterEquipmentType::all();
        

        return view('pages.master.tasks.index', compact('tasks','equipmentTypes'));
    }

    public function data()
    {
        // Memuat tugas beserta relasi equipmentType
        $tasks = MasterPmTask::with('equipmentType')->select('*');

        return DataTables::of($tasks)
            ->addIndexColumn()
            
            // Kolom Tipe Equipment
            ->addColumn('equipment_name', fn($row) => $row->equipmentType->name ?? '-')
            
            // Kolom Kategori I-L-C-T
            ->addColumn('category', function ($row) {
            $category = $row->task_category;
            $colorClass = match ($category) {
                'I' => 'bg-info',    // Inspection (Biru Muda)
                'L' => 'bg-success', // Level/Lubrication (Hijau)
                'C' => 'bg-warning', // Cleaning (Kuning/Orange)
                'T' => 'bg-danger',  // Tightening/Condition (Merah)
                default => 'bg-secondary', // Default jika kategori tidak terdefinisi
            };

            return "<td class='text-center'><span class='badge {$colorClass} text-white'>{$category}</span></td>";
        })
            
            // Kolom Aksi (CRUD)
            ->addColumn('action', function ($row) {
                $editUrl = route('pm_tasks.edit', $row->id);
                return '
                    <a href="' . $editUrl . '" class="btn btn-sm btn-warning">Edit</a>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="' . $row->id . '">Delete</button>
                ';
            })
            
            ->rawColumns(['category', 'action'])
            ->make(true);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil data equipment types untuk dropdown di form
        $equipmentTypes = MasterEquipmentType::all(); 
        
        // Kembalikan view form
        return view('pages.master.tasks.create', compact('equipmentTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMasterpmtaskRequest $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'equipment_type_id' => 'required|exists:master_equipment_types,id',
            'task_name' => 'required|string|max:255',
            'task_category' => 'required|string|in:I,L,C,T',
            'anomaly_threshold' => 'required|string',
            'frequency_type' => 'required|string',
            'responsible_role' => 'required|string',
        ]);

        if ($validator->fails()) {
            // Mengembalikan error validasi ke AJAX
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // 2. Simpan Data
            $task = MasterPmTask::create($request->all());

            // 3. Respon Sukses
            return redirect()->route('pm_tasks.index')->with([ // <-- INI HARUS ADA!
                'success_title' => 'Berhasil!',
                'success_message' => 'Tugas baru berhasil ditambahkan.'
            ]);

        } catch (\Exception $e) {
            // 4. Respon Error Server
            return redirect()->back()->withInput()->with([
                'error_title' => 'Gagal!',
                'error_message' => 'Gagal menyimpan tugas. Error: ' . $e->getMessage()
            ]);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Masterpmtask $masterpmtask)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Masterpmtask $task)
    {
        // Ambil semua tipe equipment untuk dropdown
        $equipmentTypes = MasterEquipmentType::all();
        
        // Kembalikan view edit dengan data tugas yang akan diedit
        return view('pages.master.tasks.edit', compact('task', 'equipmentTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMasterpmtaskRequest $request, Masterpmtask $task)
    {
        // 1. Validasi Input (Sama dengan store, tapi bisa diabaikan untuk ID sendiri)
        $validator = Validator::make($request->all(), [
            'equipment_type_id' => 'required|exists:master_equipment_types,id',
            'task_name' => 'required|string|max:255',
            'task_category' => 'required|string|in:I,L,C,T',
            'anomaly_threshold' => 'required|string',
            'frequency_type' => 'required|string', 
            'responsible_role' => 'required|string',
        ]);

        if ($validator->fails()) {
            // Jika validasi gagal, kembalikan ke halaman form dengan input dan error
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // 2. Perbarui Data
            $task->update($request->all());

            // 3. Respon Sukses (Menggunakan SweetAlert melalui session)
            return redirect()->route('pm_tasks.index')->with([
                'success_title' => 'Berhasil!',
                'success_message' => 'Tugas "' . $task->task_name . '" berhasil diperbarui.'
            ]);

        } catch (\Exception $e) {
            // 4. Respon Error Server
            return redirect()->back()->withInput()->with([
                'error_title' => 'Gagal!',
                'error_message' => 'Gagal memperbarui tugas. Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Masterpmtask $task)
    {
        try {
            $taskName = $task->task_name;
            $task->delete();

            // Respon Sukses ke AJAX
            return response()->json([
                'success' => true,
                'message' => 'Tugas "' . $taskName . '" berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            // Respon Error Server
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tugas. Error: ' . $e->getMessage()
            ], 500);
        }

    }
}

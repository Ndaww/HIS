<?php

namespace App\Http\Controllers;

use App\Models\PmShiftTask;
use App\Http\Requests\StorePmShiftTaskRequest;
use App\Http\Requests\UpdatePmShiftTaskRequest;
use App\Models\Masterpmtask;
use App\Models\PmShiftSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PmShiftTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Mendapatkan bulan, tahun, dan shift dari request, default ke periode saat ini
        $targetMonth = $request->input('month', now()->month);
        $targetYear = $request->input('year', now()->year);
        $targetShift = $request->input('shift', 'Shift 1'); // Default Shift 1 atau shift user yang login

        $shifts = ['Shift 1', 'Shift 2', 'Shift 3'];
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        // --- LOGIKA PENGAMBILAN DATA ---

        // 1. Ambil ID tugas yang dijadwalkan dari PmShiftSchedules
        $scheduledTasksIds = PmShiftSchedule::where('month', $targetMonth)
            ->where('year', $targetYear)
            ->where('shift_name', $targetShift)
            ->pluck('master_pm_task_id');

        // 2. Ambil detail tugas dan LEFT JOIN dengan tabel REALISASI (pm_shift_tasks)
        $tasks = MasterPmTask::select('masterpmtasks.*', 
                                      'r.status as realization_status', // Ubah alias status
                                      'r.completion_date',
                                      'r.performed_by_user_id')
            ->whereIn('masterpmtasks.id', $scheduledTasksIds)
            
            // LEFT JOIN dengan tabel pm_shift_tasks (alias: r)
            ->leftJoin('pm_shift_tasks as r', function($join) use ($targetMonth, $targetYear) { // Ubah tabel join
                $join->on('r.master_pm_task_id', '=', 'masterpmtasks.id')
                     ->where('r.month', $targetMonth)
                     ->where('r.year', $targetYear);
            })
            ->with('equipmentType') 
            ->orderBy('equipment_type_id')
            ->get();

            // dd($tasks);
        
        // Pilihan tahun yang tersedia (misalnya, tahun ini hingga 2 tahun ke depan)
        $availableYears = range(now()->year, now()->year + 2);

        return view('pages.preventive-shift-v2.v2.create', compact(
            'tasks', 
            'targetMonth', 
            'targetYear', 
            'targetShift',
            'shifts',
            'months',
            'availableYears'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // 1. Validasi parameter wajib
        $taskId = $request->input('task_id');
        $month = $request->input('month');
        $year = $request->input('year');
        $shift = $request->input('shift');
        // dd($request->input());

        if (!$taskId || !$month || !$year || !$shift) {
            return redirect()->route('pm_shift.index')->withErrors('Parameter tugas tidak lengkap.');
        }

        // 2. Ambil detail tugas master
        $task = MasterPmTask::with('equipmentType')->find($taskId);

        if (!$task) {
            return redirect()->route('pm_shift.index')->withErrors('Tugas tidak ditemukan.');
        }

        // 3. Cek apakah sudah ada realisasi untuk periode ini
        $realization = PmShiftTask::where('master_pm_task_id', $taskId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
            
        // Jika sudah Done, arahkan kembali (meskipun tombol sudah non-aktif)
        if ($realization && $realization->status == 'Done') {
             return redirect()->route('pm_shift.index')->with('error_title', 'Gagal')->with('error_message', 'Tugas sudah selesai dan terekam.');
        }

        return view('pages.preventive-shift-v2.v2.form', compact('task', 'month', 'year', 'shift', 'realization'));
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePmShiftTaskRequest $request)
    {
        $request->validate([
            'master_pm_task_id' => 'required|exists:masterpmtasks,id',
            'assigned_shift' => 'required|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        // 1. Cek duplikasi atau data lama
        $realization = PmShiftTask::where('master_pm_task_id', $request->master_pm_task_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();
        
        try {
            // 2. Simpan atau Perbarui Realisasi
            if (!$realization) {
                // Buat entri baru (Status: Done)
                PmShiftTask::create([
                    'master_pm_task_id' => $request->master_pm_task_id,
                    'month' => $request->month,
                    'year' => $request->year,
                    'assigned_shift' => $request->assigned_shift,
                    'performed_by_user_id' => auth()->id(), // Asumsi user sudah login
                    'status' => 'Done',
                    'completion_date' => Carbon::now(),
                    'notes' => $request->notes,
                ]);
            } else {
                // Update entri lama (jika status sebelumnya bukan Done)
                $realization->update([
                    'performed_by_user_id' => auth()->id(),
                    'status' => 'Done',
                    'completion_date' => Carbon::now(),
                    'notes' => $request->notes,
                ]);
            }
            
            return redirect()->route('pm_shift.index')->with('success_title', 'Berhasil!')->with('success_message', 'Tugas telah dicatat selesai.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error_title', 'Gagal Menyimpan')->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PmShiftTask $pmShiftTask)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PmShiftTask $pmShiftTask, Request $request)
    {
        // 1. Ambil parameter (Sama seperti create)
        $taskId = $request->input('task_id');
        $month = $request->input('month');
        $year = $request->input('year');
        $shift = $request->input('shift');
        
        
        if (!$taskId || !$month || !$year || !$shift) {
            return redirect()->route('pm_shift.index')->withErrors('Parameter tugas tidak lengkap.');
        }

        // 2. Ambil detail tugas master
        $task = MasterPmTask::with('equipmentType')->find($taskId);

        // 3. Wajib ambil data realisasi yang sudah ada
        $realization = PmShiftTask::where('master_pm_task_id', $taskId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
            
        // Validasi: Harus ada data realisasi
        if (!$realization) {
            return redirect()->route('pm_shift.index')->with('error_title', 'Gagal')->with('error_message', 'Realisasi tugas belum tercatat.');
        }
        
        // Asumsi hanya bisa mengedit yang statusnya 'Done'
        if ($realization->status !== 'Done') {
            return redirect()->route('pm_shift.index')->with('error_title', 'Gagal')->with('error_message', 'Tugas belum selesai, gunakan tombol "Mulai Tugas".');
        }
        // dengan passing data $realization
        return view('pages.preventive-shift-v2.v2.form', compact('task', 'month', 'year', 'shift', 'realization'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePmShiftTaskRequest $request, PmShiftTask $pmShiftTask)
    {
        $request->validate([
            'master_pm_task_id' => 'required|exists:masterpmtasks,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'notes' => 'nullable|string|max:1000',
            'completion_date' => 'required|date', // Tambah validasi tanggal
        ]);
        
        // 1. Cari data realisasi yang akan diupdate
        $realization = PmShiftTask::where('master_pm_task_id', $request->master_pm_task_id)
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if (!$realization) {
             return redirect()->back()->withInput()->with('error_title', 'Gagal Update')->with('error_message', 'Data realisasi tidak ditemukan untuk diubah.');
        }
        
        try {
            // 2. Lakukan Update
            $realization->update([
                'performed_by_user_id' => auth()->user()->id, // Mungkin user yang mengedit berbeda
                'completion_date' => $request->completion_date, // Ambil dari form edit
                'notes' => $request->notes,
                // Status tidak perlu diubah karena pasti 'Done'
            ]);
            
            return redirect()->route('pm_shift.index')->with('success_title', 'Berhasil!')->with('success_message', 'Realisasi tugas berhasil diubah.');

        } catch (\Exception $e) {
            \Log::error('Error updating PM task realization: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error_title', 'Gagal Update')->with('error_message', 'Terjadi kesalahan sistem saat menyimpan perubahan.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PmShiftTask $pmShiftTask)
    {
        //
    }

    public function history(Request $request)
    {
        // Mendapatkan filter dari request, default ke tahun saat ini
        $targetYear = $request->input('year', now()->year);
        $targetShift = $request->input('shift', 'Shift 1'); 

        $shifts = ['Shift 1', 'Shift 2', 'Shift 3'];
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $availableYears = range(now()->year - 2, now()->year + 1); // Rentang 2 tahun ke belakang dan 1 tahun ke depan

        // Mengambil data realisasi tugas yang sudah Done
        $historyTasks = PmShiftTask::where('year', $targetYear)
            ->where('assigned_shift', $targetShift)
            ->where('status', 'Done')
            ->with(['masterPmTask.equipmentType', 'performer']) // Load detail tugas dan user pelaksana
            ->orderBy('completion_date', 'desc')
            ->get()
            ->groupBy(function($item) {
                // Grouping berdasarkan bulan untuk tampilan yang rapi
                return $item->month;
            });
            
        return view('pages.preventive-shift-v2.v2.history', compact(
            'historyTasks',
            'targetYear',
            'targetShift',
            'shifts',
            'months',
            'availableYears'
        ));
    }

    public function historyData(Request $request)
    {
        // 1. Ambil filter dari Request 
        $targetShift = $request->input('shift', 'Shift 1');
        $startDate = $request->input('start_date'); // NEW
        $endDate = $request->input('end_date');     // NEW

        // 2. Ambil Query Builder untuk Realisasi Tugas
        $query = PmShiftTask::select([
                'pm_shift_tasks.id',
                'pm_shift_tasks.completion_date',
                'pm_shift_tasks.assigned_shift',
                'masterpmtasks.task_name',
                'masterpmtasks.task_category',
                'users.name as performer_name',
                'master_equipment_types.name as equipment_type_name',
                'pm_shift_tasks.notes'
            ])
            ->join('masterpmtasks', 'pm_shift_tasks.master_pm_task_id', '=', 'masterpmtasks.id')
            ->join('master_equipment_types', 'masterpmtasks.equipment_type_id', '=', 'master_equipment_types.id') 
            ->join('users', 'pm_shift_tasks.performed_by_user_id', '=', 'users.id') 
            ->where('pm_shift_tasks.assigned_shift', $targetShift)
            ->where('pm_shift_tasks.status', 'Done')
            ->orderBy('pm_shift_tasks.completion_date', 'desc');

        if ($startDate && $endDate) {
            $endOfDay = Carbon::parse($endDate)->endOfDay();
            
            $query->whereBetween('pm_shift_tasks.completion_date', [$startDate, $endOfDay]);
        }


        return DataTables::of($query)
            ->editColumn('completion_date', function($task) {
                return Carbon::parse($task->completion_date)->format('d M Y H:i');
            })
            ->editColumn('task_category', function($task) {
                $badgeClass = match ($task->task_category) {
                    'I' => 'bg-info', 
                    'L' => 'bg-success', 
                    'C' => 'bg-warning', 
                    'T' => 'bg-danger', 
                    default => 'bg-secondary',
                };
                return '<span class="badge ' . $badgeClass . ' text-white">' . $task->task_category . '</span>';
            })
            ->rawColumns(['task_category']) 
            ->make(true);
    }

    public function dashboard(Request $request)
    {
        // 1. Ambil Bulan dan Tahun dari request atau default
        $currentMonth = $request->input('month', Carbon::now()->month);
        $currentYear = $request->input('year', Carbon::now()->year);

        $nama_bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // =================================================================
        // 2. Query Dasar yang sudah DIFILTER dengan ALIAS
        // =================================================================
        
        // Base Query untuk Jadwal PM (pm_shift_schedules)
        $scheduledQuery = DB::table('pm_shift_schedules', 's') // Tambahkan alias 's' di sini
                            // PERBAIKAN: Gunakan alias untuk kolom yang ambigu
                            ->where('s.month', $currentMonth)
                            ->where('s.year', $currentYear);
                            
        $totalScheduled = $scheduledQuery->count();

        // Total Tugas Selesai (pm_shift_tasks)
        $totalCompleted = DB::table('pm_shift_tasks')
                            ->where('month', $currentMonth) // Di sini tidak ambigu karena hanya satu tabel
                            ->where('year', $currentYear)
                            ->where('status', 'Done')
                            ->count();
        
        // Gabungan Data (LEFT JOIN) untuk Analisis mendalam
        $combinedData = $scheduledQuery
            ->select('s.master_pm_task_id', 's.shift_name', 'm.task_name',
                     't.id as task_id', 't.status', 't.performed_by_user_id')
            ->leftJoin('pm_shift_tasks as t', function($join) {
                // Join berdasarkan ID Tugas.
                $join->on('s.master_pm_task_id', '=', 't.master_pm_task_id');
            })
            ->leftJoin('masterpmtasks as m', function($join) {
                // Join berdasarkan ID Tugas.
                $join->on('s.master_pm_task_id', '=', 'm.id');
            })
            // Filter bulan dan tahun sudah diaplikasikan di $scheduledQuery.
            ->get();
        
        // =================================================================
        // 3. Perhitungan Metrik Global dan Equipment/Shift
        // =================================================================
        
        $overallPercentage = ($totalScheduled > 0) ? round(($totalCompleted / $totalScheduled) * 100) : 0;
        $globalSummary = [
            'totalScheduled' => $totalScheduled,
            'totalCompleted' => $totalCompleted,
            'totalTarget' => $totalScheduled, 
            'overallPercentage' => $overallPercentage,
        ];
        
        // Perhitungan Metrik Equipment (grouped by master_pm_task_id)
        $dashboardData = $combinedData->groupBy('master_pm_task_id')->map(function ($tasks, $taskId) {
            $totalScheduled = $tasks->count();
            $completedCount = $tasks->filter(fn($t) => $t->status === 'Done')->count();
            $percentage = ($totalScheduled > 0) ? round(($completedCount / $totalScheduled) * 100) : 0;
            $equipmentType = 'Task ID: ' . $taskId . ' (Shift: ' . ($tasks->first()->shift_name ?? 'N/A') . ')'; 
            $taskName = $tasks->first()->task_name; 
            
            return (object) [
                'equipment_type' => $equipmentType,
                'target_count' => 1, 
                'total_scheduled' => $totalScheduled,
                'completed_count' => $completedCount,
                'percentage' => $percentage,
                'task_name' => $taskName
            ];
        })->values();

        // Perhitungan Metrik Beban Kerja Shift (Spesialis)
        $specialistRealization = $combinedData->groupBy('shift_name')->map(function ($tasks, $shiftName) {
            $totalAssigned = $tasks->count();
            $totalCompleted = $tasks->filter(fn($t) => $t->status === 'Done')->count();
            $percentage = ($totalAssigned > 0) ? round(($totalCompleted / $totalAssigned) * 100) : 0;
            
            return (object) [
                'specialist_name' => $shiftName,
                'total_assigned' => $totalAssigned,
                'total_completed' => $totalCompleted,
                'percentage' => $percentage,
            ];
        })->values();

        // =================================================================
        // 4. Perhitungan Metrik untuk Kinerja Per User
        // =================================================================
        
        // Ambil semua tugas yang *sudah* selesai di periode ini
        $tasksCompletedByUser = DB::table('pm_shift_tasks')
                                    ->where('month', $currentMonth)
                                    ->where('year', $currentYear)
                                    ->where('status', 'Done')
                                    ->whereNotNull('performed_by_user_id')
                                    ->get();

        $userPerformance = $tasksCompletedByUser->groupBy('performed_by_user_id')->map(function ($tasks, $userId) use ($currentMonth, $currentYear) {
            $totalCompleted = $tasks->count();
            $assignedShift = $tasks->first()->assigned_shift ?? 'N/A';
            
            // Query untuk mendapatkan total beban kerja shift user
            $totalAssignedToShift = DB::table('pm_shift_schedules')
                ->where('month', $currentMonth)
                ->where('year', $currentYear)
                ->where('shift_name', $assignedShift)
                ->count();
            
            $userName = 'User ID: ' . $userId; 

            $percentage = ($totalAssignedToShift > 0) ? round(($totalCompleted / $totalAssignedToShift) * 100) : 0;

            return (object) [
                'user_id' => $userId,
                'user_name' => $userName,
                'assigned_shift' => $assignedShift,
                'total_assigned_to_shift' => $totalAssignedToShift,
                'total_completed' => $totalCompleted,
                'percentage' => $percentage,
            ];
        })->values(); 

        // 5. Mengirim data ke View
        return view('pages.preventive-shift-v2.v2.dashboard', compact(
            'nama_bulan', 'currentMonth', 'currentYear',
            'globalSummary', 'dashboardData', 'specialistRealization',
            'userPerformance'
        ));
    }


}

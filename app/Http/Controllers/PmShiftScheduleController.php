<?php

namespace App\Http\Controllers;

use App\Models\PmShiftSchedule;
use App\Http\Requests\StorePmShiftScheduleRequest;
use App\Http\Requests\UpdatePmShiftScheduleRequest;
use App\Models\Masterpmtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PmShiftScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Tentukan periode target: ambil dari request atau default ke bulan/tahun saat ini
        $targetMonth = $request->input('month', date('m')); 
        $targetYear = $request->input('year', now()->year);
        
        // 1. Ambil semua Master Tasks
        $tasks = MasterPmTask::with('equipmentType')
                            ->orderBy('equipment_type_id')
                            ->get();

        // 2. Ambil jadwal yang sudah tersimpan untuk periode TARGET
        $currentSchedule = PmShiftSchedule::where('month', $targetMonth)
                                        ->where('year', $targetYear)
                                        ->pluck('shift_name', 'master_pm_task_id')
                                        ->toArray();
        
        $shifts = ['Shift 1', 'Shift 2', 'Shift 3'];
        
        // Data untuk dropdown di View
        $availableYears = range(now()->year, now()->year + 3); // Misal, tahun ini sampai 3 tahun ke depan
        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', 
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', 
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        return view('pages.preventive-shift-v2.v2.index', compact('tasks', 'currentSchedule', 'shifts', 'targetMonth', 'targetYear', 'availableYears', 'months'));
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
    public function store(StorePmShiftScheduleRequest $request)
    {
        $targetMonth = $request->schedule_month; 
        $targetYear = $request->schedule_year;
        
        $assignments = $request->input('assignment', []); 

        DB::beginTransaction();

        try {
            // 1. Hapus SEMUA jadwal lama HANYA untuk bulan dan tahun TARGET
            PmShiftSchedule::where('month', $targetMonth)
                        ->where('year', $targetYear)
                        ->delete(); 
            
            $insertData = [];
            foreach ($assignments as $taskId => $shiftName) {
                if ($shiftName) {
                    $insertData[] = [
                        'master_pm_task_id' => $taskId,
                        'shift_name' => $shiftName,
                        'month' => $targetMonth, 
                        'year' => $targetYear,  
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // 2. Sisipkan data baru
            if (!empty($insertData)) {
                // dd($insertData); 
                
                PmShiftSchedule::insert($insertData);
            }
            
            DB::commit();

            return redirect()->route('pm_schedule.index', ['month' => $targetMonth, 'year' => $targetYear])->with([
                'success_title' => 'Berhasil!',
                'success_message' => "Pembagian tugas untuk bulan {$targetMonth} tahun {$targetYear} berhasil disimpan."
            ]);


        } catch (\Exception $e) {
            DB::rollBack();
            if (config('app.debug')) {
                dd($e->getMessage(), $e->getLine(), $e->getFile());
            }
            
            return redirect()->back()->withInput()->with([
                'error_title' => 'Gagal!',
                'error_message' => 'Gagal menyimpan jadwal. Detail: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(PmShiftSchedule $pmShiftSchedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PmShiftSchedule $pmShiftSchedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePmShiftScheduleRequest $request, PmShiftSchedule $pmShiftSchedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PmShiftSchedule $pmShiftSchedule)
    {
        //
    }
}

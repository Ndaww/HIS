<?php

namespace App\Http\Controllers;

use App\Models\PmCheckRound;
use App\Http\Requests\StorePmCheckRoundRequest;
use App\Http\Requests\UpdatePmCheckRoundRequest;
use App\Models\MasterEquipment;
use App\Models\Masterpmtask;
use App\Models\PmCheckResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PmCheckRoundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.preventive-shift-v2.index');
    }

    public function data()
    {
        // Ambil data Ronde dan eager-load relasi teknisi (user)
        $rounds = PmCheckRound::with('technician')->orderBy('start_time', 'desc');

        return DataTables::of($rounds)
            ->addIndexColumn()
            ->addColumn('technician_name', fn($round) => $round->technician->name ?? '-')
            ->editColumn('start_time', fn($round) => $round->start_time ? date('d/m H:i', strtotime($round->start_time)) : '-')
            ->editColumn('completion_time', fn($round) => $round->completion_time ? date('d/m H:i', strtotime($round->completion_time)) : '-')
            
            // Kolom Status (Badge)
            ->addColumn('round_status', function ($round) {
                $status = $round->round_status;
                $class = match ($status) {
                    'In Progress' => 'bg-warning text-dark',
                    'Completed' => 'bg-success',
                    default => 'bg-secondary',
                };
                return "<span class='badge {$class}'>{$status}</span>";
            })
            
            // Kolom Aksi
            ->addColumn('action', function ($round) {
                if ($round->round_status == 'In Progress') {
                    // Jika sedang berlangsung, tombol utama adalah Lanjutkan Eksekusi
                    return '<a href="' . route('pm_rounds.execute', $round->id) . '" class="btn btn-sm btn-info">Lanjutkan</a>';
                }
                
                // Jika sudah selesai, tombol utama adalah Lihat Detail
                return '<a href="' . route('pm_rounds.show', $round->id) . '" class="btn btn-sm btn-primary">Detail</a>';
            })
            
            ->rawColumns(['round_status', 'action'])
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $equipments = MasterEquipment::all(); 
        $technicians = User::where('department_id', 4)->get();
        $currentShift = $this->determineShift(); 

        return view('pages.preventive-shift-v2.create', compact('equipments', 'technicians', 'currentShift'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePmCheckRoundRequest $request)
    {
        $request->validate([
            'technician_id' => 'required|exists:users,id',
            'shift_name' => 'required|string',
        ]);

        try {
            $round = PmCheckRound::create([
                'technician_id' => $request->technician_id,
                'shift_name' => $request->shift_name,
                'start_time' => now(), 
                'round_status' => 'In Progress',
            ]);

            return redirect()->route('pm_rounds.execute', $round->id)->with([
                'success_title' => 'Ronde Dimulai!',
                'success_message' => 'Ronde shift ' . $round->shift_name . ' berhasil dimulai. Silakan isi hasil cek.'
            ]);

        } catch (\Exception $e) {
             return redirect()->back()->withInput()->with([
                'error_title' => 'Gagal!',
                'error_message' => 'Gagal memulai ronde. Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PmCheckRound $pmCheckRound)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PmCheckRound $pmCheckRound)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePmCheckRoundRequest $request, PmCheckRound $pmCheckRound)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PmCheckRound $pmCheckRound)
    {
        //
    }

    public function execute(PmCheckRound $round)
    {
        // Pengecekan status
        if ($round->round_status !== 'In Progress') {
             return redirect()->route('pm_rounds.index')->with([
                'error_title' => 'Ronde Selesai',
                'error_message' => 'Ronde ini sudah diselesaikan pada ' . $round->completion_time
            ]);
        }
        
        // Ambil data untuk Form Eksekusi
        $equipments = MasterEquipment::with('type')->get();
        // Kelompokkan Master Tugas berdasarkan tipe equipment
        $masterTasks = Masterpmtask::all()->groupBy('equipment_type_id'); 
        
        // Ambil hasil yang sudah tersimpan (jika Teknisi melakukan save draft)
        $savedResults = PmCheckResult::where('round_id', $round->id)
                                    ->get()
                                    ->keyBy(function($item) {
                                        return $item->equipment_id . '-' . $item->master_pm_task_id;
                                    });

        return view('pages.preventive-shift-v2.execute', compact('round', 'equipments', 'masterTasks', 'savedResults'));
    }

    public function saveResults(Request $request, PmCheckRound $round)
    {
        $data = $request->input('results');
        $savedCount = 0;

        // Memulai transaksi database
        DB::beginTransaction();

        try {
            foreach ($data as $equipmentId => $taskResults) {
                foreach ($taskResults as $taskId => $result) {
                    
                    // Hanya proses jika 'check_result' telah dipilih
                    if (isset($result['check_result']) && $result['check_result'] !== '') {
                        
                        $isAnomaly = ($result['check_result'] === 'Anomaly');
                        $description = $isAnomaly ? ($result['anomaly_description'] ?? 'N/A') : null;

                        // Cari dan perbarui, atau buat baru (upsert)
                        PmCheckResult::updateOrCreate(
                            [
                                'round_id' => $round->id,
                                'equipment_id' => $equipmentId,
                                'master_pm_task_id' => $taskId,
                            ],
                            [
                                'check_result' => $result['check_result'],
                                'anomaly_description' => $description,
                                'is_anomaly_detected' => $isAnomaly,
                                'anomaly_found_at' => $isAnomaly ? now() : null,
                            ]
                        );
                        $savedCount++;
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with([
                'success_title' => 'Tersimpan!',
                'success_message' => "{$savedCount} hasil pengecekan berhasil disimpan sebagai draft."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'error_title' => 'Gagal Menyimpan!',
                'error_message' => 'Terjadi kesalahan saat menyimpan hasil: ' . $e->getMessage()
            ]);
        }
    }

    public function completeRound(Request $request, PmCheckRound $round)
    {
        // Pastikan Anda memanggil saveResults terlebih dahulu (melalui form submit)
        $this->saveResults($request, $round); 

        // Hitung total anomali
        $totalAnomalies = PmCheckResult::where('round_id', $round->id)
                                        ->where('is_anomaly_detected', true)
                                        ->count();
        
        // Finalisasi Ronde
        $round->update([
            'round_status' => 'Completed',
            'completion_time' => now(),
            'total_anomalies' => $totalAnomalies,
        ]);

        return redirect()->route('pm_rounds.index')->with([
            'success_title' => 'Ronde Selesai!',
            'success_message' => "Ronde shift {$round->shift_name} selesai dengan {$totalAnomalies} temuan anomali."
        ]);
    }

    protected function determineShift()
    {
        $hour = date('H');
        if ($hour >= 6 && $hour < 14) {
            return 'Shift 1 (Pagi)';
        } elseif ($hour >= 14 && $hour < 22) {
            return 'Shift 2 (Siang)';
        } else {
            return 'Shift 3 (Malam)';
        }
    }

}

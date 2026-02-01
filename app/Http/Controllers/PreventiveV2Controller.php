<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePreventiveTargetRequest;
use App\Models\MasterEquipment;
use App\Models\MasterEquipmentType;
use App\Models\PreventiveSchedulesV2;
use App\Models\PreventiveTarget;
use App\Models\PreventiveTargetsV2;
use App\Models\Specializations;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreventiveV2Controller extends Controller
{
    private function getSpecialistId(int $equipmentTypeId): ?int
    {
        // 1. Temukan Specialization ID berdasarkan Equipment Type ID
        // Asumsi: 1 Equipment Type = 1 Specialization (menggunakan kolom 'type_id' di specializations)
        $specialization = Specializations::where('type_id', $equipmentTypeId)->first();

        if (!$specialization) {
            return null; // Tidak ada Spesialisasi yang didefinisikan untuk tipe ini
        }
        
        $specializationId = $specialization->id;

        // 2. Ambil semua User ID yang memiliki Specialization ID ini
        $technicianIds = DB::table('technician_specialists')
                           ->where('specialization_id', $specializationId)
                           ->pluck('user_id')
                           ->toArray();

        if (empty($technicianIds)) {
            return null; // Tidak ada teknisi yang memiliki spesialisasi ini
        }

        // 3. Pilih salah satu teknisi secara acak untuk load balancing sederhana
        return $technicianIds[array_rand($technicianIds)];
    }

    public function index(Request $request)
    {
        // 1. Ambil Periode Filter
        $currentMonth = $request->input('month', date('n'));
        $currentYear = $request->input('year', date('Y'));
        
        // Data untuk filter Bulan/Tahun
        $nama_bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        // 2. DATA TARGET VS REALISASI (PER TIPE EQUIPMENT & GLOBAL)
        $targets = PreventiveTargetsV2::with('equipmentType')
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->get();
        
        $targetIds = $targets->pluck('id');

        // Hitung status jadwal yang dibuat dari target-target ini
        $schedulesSummary = PreventiveSchedulesV2::select(
                            'target_id', 
                            DB::raw('COUNT(id) as total_scheduled'),
                            DB::raw('SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as total_completed'),
                            DB::raw('SUM(CASE WHEN status = "Canceled" THEN 1 ELSE 0 END) as total_canceled')
                        )
                        ->whereIn('target_id', $targetIds)
                        ->groupBy('target_id')
                        ->get()
                        ->keyBy('target_id');
        
        // Data Per Equipment Type
        $dashboardData = $targets->map(function ($target) use ($schedulesSummary) {
            $summary = $schedulesSummary->get($target->id);

            $totalScheduled = $summary->total_scheduled ?? 0;
            $totalCompleted = $summary->total_completed ?? 0;
            
            // Hitung persentase realisasi terhadap total jadwal yang dibuat
            $percentage = ($totalScheduled > 0) 
                          ? round(($totalCompleted / $totalScheduled) * 100, 1) 
                          : 0;

            return (object) [
                'equipment_type' => $target->equipmentType->name,
                'target_count' => $target->target_count, // Target (Jumlah Unit)
                'total_scheduled' => $totalScheduled,    // Total Jadwal yang Dibuat
                'completed_count' => $totalCompleted,
                'percentage' => $percentage,
            ];
        });

        // Data Global Summary
        $totalTarget = $targets->sum('target_count');
        $totalScheduledGlobal = $dashboardData->sum('total_scheduled');
        $totalCompletedGlobal = $dashboardData->sum('completed_count');
        
        $overallPercentage = ($totalScheduledGlobal > 0) ? round(($totalCompletedGlobal / $totalScheduledGlobal) * 100, 1) : 0;
        
        $globalSummary = [
            'totalTarget' => $totalTarget,
            'totalScheduled' => $totalScheduledGlobal,
            'totalCompleted' => $totalCompletedGlobal,
            'overallPercentage' => $overallPercentage,
        ];
        
        
        // 3. DATA BEBAN KERJA SPESIALIS
        
        // Ambil semua Spesialis Aktif (is_specialist = 1)
        $specialists = User::where('is_specialist', 1)
                           ->get(['id', 'name']);
                           
        // Hitung Jadwal yang Ditugaskan untuk periode ini
        $schedulesByTechnician = PreventiveSchedulesV2::select(
                            'technician_id', 
                            DB::raw('COUNT(id) as total_assigned'),
                            DB::raw('SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as total_completed')
                        )
                        ->where('target_month', $currentMonth)
                        ->where('target_year', $currentYear)
                        ->whereNotNull('technician_id')
                        ->groupBy('technician_id')
                        ->get()
                        ->keyBy('technician_id');
        
        // Gabungkan Data (Left Join)
        $specialistRealization = $specialists->map(function ($specialist) use ($schedulesByTechnician) {
            $summary = $schedulesByTechnician->get($specialist->id);

            $totalAssigned = $summary->total_assigned ?? 0;
            $totalCompleted = $summary->total_completed ?? 0;
            
            $percentage = ($totalAssigned > 0) 
                          ? round(($totalCompleted / $totalAssigned) * 100, 1) 
                          : 0;

            return (object) [
                'specialist_name' => $specialist->name,
                'total_assigned' => $totalAssigned,
                'total_completed' => $totalCompleted,
                'percentage' => $percentage,
            ];
        })->sortByDesc('total_assigned'); // Urutkan berdasarkan beban kerja tertinggi

        return view('pages.preventive-v2.index', compact(
            'dashboardData',         // Target vs Realisasi Per Equipment
            'specialistRealization', // Beban Kerja Per Spesialis
            'globalSummary',         // Ringkasan Total
            'currentMonth', 
            'currentYear', 
            'nama_bulan'
        ));
    }

    public function create()
    {
        // 1. Ambil semua tipe equipment untuk dropdown
        $equipments = MasterEquipmentType::all();

        // 2. Ambil daftar target yang sudah dibuat untuk tabel di sisi kanan
        $targets = PreventiveTargetsV2::with('equipmentType')
                     ->orderBy('year', 'desc')
                     ->orderBy('month', 'desc')
                     ->get();

        return view('pages.preventive-v2.create', compact('equipments', 'targets'));
    }


    public function store(StorePreventiveTargetRequest $request)
    {
         // Data sudah divalidasi oleh StorePreventiveTargetRequest (termasuk unique constraint)
        $data = $request->validated();
        
        $data['created_by'] = auth()->id();
        
        DB::beginTransaction();

        try {
            // 1. SIMPAN DATA TARGET TINGKAT TIPE
            $target = PreventiveTargetSV2::create($data);

            // 2. GENERASI JADWAL TINGKAT UNIT & TANGKAP JUMLAH JADWAL
            $generatedCount = $this->generateSchedules($target); // Tangkap jumlah yang di-generate

            DB::commit();

            // Mengubah key menjadi 'swal_success' dan menggunakan $generatedCount
            return redirect()->route('preventive-target.create')
                            ->with('swal_success', "Target bulanan dan jadwal telah berhasil dibuat! Sebanyak $generatedCount jadwal unit telah digenerasi.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Mengubah key menjadi 'swal_error' untuk ditampilkan di SweetAlert (Opsional, tapi bagus)
            $errorMessage = 'Gagal menyimpan target dan menggenerasi jadwal. Mohon coba lagi.';
            
            // Log error
            \Log::error("Gagal membuat target dan jadwal PM: " . $e->getMessage());

            return redirect()->back()
                            ->withInput()
                            ->with('swal_error', $errorMessage);
        }
    }

    protected function generateSchedules(PreventiveTargetsV2 $target)
    {
        // A. Cari semua unit equipment yang termasuk dalam equipment_type_id ini
        // $units = MasterEquipment::where('equipment_type_id', $target->equipment_type_id)->get();
        $units = MasterEquipment::where('equipment_type_id', $target->equipment_type_id)
                           ->orderByRaw('last_pm_date IS NULL DESC') 
                           ->orderBy('last_pm_date', 'asc')
                           ->get();
        
        // B. Batasi jumlah unit yang akan dijadwalkan sesuai target_count
        // Jika target_count lebih kecil dari jumlah unit, ambil N unit pertama
        $unitsToSchedule = $units->take($target->target_count); 

        $schedules = [];
        foreach ($unitsToSchedule as $unit) {
            // C. Buat array data untuk jadwal baru
            $technicianId = $this->getSpecialistId($unit->equipment_type_id); // Ambil dari unit->equipment_type_id

            $schedules[] = [
                'target_id' => $target->id,
                'technician_id' => $technicianId,
                'equipment_id' => $unit->id, 
                'target_month' => $target->month,
                'target_year' => $target->year,
                'status' => 'Scheduled',
                'created_at' => now(),
                'updated_at' => now(),
                'scheduled_date' => now() //bisa dikosongkan/diisi nanti oleh penjadwal
            ];
        }
        
        // D. Insert semua jadwal baru
        if (!empty($schedules)) {
            PreventiveSchedulesV2::insert($schedules);
        }
        
        // E. KEMBALIKAN JUMLAH SCHEDULE YANG DIBUAT
        return count($schedules); 

    }

    public function edit(PreventiveTargetsV2 $target)
    {
        $equipments = MasterEquipmentType::all();

        $schedules = PreventiveSchedulesV2::where('target_id',$target->id)->get();

        $technicians = User::all();
        // dd($schedules);
        
        return view('pages.preventive-v2.edit', compact('equipments', 'target','schedules','technicians')); 
    }


    public function update(StorePreventiveTargetRequest $request, PreventiveTargetsV2 $target)
    {
        $data = $request->validated();
        $newTargetCount = $data['target_count'];
        $oldTargetCount = $target->target_count;
        $difference = $newTargetCount - $oldTargetCount;
        
        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // 1. UPDATE DATA TARGET TINGKAT TIPE
            $target->update(['target_count' => $newTargetCount]);

            $statusMessage = "";
            
            // 2. LOGIKA PENYESUAIAN JADWAL
            
            if ($difference > 0) {
                // TARGET BERTAMBAH: Tambahkan jadwal baru.
                
                // Kita perlu mengambil daftar unit yang BELUM dijadwalkan di bulan ini,
                // atau yang paling lama di-PM, lalu generate sebanyak $difference.
                
                $generated = $this->generateAdditionalSchedules($target, abs($difference));
                $statusMessage = "Target berhasil diubah. Ditambahkan {$generated} jadwal unit baru.";
                
            } elseif ($difference < 0) {
                // TARGET BERKURANG: Batalkan jadwal yang belum dikerjakan.
                
                $canceled = $this->cancelExcessSchedules($target, abs($difference));
                $statusMessage = "Target berhasil diubah. Dibatalkan {$canceled} jadwal unit yang belum dikerjakan.";
                
            } else {
                // TARGET SAMA: Tidak ada yang diubah.
                $statusMessage = "Target berhasil diubah, namun jumlah jadwal tetap.";
            }
            
            DB::commit();

            // 3. KIRIM PESAN SUKSES KE VIEW DENGAN SWEETALERT
            $message = "Target PM untuk {$target->equipmentType->name} pada {$target->year}/{$target->month} berhasil diubah menjadi {$newTargetCount} kali.<br>{$statusMessage}";
            
            return redirect()->route('preventive-target.create')
                            ->with('swal_success', $message);
                            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error("Gagal mengupdate target dan menyesuaikan jadwal PM: " . $e->getMessage());

            return redirect()->back()
                            ->withInput()
                            ->with('swal_error', 'Gagal mengupdate target. Error: ' . $e->getMessage());
        }
    }

    protected function generateAdditionalSchedules(PreventiveTargetsV2 $target, int $count): int
    {
        // A. Temukan unit yang PALING LAMA di-PM dan BELUM memiliki jadwal 'Scheduled' di bulan ini
        $scheduledUnitIds = PreventiveSchedulesV2::where('target_month', $target->month)
                                                ->where('target_year', $target->year)
                                                ->whereIn('status', ['Scheduled', 'In Progress']) // Unit yang sudah punya jadwal aktif
                                                ->pluck('equipment_id');
        
        // B. Ambil unit yang paling lama di-PM dan TIDAK ADA di $scheduledUnitIds
        $unitsToSchedule = MasterEquipment::where('equipment_type_id', $target->equipment_type_id)
                                        ->whereNotIn('id', $scheduledUnitIds)
                                        ->orderByRaw('last_pm_date IS NULL DESC') 
                                        ->orderBy('last_pm_date', 'asc')
                                        ->take($count) // Ambil sebanyak $count
                                        ->get();

        $schedules = [];
        foreach ($unitsToSchedule as $unit) {
            // Dapatkan ID Spesialisasi Otomatis
            $technicianId = $this->getSpecialistId($unit->equipment_type_id); // Ambil dari unit->equipment_type_id
            $schedules[] = [
                'target_id' => $target->id,
                'equipment_id' => $unit->id, 
                'technician_id' => $technicianId, 
                'target_month' => $target->month,
                'target_year' => $target->year,
                'status' => 'Scheduled',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        // C. Insert semua jadwal baru dan kembalikan jumlah yang dimasukkan
        if (!empty($schedules)) {
            PreventiveSchedulesV2::insert($schedules);
            return count($schedules); // <-- Mengembalikan INT jika ada data
        }
        
        // D. Krusial: Mengembalikan 0 jika array $schedules kosong.
        return 0; // <-- Mengembalikan INT jika tidak ada data yang dimasukkan
    }
        
    protected function cancelExcessSchedules(PreventiveTargetsV2 $target, int $count): int
    {
        // C. Cari jadwal yang masih berstatus 'Scheduled' di bulan ini
        $schedulesToCancel = PreventiveSchedulesV2::where('target_month', $target->month)
                                                ->where('target_year', $target->year)
                                                ->where('status', 'Scheduled') // Hanya batalkan yang masih 'Scheduled'
                                                ->orderBy('created_at', 'desc') // Batalkan dari yang paling baru digenerasi/dijadwalkan
                                                ->take($count)
                                                ->pluck('id');

        if ($schedulesToCancel->isEmpty()) {
            return 0;
        }
        
        // D. Lakukan pembatalan (update status)
        PreventiveSchedulesV2::whereIn('id', $schedulesToCancel)
                            ->update(['status' => 'Canceled', 'updated_at' => now()]);
                            
        return $schedulesToCancel->count();
    }

    public function destroy(PreventiveTargetsV2 $target)
    {
        DB::beginTransaction();

        try {
            // 1. Ambil jumlah jadwal yang terkait sebelum dihapus/dibatalkan
            $schedulesCount = PreventiveSchedulesV2::where('target_month', $target->month)
                                                ->where('target_year', $target->year)
                                                ->count();

            // 2. HAPUS SEMUA JADWAL UNIT YANG TERKAIT
            // Catatan: Jika Anda tidak ingin menghapus yang sudah Completed/In Progress,
            // gunakan logika ini:
            
            // Batalkan/Hapus yang Scheduled
            $canceled = PreventiveSchedulesV2::where('target_month', $target->month)
                                            ->where('target_year', $target->year)
                                            ->where('status', 'Scheduled')
                                            ->delete(); // Atau ->update(['status' => 'Canceled'])

            // Sisakan yang sudah In Progress atau Completed (hanya jika target boleh dihapus)
            // Jika kebijakan bisnis mengharuskan semua jadwal dihapus/dibatalkan, gunakan:
            PreventiveSchedulesV2::where('target_month', $target->month)
                                ->where('target_year', $target->year)
                                ->delete(); // Menghapus semua jadwal terkait

            // 3. HAPUS TARGET UTAMA
            $target->delete();

            DB::commit();
            
            $message = "Target {$target->equipmentType->name} pada {$target->year}/{$target->month} berhasil dihapus. <br> Sebanyak {$schedulesCount} jadwal unit telah dibatalkan/dihapus.";

            return redirect()->route('preventive-target.create')
                            ->with('swal_success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error("Gagal menghapus target PM: " . $e->getMessage());
            
            return redirect()->back()
                            ->with('swal_error', 'Gagal menghapus target. Error: ' . $e->getMessage());
        }
    }

    public function bulkUpdate(Request $request)
    {
        try {
            $request->validate([
                'schedules' => 'required|array',
            ]);

            foreach ($request->schedules as $id => $data) {

                PreventiveSchedulesV2::where('id', $id)->update([
                    'technician_id'    => $data['technician_id'] ?? null,
                ]);
            }

            return redirect()->back()->with(
                'swal_success',
                'Preventive schedule berhasil diperbarui.'
            );

        } catch (\Throwable $e) {

            return redirect()->back()->with(
                'swal_error',
                'Terjadi kesalahan saat memperbarui preventive schedule.<br>Silakan coba kembali.'
            );
        }
    }



    // ambil dari sini untuk dashboard baru
    public function globalSummary(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $targets = PreventiveTargetsV2::where('month', $month)->where('year', $year)->get();
        $totalTarget = $targets->sum('target_count');

        $targetIds = $targets->pluck('id');
        $schedulesSummary = PreventiveSchedulesV2::select(
                                'target_id',
                                DB::raw('COUNT(id) as total_scheduled'),
                                DB::raw('SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as total_completed')
                            )
                            ->whereIn('target_id', $targetIds)
                            ->groupBy('target_id')
                            ->get();

        $totalScheduledGlobal = $schedulesSummary->sum('total_scheduled');
        $totalCompletedGlobal = $schedulesSummary->sum('total_completed');

        $overallPercentage = ($totalScheduledGlobal > 0) ? round(($totalCompletedGlobal / $totalScheduledGlobal) * 100, 1) : 0;

        return response()->json([
            'totalTarget' => $totalTarget,
            'totalScheduled' => $totalScheduledGlobal,
            'totalCompleted' => $totalCompletedGlobal,
            'overallPercentage' => $overallPercentage
        ]);
    }

    public function chartEquipment(Request $request)
    {
        $month = $request->month;
        $year = $request->year;

        $targets = PreventiveTargetsV2::with('equipmentType')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $targetIds = $targets->pluck('id');

        $schedulesSummary = PreventiveSchedulesV2::select(
            'target_id',
            DB::raw('COUNT(id) as total_scheduled'),
            DB::raw('SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed_count')
        )
        ->whereIn('target_id', $targetIds)
        ->groupBy('target_id')
        ->get()
        ->keyBy('target_id');

        $dashboardData = $targets->map(function ($target) use ($schedulesSummary) {
            $summary = $schedulesSummary->get($target->id);

            $totalScheduled = $summary->total_scheduled ?? 0;
            $totalCompleted = $summary->completed_count ?? 0;

            return [
                'equipment_type' => $target->equipmentType->name,
                'target_count' => $target->target_count,
                'total_scheduled' => $totalScheduled,
                'completed_count' => $totalCompleted,
                'percentage' => ($totalScheduled > 0) ? round(($totalCompleted / $totalScheduled) * 100, 1) : 0,
            ];
        });

        return response()->json($dashboardData);
    }

    public function chartSpecialist(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $specialists = User::where('is_specialist', 1)->get(['id','name']);

        $schedulesByTechnician = PreventiveSchedulesV2::select(
                                'technician_id',
                                DB::raw('COUNT(id) as total_assigned'),
                                DB::raw('SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as total_completed')
                            )
                            ->where('target_month', $month)
                            ->where('target_year', $year)
                            ->whereNotNull('technician_id')
                            ->groupBy('technician_id')
                            ->get()
                            ->keyBy('technician_id');
        // dd($schedulesByTechnician);

        $data = $specialists->map(function ($specialist) use ($schedulesByTechnician) {
            $summary = $schedulesByTechnician->get($specialist->id);
            $totalAssigned = $summary->total_assigned ?? 0;
            $totalCompleted = $summary->total_completed ?? 0;
            $percentage = ($totalAssigned > 0) ? round(($totalCompleted / $totalAssigned) * 100, 1) : 0;

            return [
                'specialist_name' => $specialist->name,
                'percentage' => $percentage,
                'total_assigned' => $totalAssigned,
                'total_completed' => $totalCompleted
            ];
        });

        return response()->json($data->sortByDesc('total_assigned')->values());
    }

    public function tableEquipment(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $targets = PreventiveTargetsV2::with('equipmentType')
                        ->where('month', $month)
                        ->where('year', $year)
                        ->get();

        $targetIds = $targets->pluck('id');

        $schedulesSummary = PreventiveSchedulesV2::select(
                                'target_id',
                                DB::raw('COUNT(id) as total_scheduled'),
                                DB::raw('SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as total_completed')
                            )
                            ->whereIn('target_id', $targetIds)
                            ->groupBy('target_id')
                            ->get()
                            ->keyBy('target_id');

        $data = $targets->map(function ($target) use ($schedulesSummary) {
            $summary = $schedulesSummary->get($target->id);
            $totalScheduled = $summary->total_scheduled ?? 0;
            $totalCompleted = $summary->total_completed ?? 0;
            $percentage = ($totalScheduled > 0) ? round(($totalCompleted / $totalScheduled) * 100, 1) : 0;

            return [
                'equipment_type' => $target->equipmentType->name,
                'target_count' => $target->target_count,
                'total_scheduled' => $totalScheduled,
                'completed_count' => $totalCompleted,
                'percentage' => $percentage
            ];
        });

        return response()->json($data);
    }

    public function tableSpecialist(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));

        $specialists = User::where('is_specialist', 1)->get(['id','name']);

        $schedulesByTechnician = PreventiveSchedulesV2::select(
                                'technician_id',
                                DB::raw('COUNT(id) as total_assigned'),
                                DB::raw('SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as total_completed')
                            )
                            ->where('target_month', $month)
                            ->where('target_year', $year)
                            ->whereNotNull('technician_id')
                            ->groupBy('technician_id')
                            ->get()
                            ->keyBy('technician_id');

        $data = $specialists->map(function ($specialist) use ($schedulesByTechnician) {
            $summary = $schedulesByTechnician->get($specialist->id);
            $totalAssigned = $summary->total_assigned ?? 0;
            $totalCompleted = $summary->total_completed ?? 0;
            $percentage = ($totalAssigned > 0) ? round(($totalCompleted / $totalAssigned) * 100, 1) : 0;

            return [
                'specialist_name' => $specialist->name,
                'total_assigned' => $totalAssigned,
                'total_completed' => $totalCompleted,
                'percentage' => $percentage
            ];
        });

        return response()->json($data->sortByDesc('total_assigned')->values());
    }

}

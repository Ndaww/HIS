<?php

namespace App\Http\Controllers;

use App\Models\PMFormHeader;
use App\Http\Requests\StorePMFormHeaderRequest;
use App\Http\Requests\UpdatePMFormHeaderRequest;
use App\Models\MasterEquipment;
use App\Models\PMFormDetail;
use App\Models\PreventiveSchedulesV2;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yajra\DataTables\Facades\DataTables;

class PMFormHeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentTechnicianId = auth()->user()->id; // Ganti dengan logika otentikasi yang sesuai, misalnya: auth()->user()->id

        // Ambil jadwal yang statusnya 'Scheduled' untuk teknisi ini
        // $schedules = PreventiveSchedulesV2::where('status', 'Scheduled')
        //                                 ->where('technician_id', $currentTechnicianId)
        //                                 // Anda bisa menambahkan eager loading jika dibutuhkan
        //                                 // ->with(['equipment', 'technician'])
        //                                 ->orderBy('scheduled_date', 'asc')
        //                                 ->get();

        return view('pages.preventive-v2.task.index');
    }

    public function getTasksData(Request $request)
    {
        $currentTechnicianId = auth()->user()->id;

        $schedules = PreventiveSchedulesV2::where('status', 'Scheduled')
                                         ->where('technician_id', $currentTechnicianId)
                                         ->with('equipment')
                                         ->orderBy('scheduled_date', 'asc');

        return DataTables::of($schedules)
            ->addIndexColumn()
            ->addColumn('name_equipment', function($schedule) {
                return $schedule->equipment ? $schedule->equipment->name : '-';
            })
            ->addColumn('target_period', function($schedule) {
                return $schedule->target_month . '/' . $schedule->target_year;
            })
            ->editColumn('scheduled_date', function($schedule) {
                if ($schedule->scheduled_date) {
                    return Carbon::parse($schedule->scheduled_date)->format('d M Y');
                }
                return '-';
            })
            ->addColumn('status', function($schedule) {
                $badge = $schedule->status == 'Scheduled' ? 'bg-warning text-dark' : 'bg-secondary text-white';
                return '<span class="badge '.$badge.'">'.$schedule->status.'</span>';
            })
            ->addColumn('action', function($schedule) {
                $url = route('pm.create', $schedule->id);
                return '<a href="'.$url.'" class="btn btn-sm btn-success">
                            <i class="fas fa-file-alt"></i> Isi Form PM
                        </a>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create($scheduleId)
    {
        try {
            DB::beginTransaction();
            $schedule = PreventiveSchedulesV2::findOrFail($scheduleId);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to prepare PM form for schedule ID " . $scheduleId . ": " . $e->getMessage());

            return back()->with('error', 'Gagal menyiapkan formulir PM. Jadwal tidak ditemukan atau error database: Cek log untuk detail.');
        }

        $schedule->load(['equipment', 'technician']);
        $details = [];

        return view('pages.preventive-v2.task.create', compact('schedule', 'details'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePMFormHeaderRequest $request)
    {
        // 1. Validasi Data
        $request->validate([
            'schedule_id' => 'required|exists:preventive_schedules_v2_s,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'overall_result' => ['required', Rule::in(['Baik', 'Perbaikan Minor', 'Tindak Lanjut'])],
            'notes' => 'nullable|string',

            // Validasi untuk New Details (Baris Input Fleksibel / Default)
            'new_details' => 'required|array',
            'new_details.*.task_description' => 'required_with:new_details|string|max:255',
            'new_details.*.standard_value' => 'nullable|string|max:100',
            'new_details.*.actual_value' => 'nullable|string|max:100',
            'new_details.*.pm_status' => ['required_with:new_details', Rule::in(['OK', 'Not OK', 'Adjusted'])],
            'new_details.*.pm_notes' => 'nullable|string|max:255',
        ], [
            'end_time.after' => 'Waktu selesai harus lebih lambat dari waktu mulai.',
            'overall_result.required' => 'Hasil Keseluruhan PM wajib dipilih.',
            'new_details.required' => 'Setidaknya satu baris tugas harus diisi.', // Pesan untuk kasus form benar-benar kosong
            'new_details.*.task_description.required_with' => 'Deskripsi untuk tugas wajib diisi.',
            'new_details.*.pm_status.required_with' => 'Status untuk tugas wajib dipilih.',
        ]);

        try {
            DB::beginTransaction();

            // 2. Ambil Schedule untuk mendapatkan ID terkait
            $schedule = PreventiveSchedulesV2::findOrFail($request->schedule_id);

            // 3. Buat Header (p_m_form_headers)
            $header = PmFormHeader::create([
                'schedule_id' => $schedule->id,
                'equipment_id' => $schedule->equipment_id,
                'technician_id' => $schedule->technician_id,
                'pm_date' => Carbon::today(), // Tanggal eksekusi PM
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'overall_result' => $request->overall_result,
                'notes' => $request->notes,
            ]);

            // 4. Buat Details (p_m_form_details)
            if ($request->has('new_details')) {
                $detailsToInsert = [];
                foreach ($request->new_details as $data) {
                    // Hanya masukkan baris jika task_description diisi (walaupun sudah divalidasi, ini double check)
                    if (!empty($data['task_description'])) {
                        $detailsToInsert[] = [
                            'form_header_id' => $header->id,
                            'task_description' => $data['task_description'],
                            'standard_value' => $data['standard_value'] ?? null,
                            'actual_value' => $data['actual_value'] ?? null,
                            'pm_status' => $data['pm_status'],
                            'pm_notes' => $data['pm_notes'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }

                // Gunakan insert untuk performa yang lebih baik
                if (!empty($detailsToInsert)) {
                    PmFormDetail::insert($detailsToInsert);
                }
            }

            // 5. Update Status Schedule
            $schedule->update([
                'realization_date' => Carbon::now(),
                'status' => 'Completed',
            ]);

            DB::commit();

            return redirect()->route('pm.index')
                             ->with('success', 'Formulir Preventive Maintenance berhasil disimpan dan jadwal telah diselesaikan!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Store PM Form Failed: " . $e->getMessage());
            return back()->withInput()
                         ->with('error', 'Terjadi kesalahan saat menyimpan data PM. Silakan coba lagi. Detail error ada di log.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PMFormHeader $pMFormHeader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PMFormHeader $pMFormHeader)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePMFormHeaderRequest $request, PMFormHeader $pMFormHeader)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PMFormHeader $pMFormHeader)
    {
        //
    }

    public function historyIndex(Request $request)
    {
        return view('pages.preventive-v2.task.history');
    }

        public function getHistoryData(Request $request)
    {
        // Query semua riwayat PM yang sudah tersimpan di p_m_form_headers
        $history = PmFormHeader::with(['equipment', 'technician'])
            ->orderBy('pm_date', 'desc')
            ->orderBy('start_time', 'desc');

        return DataTables::of($history)
            ->addIndexColumn()
            ->addColumn('equipment_name', function($header) {
                // Mengambil nama equipment melalui relasi
                return $header->equipment->name ?? '-';
            })
            ->addColumn('technician_name', function($header) {
                // Mengambil nama teknisi melalui relasi
                return $header->technician->name ?? '-';
            })
            ->editColumn('pm_date', function($header) {
                return \Carbon\Carbon::parse($header->pm_date)->format('d M Y');
            })
            ->addColumn('time_range', function($header) {
                return $header->start_time . ' - ' . $header->end_time;
            })
            ->addColumn('duration', function($header) {
                // Menghitung durasi (selisih Waktu Selesai - Waktu Mulai)
                try {
                    $start = Carbon::parse($header->start_time);
                    $end = Carbon::parse($header->end_time);
                    $diff = $start->diff($end);

                    $duration = '';
                    if ($diff->h > 0) {
                         $duration .= $diff->h . ' jam ';
                    }
                    if ($diff->i > 0) {
                         $duration .= $diff->i . ' menit';
                    }
                    return trim($duration) ?: '-';
                } catch (\Exception $e) {
                    return '-';
                }
            })
            ->addColumn('action', function($header) {
                $url = route('pm.show_history', $header->id);
                return '<a href="'.$url.'" class="btn btn-sm btn-info text-white">
                            <i class="ri-search-line"></i> Detail
                        </a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function showHistory($headerId)
    {
        try {
            // Memuat relasi equipment, technician, dan details
            $header = PmFormHeader::with(['equipment', 'technician', 'details'])
                        ->findOrFail($headerId);

            return view('pages.preventive-v2.task.show_history', compact('header'));

        } catch (\Exception $e) {
            Log::error("Failed to display PM history detail for header ID " . $headerId . ": " . $e->getMessage());

            return back()->with('error', 'Gagal memuat detail formulir PM. Data tidak ditemukan: Cek log untuk detail.');
        }
    }

    public function showHistoryEquipment($id)
    {
        $histories = PMFormHeader::where('equipment_id',$id)->get();
        $equipments = MasterEquipment::where('id',$id)->get()[0];
        // dd(empty($histories));

        return view('pages.preventive-v2.task.show_history_equipment', compact('histories','equipments'));
    }

    public function reportIndex()
    {
        $technicians = User::whereIn('id', PmFormHeader::pluck('technician_id')->unique())->get();
        $equipmentList = MasterEquipment::whereIn('id', PmFormHeader::pluck('equipment_id')->unique())->get();

        return view('pages.preventive-v2.task.report', compact('technicians', 'equipmentList'));
    }

    public function getReportData(Request $request)
    {
        $query = PmFormHeader::with(['equipment', 'technician'])
            ->orderBy('pm_date', 'desc')
            ->orderBy('start_time', 'desc');

        // --- FILTERING LOGIC ---

        // 1. Filter Tanggal (Date Range)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('pm_date', [$request->start_date, $request->end_date]);
        }

        // 2. Filter Teknisi (Technician)
        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        // 3. Filter Tipe Equipment (Equipment Type/Name)
        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->equipment_id);
        }

        // --- DATATABLES RESPONSE ---

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('equipment_name', function($header) {
                return $header->equipment->name ?? '-';
            })
            ->addColumn('technician_name', function($header) {
                return $header->technician->name ?? '-';
            })
            ->editColumn('pm_date', function($header) {
                return \Carbon\Carbon::parse($header->pm_date)->format('d M Y');
            })
            ->addColumn('time_range', function($header) {
                return $header->start_time . ' - ' . $header->end_time;
            })
            // Tambahan kolom detail: Durasi
            ->addColumn('duration', function($header) {
                try {
                    $start = Carbon::parse($header->start_time);
                    $end = Carbon::parse($header->end_time);
                    $diff = $start->diff($end);

                    $duration = '';
                    if ($diff->h > 0) { $duration .= $diff->h . ' jam '; }
                    if ($diff->i > 0) { $duration .= $diff->i . ' menit'; }
                    return trim($duration) ?: '-';
                } catch (\Exception $e) {
                    return '-';
                }
            })
            // Tambahan kolom detail: Notes/Catatan
            ->addColumn('notes_summary', function($header) {
                return \Illuminate\Support\Str::limit($header->notes, 50, '...');
            })
            ->addColumn('action', function($header) {
                $url = route('pm.show_history', $header->id);
                return '<a href="'.$url.'" class="btn btn-sm btn-info text-white">
                            <i class="ri-search-line"></i> Detail
                        </a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }


}

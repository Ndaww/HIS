<?php

namespace App\Http\Controllers;

use App\Models\MasterPatient;
use App\Http\Requests\StoreMasterPatientRequest;
use App\Http\Requests\UpdateMasterPatientRequest;
use App\Models\MasterRoom;
use App\Models\RoomBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class MasterPatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $patients = MasterPatient::latest()->get();
        return view('pages.master.patients.index', compact('patients'));
    }

    public function indexData(Request $request)
    {
        $query = RoomBooking::whereNull('checkout_at')->with('patient', 'room');

        if ($request->gender) {
            $query->whereHas('patient', fn($q) => $q->where('gender', $request->gender));
        }

        if ($request->room_status) {
            $query->whereHas('room', fn($q) => $q->where('status', $request->room_status));
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('name', fn($row) => $row->patient->name)
            ->addColumn('gender', fn($row) => $row->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan')
            ->addColumn('birth_date', fn($row) => \Carbon\Carbon::parse($row->patient->birth_date)->format('d-m-Y'))
            ->addColumn('no_ktp', fn($row) => $row->patient->no_ktp)
            ->addColumn('no_bpjs', fn($row) => $row->patient->no_bpjs ?? '-')
            ->addColumn('room', fn($row) => $row->room->name . ' (' . $row->room->class . ' - ' . $row->room->floor . ')')
            ->addColumn('checkin_at', fn($row) => $row->checkin_at ? \Carbon\Carbon::parse($row->checkin_at)->format('d-m-Y H:i') : '-')
            ->addColumn('room_status', function ($row) {
                return '<span class="badge bg-' . match($row->room->status) {
                    'kosong' => 'success',
                    'terisi' => 'danger',
                    'preventive' => 'warning',
                } . '">' . ucfirst($row->room->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row->checkout_at || $row->room_status == 'preventive') {
                    return '<span class="badge bg-success">Sudah Checkout</span>';
                }

                return '
                    <form action="' . route('patients.checkout', $row->id) . '" method="POST" class="d-inline form-checkout">
                        ' . csrf_field() . '
                        <button type="submit" class="btn btn-sm btn-danger btn-checkout">Checkout</button>
                    </form>
                ';
            })
            ->rawColumns(['room_status', 'action'])
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       return view('pages.master.patients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMasterPatientRequest $request)
    {
        // dd($request);
        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date',
            'no_ktp' => 'required|string|unique:master_patients,no_ktp',
            'no_bpjs' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        MasterPatient::create($request->all());

        return redirect()->route('patients.index')->with('success', 'Pasien berhasil didaftarkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterPatient $masterPatient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MasterPatient $masterPatient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMasterPatientRequest $request,$id)
    {
        $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'gender' => 'required|in:L,P',
        'birth_date' => 'required|date',
        'no_ktp' => 'required|string|unique:master_patients,no_ktp,' . $id,
        'no_bpjs' => 'nullable|string',
        'address' => 'nullable|string',
        'phone' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $patient = MasterPatient::findOrFail($id);
    $patient->update($request->all());

    return response()->json(['message' => 'Data berhasil diperbarui.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterPatient $masterPatient)
    {
        //
    }

    public function data()
    {
        $data = MasterPatient::latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('gender_text', fn($row) => $row->gender == 'L' ? 'Laki-laki' : 'Perempuan')
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-warning btn-edit" data-id="'.$row->id.'" data-json=\''.json_encode($row).'\'><i class="ri-edit-line"></i> Edit</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function checkout($id)
    {
        DB::beginTransaction();

        try {
            $booking = RoomBooking::with('room')->findOrFail($id);

            $booking->update([
                'checkout_at' => Carbon::now(),
            ]);

            $booking->room->update([
                'status' => 'preventive',
            ]);

            DB::commit();

            return back()->with('success', 'Pasien berhasil di-checkout. Ruangan masuk preventive.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat checkout: ' . $e->getMessage());
        }
    }

}

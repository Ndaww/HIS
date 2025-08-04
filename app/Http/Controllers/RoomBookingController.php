<?php

namespace App\Http\Controllers;

use App\Models\RoomBooking;
use App\Http\Requests\StoreRoomBookingRequest;
use App\Http\Requests\UpdateRoomBookingRequest;
use App\Models\MasterPatient;
use App\Models\MasterRoom;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class RoomBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $patients = MasterPatient::all();
        $rooms = MasterRoom::where('status', 'kosong')->get();
        return view('pages.bookings.index', compact('patients', 'rooms'));
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
    public function store(StoreRoomBookingRequest $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:master_patients,id',
            'room_id'    => 'required|exists:master_rooms,id',
            'checkin_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $room = MasterRoom::find($request->room_id);

        if (!$room || $room->status !== 'kosong') {
            return response()->json([
                'message' => 'Kamar tidak tersedia untuk booking.'
            ], 422);
        }

        $existingBooking = RoomBooking::where('patient_id', $request->patient_id)
            ->whereNull('checkout_at')
            ->first();

        if ($existingBooking) {
            return response()->json([
                'message' => 'Pasien ini masih menempati kamar lain.'
            ], 422);
        }

        RoomBooking::create([
            'patient_id' => $request->patient_id,
            'room_id'    => $request->room_id,
            'checkin_at' => $request->checkin_at,
        ]);

        $room->update(['status' => 'terisi']);

        return response()->json([
            'message' => 'Booking berhasil disimpan.'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomBooking $roomBooking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomBooking $roomBooking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoomBookingRequest $request, RoomBooking $roomBooking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomBooking $roomBooking)
    {
        //
    }

    public function data(Request $request)
    {
        $data = RoomBooking::with(['patient', 'room']);

        if ($request->has('room_ids') && is_array($request->room_ids)) {
            $data->whereIn('room_id', $request->room_ids);
        }

        // Booking dengan checkout null dulu, baru yang sudah checkout
        $data->orderByRaw('ISNULL(checkout_at) DESC')->orderBy('checkout_at', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('patient', fn($row) => $row->patient->name)
            ->addColumn('room', fn($row) => $row->room->name)
            ->addColumn('class', fn($row) => $row->room->class)
            ->addColumn('checkin', fn($row) => $row->checkin_at ? Carbon::parse($row->checkin_at)->format('d-m-Y H:i') : '-')
            ->addColumn('checkout', fn($row) => $row->checkout_at ? Carbon::parse($row->checkout_at)->format('d-m-Y H:i') : '-')
            ->addColumn('action', function ($row) {
                if (!$row->checkout_at) {
                    return '
                        <button class="btn btn-success btn-sm btn-checkout" data-id="'.$row->id.'">
                            <i class="ri ri-door-open-line"></i> Checkout
                        </button>
                        <button class="btn btn-danger btn-sm btn-cancel" data-id="'.$row->id.'">
                            <i class="ri ri-close-line"></i> Batal
                        </button>
                    ';
                }
                return '';
            })
            ->make(true);
    }

    public function cancel($id)
    {
        $booking = RoomBooking::findOrFail($id);

        if ($booking->checkout_at) {
            return response()->json([
                'message' => 'Booking tidak bisa dibatalkan karena sudah checkout.'
            ], 422);
        }

        // Set room back to "kosong"
        $booking->room->update(['status' => 'kosong']);

        $booking->delete();

        return response()->json([
            'message' => 'Booking berhasil dibatalkan.'
        ]);
    }

    public function checkout($id)
    {
        $booking = RoomBooking::findOrFail($id);

        if ($booking->checkout_at) {
            return response()->json([
                'message' => 'Pasien sudah checkout sebelumnya.'
            ], 422);
        }

        $booking->update([
            'checkout_at' => now()
        ]);

        // Update kamar jadi status 'preventive'
        $booking->room->update(['status' => 'preventive']);

        return response()->json([
            'message' => 'Checkout berhasil. Kamar masuk tahap preventive.'
        ]);
    }



}

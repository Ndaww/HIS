<?php

namespace App\Http\Controllers;

use App\Models\MasterRoom;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ValidasiGAController extends Controller
{
    public function index()
    {
        return view('pages.bookings.validasi');
    }

    public function datatable(Request $request)
    {
        $data = MasterRoom::where('status', 'done preventive')
                    ->wherein('ga_status',['pending','not_ok',null])
                    ->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('preventive_done_at', function ($row) {
                return $row->preventive_done_at
                    ? Carbon::parse($row->preventive_done_at)->format('d-m-Y')
                    : '-';
            })
            ->addColumn('status', function ($row) {
                return '<span class="badge bg-warning text-dark">Preventive</span>';
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-success btn-sm btn-validate" data-id="' . $row->id . '">
                            <i class="ri-check-line"></i> Validasi
                        </button>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function validateRoom(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:master_rooms,id',
            'status' => 'required|in:ok,not_ok',
            'notes' => 'nullable|string|max:1000',
        ]);

        $room = MasterRoom::findOrFail($request->room_id);

        if ($room->status !== 'preventive' && $room->status !== 'done preventive' ) {
            return response()->json(['message' => 'Status kamar bukan preventive.'], 422);
        }

        if ($request->status === 'ok') {
            $room->update([
                'status' => 'done ga',
                'ga_status' => 'ok',
                'ga_notes' => $request->notes,
                'preventive_done_at' => null,
            ]);
        } else {
            $room->update([
                'ga_status' => 'not_ok',
                'ga_notes' => $request->notes,
            ]);
        }

        return response()->json(['message' => 'Validasi GA Berhasil Disimpan']);
    }



}

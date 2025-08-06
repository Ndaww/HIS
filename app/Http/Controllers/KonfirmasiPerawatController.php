<?php

namespace App\Http\Controllers;

use App\Models\MasterRoom;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KonfirmasiPerawatController extends Controller
{

    public function index()
    {
    return view('pages.bookings.konfirmasi');
    }

    public function datatable(Request $request)
    {
        $data = MasterRoom::where('status', 'done ga')
            ->where('ga_status', 'ok')
            ->whereNull('nurse_confirmed_at')
            ->latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('checkbox', fn($row) => '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">')
            ->addColumn('ga_status', fn($row) => '<span class="badge bg-success">GA OK</span>')
            ->addColumn('action', fn($row) => '
                <button class="btn btn-sm btn-success btn-konfirmasi" data-id="' . $row->id . '">
                    <i class="ri-check-line"></i> Konfirmasi
                </button>
            ')
            ->rawColumns(['checkbox','ga_status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        // $request->validate([
        //     'room_ids' => 'required|array',
        //     'room_ids.*' => 'exists:master_rooms,id'
        // ]);

        if(isset($request->room_id)){
            $updated = MasterRoom::where('id', $request->room_id)
            ->where('status', 'done ga')
            ->where('ga_status', 'ok')
            ->whereNull('nurse_confirmed_at')
            ->update(['nurse_confirmed_at' => now(),'status'=>'kosong']);
            return redirect('/kamar-kosong/konfirmasi')->with('success','Berhasil dikonfirmasi');
        } else {
            $updated = MasterRoom::whereIn('id', $request->room_ids)
                ->where('status', 'done ga')
                ->where('ga_status', 'ok')
                ->whereNull('nurse_confirmed_at')
                ->update(['nurse_confirmed_at' => now(),'status'=>'kosong']);
        }

            return response()->json(['message' => "Berhasil mengonfirmasi {$updated} kamar."]);
    }
}

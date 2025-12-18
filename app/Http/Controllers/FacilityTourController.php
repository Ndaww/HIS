<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FacilityTour;
use App\Models\MasterEquipment;
use App\Models\MasterRoom;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FacilityTourController extends Controller
{
    public function index(Request $request)
    {

        if ($request->ajax()) {
            // $data = FacilityTour::with(['room', 'department'])->latest();
            $data = FacilityTour::select(
                'facility_tours.*', 
                'master_rooms.name as room_name',
                'departments.name as department_name'
            )
            ->leftJoin('master_rooms', 'facility_tours.room_id', '=', 'master_rooms.id')
            ->leftJoin('departments', 'facility_tours.department_id', '=', 'departments.id')
            ->latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function($row){
                    return \Carbon\Carbon::parse($row->created_at)->format('d-M-Y'); // contoh: 12-Dec-2025
                })
                ->make(true);
        }

        return view('pages.facility-tour.index');

        // ini per alat
        // return view('pages.facility-tour.create',compact('rooms'));
    }

    public function create()
    {

        $rooms = MasterRoom::all();
        $departments = Department::all();


        return view('pages.facility-tour.create2',compact('rooms','departments'));
    }

    public function createByRoom($roomId)
    {

        $rooms = MasterRoom::where('id',$roomId)->get()[0];
        $departments = Department::all();

        // ini per alat
        return view('pages.facility-tour.createByRoom',compact('rooms','departments'));
    }

    public function getEquipment($roomId)
    {
        return MasterEquipment::where('room_id', $roomId)->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pelapor' => 'required',
            'title' => 'required|string|max:255',
            'room_id' => 'required|integer',
            'risk_grading' => 'required|in:low,medium,high',
            'department_id' => 'required|integer',
            'detail' => 'required|string',
        ]);


        FacilityTour::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Facility Tour berhasil disimpan'
        ]);
    }


}

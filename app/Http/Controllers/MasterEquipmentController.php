<?php

namespace App\Http\Controllers;

use App\Models\MasterEquipment;
use App\Http\Requests\StoreMasterEquipmentRequest;
use App\Http\Requests\UpdateMasterEquipmentRequest;
use App\Models\MasterEquipmentType;
use App\Models\MasterRoom;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MasterEquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $equipmentTypes = MasterEquipmentType::all();
        $rooms = MasterRoom::all();
        return view('pages.master.equipments.index', compact('equipmentTypes','rooms'));
    }

    public function getTypeDatatable(Request $request)
    {
        if ($request->ajax()) {
            $data = MasterEquipmentType::select(['id', 'name', 'created_at', 'updated_at']);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="javascript:void(0)" data-id="'.$row->id.'" data-toggle="tooltip" data-original-title="Edit" class="edit-type btn btn-primary btn-sm mx-1">Edit</a>';
                    $btn .= '<a href="javascript:void(0)" data-id="'.$row->id.'" data-original-title="Delete" class="delete-type btn btn-danger btn-sm mx-1">Hapus</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
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
    public function store(StoreMasterEquipmentRequest $request)
    {
        //
    }

    public function storeType(Request $request)
    {
        MasterEquipmentType::updateOrCreate(
            ['id' => $request->type_id],
            ['name' => $request->name]
        );

        return response()->json(['success' => 'Master Equipment Type saved successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterEquipment $masterEquipment)
    {
        //
    }

    public function showType($id)
    {
        $type = MasterEquipmentType::find($id);
        return response()->json($type);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MasterEquipment $masterEquipment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMasterEquipmentRequest $request, MasterEquipment $masterEquipment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterEquipment $masterEquipment)
    {
        //
    }

    public function destroyType($id)
    {
        MasterEquipmentType::find($id)->delete();
        return response()->json(['success' => 'Master Equipment Type deleted successfully.']);
    }

    public function getEquipmentDatatable(Request $request)
    {
        if ($request->ajax()) {
            $data = MasterEquipment::with('type','room')->select('*');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('equipment_type_name', function ($row) {
                    return $row->type->name;
                })
                ->addColumn('room_name', function ($row) {
                    return $row->room->name;
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="javascript:void(0)" data-id="'.$row->id.'" data-toggle="tooltip" data-original-title="Edit" class="edit-equipment btn btn-primary btn-sm mx-1">Edit</a>';
                    $btn .= '<a href="javascript:void(0)" data-id="'.$row->id.'" data-original-title="Delete" class="delete-equipment btn btn-danger btn-sm mx-1">Hapus</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    /**
     * Store a newly created master equipment in storage.
     */
    public function storeEquipment(Request $request)
    {
        MasterEquipment::updateOrCreate(
            ['id' => $request->equipment_id],
            [
                'name' => $request->name,
                'serial_number' => $request->serial_number,
                'room_id' => $request->room_id,
                'equipment_type_id' => $request->equipment_type_id
            ]
        );

        return response()->json(['success' => 'Master Equipment saved successfully.']);
    }

    /**
     * Show the form for editing the specified master equipment.
     */
    public function showEquipment($id)
    {
        $equipment = MasterEquipment::with('type')->find($id);
        return response()->json($equipment);
    }

    /**
     * Remove the specified master equipment from storage.
     */
    public function destroyEquipment($id)
    {
        MasterEquipment::find($id)->delete();
        return response()->json(['success' => 'Master Equipment deleted successfully.']);
    }
}

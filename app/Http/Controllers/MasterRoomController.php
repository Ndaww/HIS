<?php

namespace App\Http\Controllers;

use App\Models\MasterRoom;
use App\Http\Requests\StoreMasterRoomRequest;
use App\Http\Requests\UpdateMasterRoomRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class MasterRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rooms = MasterRoom::latest()->get();
        return view('pages.master.rooms.index', compact('rooms'));
    }

    public function data()
    {
        $data = MasterRoom::latest();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-warning btn-edit" data-id="'.$row->id.'" data-json=\''.json_encode($row).'\'><i class="ri-edit-line"></i> Edit</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.master.rooms.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMasterRoomRequest $request)
    {
        // dd($request);
        $request->validate([
            'name' => 'required|string|max:255',
            'floor' => 'required',
            'class' => 'required',
        ]);

        MasterRoom::create($request->all());

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterRoom $masterRoom)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MasterRoom $masterRoom)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMasterRoomRequest $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'floor' => 'required',
            'class' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $patient = MasterRoom::findOrFail($id);
        $patient->update($request->all());

        return response()->json(['message' => 'Data berhasil diperbarui.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterRoom $masterRoom)
    {
        //
    }
}

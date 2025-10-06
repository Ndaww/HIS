<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterEquipmentType;
use App\Models\Specializations;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class SpecializationController extends Controller
{
    public function index()
    {
        $specializations = Specializations::all();
        $equipments = MasterEquipmentType::all();
        return view('pages.master.specializations.index', compact('specializations','equipments'));
    }

    public function data()
    {
        $query = Specializations::query();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('equipment_name', function ($row) {
                return $row->type->name ?? '-'; 
            })
            ->addColumn('action', function ($row) {
                // $data_for_json = $row->toArray();
                // $data_for_json['type_id'] = $row->type_id; 

                return '
                    <button class="btn btn-sm btn-warning btn-edit" data-json=\''.json_encode($row).'\'>Edit</button>
                    <form action="'.route('specializations.destroy', $row->id).'" method="POST" class="form-delete" style="display:inline-block;">
                        '.csrf_field().method_field('DELETE').'
                        <button type="submit" class="btn btn-sm btn-danger btn-delete">Hapus</button>
                    </form>

                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('master.specializations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:specializations,name',
            'description' => 'nullable',
            'type_id' => 'nullable'
        ]);

        Specializations::create($request->only(['name', 'description','type_id']));

        return redirect()->route('specializations.index')->with('success', 'Spesialis berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $specialization = Specializations::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:specializations,name,'.$id,
            'description' => 'nullable',
            'type_id' => 'nullable'
        ]);

        $specialization->update($request->only(['name', 'description','type_id']));

        return response()->json(['message' => 'Spesialis berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $specialization = Specializations::findOrFail($id);
        $specialization->delete();

        return redirect()->route('specializations.index')->with('success', 'Spesialisasi berhasil dihapus');
    }

}

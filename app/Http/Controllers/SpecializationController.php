<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Specializations;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class SpecializationController extends Controller
{
    public function index()
    {
        $specializations = Specializations::all();
        return view('pages.master.specializations.index', compact('specializations'));
    }

    public function data()
    {
        $query = Specializations::query();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
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
        ]);

        Specializations::create($request->only(['name', 'description']));

        return redirect()->route('specializations.index')->with('success', 'Spesialis berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $specialization = Specializations::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:specializations,name,'.$id,
            'description' => 'nullable',
        ]);

        $specialization->update($request->only(['name', 'description']));

        return response()->json(['message' => 'Spesialis berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $specialization = Specializations::findOrFail($id);
        $specialization->delete();

        return redirect()->route('specializations.index')->with('success', 'Spesialis berhasil dihapus');
    }

}

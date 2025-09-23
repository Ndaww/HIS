<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Department::select(['id', 'name', 'head_id', 'created_at', 'updated_at']);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('head_name', function(Department $department) {
                    return $department->head->name ?? '-';
                })
                ->addColumn('action', function($row){
                    $editBtn = '<button type="button" class="btn btn-sm btn-warning text-center edit-btn" data-id="'.$row->id.'">Edit</button>';
                    $deleteBtn = '<button type="button" class="btn btn-sm btn-danger text-center delete-btn" data-id="'.$row->id.'">Hapus</button>';
                    return '<div class="text-center">' . $editBtn . ' ' . $deleteBtn . '</div>';
                })
                ->rawColumns(['head_name','action'])
                ->make(true);
        }
        $users = User::select('id', 'name')->get();

        return view('pages.master.departments.index',[
            'users' => $users
        ]);

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
    public function store(StoreDepartmentRequest $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'head_id' => 'nullable|integer',
        ]);

        Department::create($request->all());
        return response()->json(['success' => 'Departemen berhasil ditambahkan.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        return response()->json($department);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'head_id' => 'nullable|integer',
        ]);

        $department->update($request->all());

        return response()->json(['success' => 'Departemen berhasil diperbarui.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return response()->json(['success' => 'Departemen berhasil dihapus.']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        $depts = Department::all();
        return view('pages.master.users.index', compact('depts'));
    }

    public function data()
    {
        $users = User::with('dept')->select('users.*');
        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('department', fn($row) => $row->dept->name ?? '-')
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-warning btn-edit" data-json=\''.json_encode($row).'\'>Edit</button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'">Delete</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $departments = Department::all();
        return view('pages.master.users.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|unique:users',
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'department_id' => 'required',
        ]);

        User::create([
            'nik' => $request->nik,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'department_id' => $request->department_id,
            'password' => bcrypt($request->password),
            'is_active' => $request->is_active ?? 1,
        ]);

        return redirect()->route('users.index')->with('success','User berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->only(['name','phone','department_id','is_active']));
        return response()->json(['message'=>'User berhasil diperbarui']);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message'=>'User berhasil dihapus']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\TechnicianSpecialist;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TechnicianController extends Controller
{
    public function index()
    {
        // 1. Eager load technicianSpecialists AND its nested specialization relationship.
        // //    Using the safer `first()` method instead of `get()[0]`.
        // //    Note: 'technicianSpecialists.specialization' implicitly includes 'technicianSpecialists'.
        // $user = User::with('technicianSpecialists.specialization')
        //     ->where('department_id', 4)
        //     ->first();

        // // 2. Check if a user was actually found before proceeding.
        // if (!$user) {
        //     dd("User with department_id 4 not found.");
        // }

        // // 3. Access the relationship data.
        // $specialists = $user->technicianSpecialists;

        // // 4. Check if the user has any specialists before accessing index 0.
        // if ($specialists->isEmpty()) {
        //     dd("User has no technician specialists.");
        // }

        // // 5. Dump the first technician specialist model.
        // dd($specialists[0]);
        // // This dumped model will include the 'specialization' relationship already loaded.
        // // You can access the specialization details like: $specialists[0]->specialization->name;
        $departments = Department::all();
        return view('pages.master.technicians.index', compact('departments'));
    }

    // public function data()
    // {
    //     $query = User::where('department_id', 4);

    //     return DataTables::of($query)
    //         ->addIndexColumn()
    //         ->addColumn('action', function ($row) {
    //             return '
    //                 <button class="btn btn-sm btn-warning btn-edit" data-json=\''.json_encode($row).'\'>Edit</button>
    //                 <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'">Hapus</button>
    //                 <button class="btn btn-sm btn-info btn-add-specialist"
    //     data-id="'.$row->id.'"
    //     data-name="'.$row->name.'">Tambah Spesialis</button>
    //             ';
    //         })
    //         ->make(true);
    // }
    public function data()
{
    // 1. Eager Load Relasi untuk menghindari N+1 problem
    $query = User::with('technicianSpecialists.specialization')
                ->where('department_id', 4);

    return DataTables::of($query)
        ->addIndexColumn()

        // 2. Tambahkan kolom Spesialisasi dengan kondisi
        ->addColumn('spesialisasi', function ($user) {
            // Cek apakah ada data di relasi technicianSpecialists
            if ($user->technicianSpecialists->isNotEmpty()) {
                // Jika ada, ambil nama-nama spesialisasinya
                $specializationNames = $user->technicianSpecialists
                    ->pluck('specialization.name')
                    ->filter() // Hapus nilai null jika relasi specialization gagal
                    ->implode(', '); // Gabungkan nama-nama dengan koma

                return $specializationNames;
            }

            // Jika tidak ada, tampilkan pesan
            return '';
        })

        // 3. Kolom 'action' (tetap seperti semula)
        ->addColumn('action', function ($row) {
            return '
                <button class="btn btn-sm btn-warning btn-edit" data-json=\''.json_encode($row).'\'>Edit</button>
                <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'">Hapus</button>
                <button class="btn btn-sm btn-info btn-add-specialist"
            data-id="'.$row->id.'"
            data-name="'.$row->name.'">Tambah Spesialis</button>
            ';
        })

        // 4. Pastikan kolom yang ditambahkan di atas aman dari XSS
        ->rawColumns(['spesialisasi', 'action']) // Karena 'action' berisi HTML, harus di-set
        ->make(true);
}

    public function data_specialist()
    {
        $query = User::where('department_id', 4);
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '
                    <button class="btn btn-sm btn-warning btn-edit" data-json=\''.json_encode($row).'\'>Edit</button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="'.$row->id.'">Hapus</button>
                    <button class="btn btn-sm btn-info btn-add-specialist"
        data-id="'.$row->id.'"
        data-name="'.$row->name.'">Tambah Spesialis</button>
                ';
            })
            ->make(true);
    }

    public function create()
    {
        $departments = Department::where('id', 4)->get();
        return view('pages.master.technicians.create',compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['department_id'] = 4;

        User::create($validated);

        return redirect()->route('technicians.index')->with('success', 'Technician berhasil ditambahkan');
    }

    public function store_tech(Request $request)
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

        return redirect()->route('technicians.index')->with('success','User berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,'.$user->id,
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        }

        $user->update($validated);

        return response()->json(['message' => 'Technician berhasil diupdate']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Technician berhasil dihapus']);
    }

    public function assignSpecialist(Request $request, $id)
    {
        $request->validate([
            'specialization_id' => 'required|exists:specializations,id',
        ]);

        TechnicianSpecialist::create([
            'user_id' => $id,
            'specialization_id' => $request->specialization_id,
        ]);

        User::where('id',$id)->update(['is_specialist'=> true]);

        return response()->json(['message' => 'Spesialis berhasil ditambahkan untuk teknisi ini']);
    }


}

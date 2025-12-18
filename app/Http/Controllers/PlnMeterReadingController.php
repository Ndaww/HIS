<?php

namespace App\Http\Controllers;

use App\Models\plnMeterReading;
use App\Http\Requests\StoreplnMeterReadingRequest;
use App\Http\Requests\UpdateplnMeterReadingRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PlnMeterReadingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = plnMeterReading::latest()->get();
        return view('pages.pln.index', compact('data'));
    }

    public function data()
    {
        $query = PlnMeterReading::orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('aksi', function ($row) {
                return '
                    <button class="btn btn-warning btn-sm" onclick="editData('.$row->id.')">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteData('.$row->id.')">Hapus</button>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.pln.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreplnMeterReadingRequest $request)
    {
        $request->validate([
            'id_pelanggan_pln' => 'required|string|max:50',
            'jam_pencatatan'   => 'required',
            'cos_phi'          => 'required|numeric',
            'wbp'              => 'required|numeric',
            'lwbp'             => 'required|numeric',
            'kwh'              => 'required|numeric',
            'kvarh'            => 'required|numeric',
            'temuan'           => 'nullable|string',
        ]);

        plnMeterReading::create([
            'id_pelanggan_pln' => $request->id_pelanggan_pln,
            'jam_pencatatan'   => $request->jam_pencatatan,
            'cos_phi'          => $request->cos_phi,
            'wbp'              => $request->wbp,
            'lwbp'             => $request->lwbp,
            'kwh'              => $request->kwh,
            'kvarh'            => $request->kvarh,
            'temuan'           => $request->temuan,
            'user_id'          => auth()->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pencatatan berhasil disimpan'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(plnMeterReading $plnMeterReading)
    {
        $data = PlnMeterReading::findOrFail($plnMeterReading);
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(plnMeterReading $plnMeterReading)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateplnMeterReadingRequest $request, plnMeterReading $plnMeterReading)
    {
        $data = PlnMeterReading::findOrFail($plnMeterReading);
        $data->update($request->all());

        return response()->json(['message' => 'Data berhasil diperbarui']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(plnMeterReading $plnMeterReading)
    {
        PlnMeterReading::findOrFail($plnMeterReading)->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);

    }
}

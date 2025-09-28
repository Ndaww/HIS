<?php

namespace App\Http\Controllers;

use App\Models\MasterEquipment;
use App\Models\PreventiveTarget;
use Illuminate\Http\Request;

class PreventiveV2Controller extends Controller
{
    public function index()
    {

    }

    public function create()
    {
        $equipments = MasterEquipment::all();
        return view('pages.preventive-v2.create', compact('equipments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'equipment_id'   => 'required|exists:equipment,id',
            'bulan'          => 'required|integer|min:1|max:12',
            'tahun'          => 'required|integer|min:2000',
            'target_bulanan' => 'required|integer|min:1',
        ]);

        PreventiveTarget::create([
            'equipment_id'   => $request->equipment_id,
            'bulan'          => $request->bulan,
            'tahun'          => $request->tahun,
            'target_bulanan' => $request->target_bulanan,
        ]);

        return redirect()->route('preventive-target.create')
                         ->with('success', 'Target bulanan berhasil disimpan.');
    }

}

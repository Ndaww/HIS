<?php

namespace App\Http\Controllers;

use App\Models\MasterEquipment;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRController extends Controller
{
    public function index($id)
    {
        $qrCode = QrCode::size(200)->generate('http://127.0.0.1:8000/preventive/v2/history-item/'.$id);

        return view('pages.tes.index', compact('qrCode'));
    }
}

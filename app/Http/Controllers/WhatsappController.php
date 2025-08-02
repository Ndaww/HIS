<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    public function kirim()
    {
        Http::post('http://127.0.0.1:8000/send-message', [
            'number' => '6281363323447',
            'message' => 'Halo dari Laravel + Zawa!',
        ]);

    }

}

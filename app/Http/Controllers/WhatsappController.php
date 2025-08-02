<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    public function kirim()
    {
        $url = 'https://api.watsap.id/send-message';
        $id_device = '12345'; // Ganti dengan ID dari dashboard watsap.id
        $api_key = '133317e3bf40398abe97a42f47d68b47c21ba789';      // Ganti dengan API key kamu
        $no_hp = '087889643945';
        $pesan = '😁 Halo Terimakasih 🙏';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'id_device' => $id_device,
            'api-key'   => $api_key,
            'no_hp'     => $no_hp,
            'pesan'     => $pesan
        ]);

        // Tampilkan response dari API
        // return response()->json($response->json());
        return $response->body(); // atau ->json(), ->status() untuk debug

    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class ZawaController extends Controller
{
    public function createSession()
    {
        // Panggil API untuk mendapatkan detail otorisasi.
        $response = Http::post('https://api-zawa.azickri.com/session');

        if ($response->failed()) {
            return view('zawa.error', ['message' => 'Gagal menginisiasi otorisasi Zawa. Silakan coba lagi.']);
        }

        $data = $response->json();
        $id = $data['id'];
        $sessionId = $data['sessionId'];

        // hapus session lama dulu
        $responses = Http::withHeaders([
                    'id' => env('ZAWA_ID'),
                    'session-id' => env('ZAWA_SESSION_ID'),
                    'Accept' => '*/*',
                ])->delete('https://api-zawa.azickri.com/session');

        sleep(5);

        // Generate QR
        $qrResponse = Http::withHeaders([
            'id' => $id,
            'session-id' => $sessionId,
            'Accept' => '*/*',
        ])->get('https://api-zawa.azickri.com/session');

        if ($qrResponse->failed()) {
            return view('zawa.error', ['message' => 'Gagal mendapatkan QR Code. Silakan muat ulang halaman.']);
        }

        $qr = $qrResponse->json();
        $qrCodeImage = $qr['qrcode'] ?? null;

        if ($qrCodeImage) {
            // Path ke file .env
            $envPath = base_path('.env');
            $envContent = File::get($envPath);

            // Hapus baris ZAWA_ID dan ZAWA_SESSION_ID yang lama jika ada
            $envContent = preg_replace('/^ZAWA_ID=.*$/m', '', $envContent);
            $envContent = preg_replace('/^ZAWA_SESSION_ID=.*$/m', '', $envContent);
            $envContent = trim($envContent);

            $envContent .= "\nZAWA_ID={$id}\nZAWA_SESSION_ID={$sessionId}";

            File::put($envPath, $envContent);
        }

        return view('pages.zawa.create', ['qr' => $qrCodeImage]);
    }


    public function checkStatus()
    {
        $id = env('ZAWA_ID');
        $sessionId = env('ZAWA_SESSION_ID');

        $response = Http::withHeaders([
            'id' => $id,
            'session-id' => $sessionId,
            'Accept' => '*/*',
            'Content-Type' => 'application/json',
            'Content-Length' => '68'
        ])->post('https://api-zawa.azickri.com/message', [
            'phone' => '6287889643945',
            'type' => 'text',
            'text' => 'TES NOTIFIKASI WA',
        ]);
        $data = $response->json();

        // jika message tidak terkirim
        if(isset($data['statusCode']) ){
            if($data['statusCode'] <> 200){
                $responses = Http::withHeaders([
                    'id' => $id,
                    'session-id' => $sessionId,
                    'Accept' => '*/*',
                ])->put('https://api-zawa.azickri.com/session');

                $qrResponse = Http::withHeaders([
                    'id' => $id,
                    'session-id' => $sessionId,
                    'Accept' => '*/*',
                ])->get('https://api-zawa.azickri.com/session');

                $qr = $qrResponse->json();
                $qrCodeImage = $qr['qrcode'] ?? null;

                $status = 'reconnect';

                return view('pages.zawa.reconnect', ['status' => $status,'qr' => $qrCodeImage]);
            }
        }

        if(isset($data['messageId'])){
            $status = [
                'connected' => 'success',
                'zawa_id' => env('ZAWA_ID'),
                'session_id' => env('ZAWA_SESSION_ID')
            ];
        } else {
            $status = [
                'connected' => 'error',
                'zawa_id' => env('ZAWA_ID'),
                'session_id' => env('ZAWA_SESSION_ID')
            ];
        }

        return view('pages.zawa.status', ['status' => $status]);
    }

    public function sendTestNotification()
    {
        $id = env('ZAWA_ID');
        $sessionId = env('ZAWA_SESSION_ID');

        // Pastikan ID dan Session ID ada sebelum memanggil API.
        if (empty($id) || empty($sessionId)) {
            // Jika tidak ada, arahkan langsung ke halaman reconnect atau buat sesi baru.
            return redirect('/zawa/reconnect-session')->with('error', 'Sesi Zawa tidak ditemukan. Silakan sambung ulang sesi.');
        }

        $response = Http::withHeaders([
            'id' => $id,
            'session-id' => $sessionId,
            'Accept' => '*/*',
            'Content-Type' => 'application/json',
        ])->post('https://api-zawa.azickri.com/message', [
            'phone' => '6287889643945',
            'type' => 'text',
            'text' => 'TES NOTIFIKASI WA',
        ]);

        $data = $response->json();

        // Cek respons dari API
        if ($response->failed() && isset($data['statusCode']) && ($data['statusCode'] == 400 || $data['statusCode'] == 404) ) {
            // Jika respons menunjukkan sesi tidak valid, arahkan ke halaman reconnect
            return redirect('/zawa/reconnect-session')->with('status', 'Sesi Anda tidak valid atau sudah kadaluarsa. Silakan sambung ulang.');
        }

        if(isset($data['messageId'])){
            $status = [
                'connected' => 'success',
                'zawa_id' => env('ZAWA_ID'),
                'session_id' => env('ZAWA_SESSION_ID')
            ];
        } else {
            $status = [
                'connected' => 'error',
                'zawa_id' => env('ZAWA_ID'),
                'session_id' => env('ZAWA_SESSION_ID')
            ];
        }

        return view('pages.zawa.status', ['data' => $data,'status' => $status]);
    }

    public function reconnectSession()
    {
        $id = env('ZAWA_ID');
        $sessionId = env('ZAWA_SESSION_ID');

        if (empty($id) || empty($sessionId)) {
            return redirect('/zawa/create-session')->with('error', 'Tidak ada sesi WA Aktif. Mohon buat sesi baru.');
        }

        $response = Http::withHeaders([
            'id' => $id,
            'session-id' => $sessionId,
            'Accept' => '*/*',
        ])->put('https://api-zawa.azickri.com/session');

        $data = $response->json('_id');
        // dd($data<>null);

        if ( $data <> null) {
            return view('pages.zawa.reconnect', [
                'status' => 'success',
                'message' => 'Sesi berhasil disambungkan ulang.'
            ]);
        } else {
            return view('pages.zawa.reconnect', [
                'status' => 'error',
                'message' => 'Gagal menyambungkan ulang sesi. Sesi mungkin sudah kadaluarsa. Silakan buat sesi baru.'
            ]);
        }
    }

}

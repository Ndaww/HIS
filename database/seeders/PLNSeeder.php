<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PLNSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $idPelanggan = '123123123123';

        $startDate = Carbon::now()->subMonth()->startOfMonth();
        $endDate   = Carbon::now()->subMonth()->endOfMonth();

        $wbp   = 100;
        $lwbp = 100;
        $kwh  = 100;
        $kvarh = 50;

        while ($startDate <= $endDate) {

            $wbpIncrement   = rand(3, 8);
            $lwbpIncrement = rand(15, 30);
            $kwhIncrement  = $wbpIncrement + $lwbpIncrement;
            $kvarhIncrement = rand(5, 15);

            $wbp   += $wbpIncrement;
            $lwbp += $lwbpIncrement;
            $kwh  += $kwhIncrement;
            $kvarh += $kvarhIncrement;

            DB::table('pln_meter_readings')->insert([
                'id_pelanggan_pln' => $idPelanggan,
                'tanggal_pencatatan'       => $startDate,
                'jam_pencatatan'   => '23:59:00',
                'cos_phi'          => rand(98, 100) / 100,
                'wbp'              => number_format($wbp, 2, '.', ''),
                'lwbp'             => number_format($lwbp, 2, '.', ''),
                'kwh'              => number_format($kwh, 2, '.', ''),
                'kvarh'            => number_format($kvarh, 2, '.', ''),
                'temuan'           => null,
                'created_at'       => $startDate->copy()->setTime(23, 59, 0),
                'updated_at'       => $startDate->copy()->setTime(23, 59, 0),
            ]);

            $startDate->addDay();
        }
    }
}
